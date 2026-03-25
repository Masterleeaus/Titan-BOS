"""
Titan-BOS Node HTTP API

A lightweight aiohttp server that exposes the running Node state
as JSON endpoints. Laravel (or any HTTP client) calls these to
display the node dashboard without needing any Python knowledge.

Usage (called automatically by Node.start() when api_port > 0):
    await node.start_api(port=8765)

Or standalone:
    from src.node.api import NodeAPIServer
    api = NodeAPIServer(node)
    await api.start(port=8765)
"""

import json
import logging
from typing import TYPE_CHECKING

from aiohttp import web

if TYPE_CHECKING:
    from src.node import Node

logger = logging.getLogger(__name__)


class NodeAPIServer:
    """
    Thin JSON HTTP API wrapping a running Node instance.

    All endpoints return JSON. Errors return {"error": "..."} with an
    appropriate HTTP status code. CORS is open (localhost-only use).
    """

    def __init__(self, node: "Node"):
        self.node = node
        self._runner: web.AppRunner | None = None
        self._site: web.TCPSite | None = None

    # -------------------------------------------------------------------------
    # Lifecycle
    # -------------------------------------------------------------------------

    async def start(self, host: str = "127.0.0.1", port: int = 8765) -> tuple[str, int]:
        """Start the HTTP server. Returns (host, port) actually bound to."""
        app = self._build_app()
        self._runner = web.AppRunner(app)
        await self._runner.setup()
        self._site = web.TCPSite(self._runner, host, port)
        await self._site.start()
        logger.info(f"Node HTTP API listening on http://{host}:{port}")
        return host, port

    async def stop(self):
        """Gracefully shut down the HTTP server."""
        if self._runner:
            await self._runner.cleanup()
            self._runner = None
            self._site = None
            logger.info("Node HTTP API stopped")

    # -------------------------------------------------------------------------
    # Router
    # -------------------------------------------------------------------------

    def _build_app(self) -> web.Application:
        app = web.Application(middlewares=[_cors_middleware])
        app.router.add_get("/api/node/status", self._status)
        app.router.add_get("/api/node/peers", self._peers)
        app.router.add_get("/api/node/repos", self._repos)
        app.router.add_get("/api/node/identities", self._identities)
        app.router.add_post("/api/node/connect", self._connect)
        app.router.add_post("/api/node/repos/create", self._create_repo)
        app.router.add_post("/api/node/repos/sync", self._sync_repos)
        # OPTIONS pre-flight for all routes
        app.router.add_options("/{path_info:.*}", _options_handler)
        return app

    # -------------------------------------------------------------------------
    # Handlers
    # -------------------------------------------------------------------------

    async def _status(self, request: web.Request) -> web.Response:
        return _json(self.node.status())

    async def _peers(self, request: web.Request) -> web.Response:
        peers = [p.to_dict() for p in self.node.registry.get_peers()]
        return _json({"peers": peers, "count": len(peers)})

    async def _repos(self, request: web.Request) -> web.Response:
        repos = [
            {
                "name": m.name,
                "description": m.description,
                "owner_node_id": m.owner_node_id,
                "owner_node_id_short": m.owner_node_id[:16] + "...",
                "is_local": m.owner_node_id == self.node.node_id,
                "remotes": m.remotes,
                "local_path": str(
                    self.node.federation.repos_path / f"{m.name}.git"
                ),
            }
            for m in self.node.federation.manifests.values()
        ]
        return _json({"repos": repos, "count": len(repos)})

    async def _identities(self, request: web.Request) -> web.Response:
        identities = [
            {
                "identity_id": iid,
                "identity_id_short": iid[:16] + "...",
                "public_key": ident.public_key,
                "public_key_short": ident.public_key[:16] + "...",
                "timestamp": ident.timestamp,
                "metadata": ident.metadata,
            }
            for iid, ident in self.node.didn.identities.items()
        ]
        return _json({"identities": identities, "count": len(identities)})

    async def _connect(self, request: web.Request) -> web.Response:
        try:
            body = await request.json()
        except Exception:
            return _error("Invalid JSON body", 400)

        host = body.get("host", "").strip()
        port = int(body.get("port", 0))

        if not host or port <= 0:
            return _error("host and port are required", 400)

        import asyncio
        asyncio.create_task(self.node.connect_to_peer(host, port))
        return _json({"queued": True, "host": host, "port": port})

    async def _create_repo(self, request: web.Request) -> web.Response:
        try:
            body = await request.json()
        except Exception:
            return _error("Invalid JSON body", 400)

        name = body.get("name", "").strip()
        description = body.get("description", "").strip()

        if not name:
            return _error("name is required", 400)

        manifest = self.node.create_repo(name, description)
        if manifest is None:
            return _error(f"Failed to create repo '{name}'", 500)

        return _json(
            {
                "created": True,
                "name": manifest.name,
                "description": manifest.description,
                "owner_node_id": manifest.owner_node_id,
            },
            status=201,
        )

    async def _sync_repos(self, request: web.Request) -> web.Response:
        results = self.node.sync_repos()
        return _json({"synced": results})


# ---------------------------------------------------------------------------
# Helpers
# ---------------------------------------------------------------------------

def _json(data: dict, status: int = 200) -> web.Response:
    return web.Response(
        text=json.dumps(data),
        content_type="application/json",
        status=status,
        headers={"Access-Control-Allow-Origin": "*"},
    )


def _error(message: str, status: int = 400) -> web.Response:
    return _json({"error": message}, status=status)


async def _options_handler(request: web.Request) -> web.Response:
    return web.Response(
        status=204,
        headers={
            "Access-Control-Allow-Origin": "*",
            "Access-Control-Allow-Methods": "GET, POST, OPTIONS",
            "Access-Control-Allow-Headers": "Content-Type",
        },
    )


@web.middleware
async def _cors_middleware(request: web.Request, handler):
    response = await handler(request)
    response.headers["Access-Control-Allow-Origin"] = "*"
    return response
