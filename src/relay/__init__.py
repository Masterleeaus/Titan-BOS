"""
Titan-BOS Relay Server

A lightweight TCP server for WAN NAT traversal. Nodes that can't reach
each other directly (different networks, firewalls) both connect *outbound*
to a public relay, which then routes QMP frames between them.

The relay uses the same 4-byte length-prefix framing as QMP.

Deploy on any VPS:
    python -m src.relay --host 0.0.0.0 --port 47339

Nodes connect by setting:
    NodeConfig(relay_url="your-vps.example.com:47339")

Relay protocol (3 message types):

  relay.register  — node authenticates with Ed25519 signature
  relay.peers     — request/receive list of registered nodes
  relay.route     — forward a QMP frame to another registered node
"""

import asyncio
import hashlib
import json
import logging
from dataclasses import dataclass
from typing import Dict, Optional

from src.crypto import verify as _verify_sig

logger = logging.getLogger(__name__)

RELAY_PORT = 47339


# ---------------------------------------------------------------------------
# Wire helpers (shared with client)
# ---------------------------------------------------------------------------

async def send_msg(writer: asyncio.StreamWriter, msg: dict):
    """Send one relay protocol message with a 4-byte length prefix."""
    data = json.dumps(msg).encode()
    writer.write(len(data).to_bytes(4, "big") + data)
    await writer.drain()


async def recv_msg(reader: asyncio.StreamReader) -> dict:
    """Read one relay protocol message."""
    length = int.from_bytes(await reader.readexactly(4), "big")
    return json.loads(await reader.readexactly(length))


# ---------------------------------------------------------------------------
# Server
# ---------------------------------------------------------------------------

@dataclass
class _NodeRegistration:
    node_id: str
    public_key: str
    qmp_host: str   # IP as seen by relay
    qmp_port: int   # QMP port the node reported
    writer: asyncio.StreamWriter


class RelayServer:
    """
    Relay server for WAN peer discovery and message routing.

    Thread-safety: designed for a single asyncio event loop.
    """

    def __init__(self):
        self._nodes: Dict[str, _NodeRegistration] = {}   # node_id → reg
        self._by_writer: Dict[int, str] = {}              # id(writer) → node_id
        self._server: Optional[asyncio.AbstractServer] = None

    # -------------------------------------------------------------------------
    # Lifecycle
    # -------------------------------------------------------------------------

    async def start(self, host: str = "0.0.0.0", port: int = RELAY_PORT) -> tuple:
        """Start listening. Returns (host, port) actually bound to."""
        self._server = await asyncio.start_server(
            self._handle_connection, host=host, port=port
        )
        bound = self._server.sockets[0].getsockname()
        logger.info(f"Relay server listening on {bound[0]}:{bound[1]}")
        return bound

    async def stop(self):
        """Shut down the relay server."""
        if self._server:
            self._server.close()
            await self._server.wait_closed()
            self._server = None
            logger.info("Relay server stopped")

    @property
    def registered_count(self) -> int:
        return len(self._nodes)

    # -------------------------------------------------------------------------
    # Connection handler
    # -------------------------------------------------------------------------

    async def _handle_connection(self, reader: asyncio.StreamReader,
                                  writer: asyncio.StreamWriter):
        peer_host = writer.get_extra_info("peername")[0]
        logger.debug(f"Relay: new connection from {peer_host}")
        try:
            while True:
                msg = await recv_msg(reader)
                await self._dispatch(msg, writer, peer_host)
        except (asyncio.IncompleteReadError, ConnectionResetError, json.JSONDecodeError):
            pass
        finally:
            self._deregister(writer)
            writer.close()

    async def _dispatch(self, msg: dict, writer: asyncio.StreamWriter, peer_host: str):
        t = msg.get("type")
        if t == "relay.register":
            await self._handle_register(msg, writer, peer_host)
        elif t == "relay.peers":
            await self._handle_peers(writer)
        elif t == "relay.route":
            await self._handle_route(msg, writer)
        else:
            logger.debug(f"Relay: unknown message type '{t}'")

    # -------------------------------------------------------------------------
    # Message handlers
    # -------------------------------------------------------------------------

    async def _handle_register(self, msg: dict, writer: asyncio.StreamWriter,
                                peer_host: str):
        node_id  = msg.get("node_id", "")
        pub_key  = msg.get("public_key", "")
        qmp_port = int(msg.get("qmp_port", 0))
        signature = msg.get("signature", "")

        # 1. node_id must be SHA-256(public_key_bytes)
        try:
            expected = hashlib.sha256(bytes.fromhex(pub_key)).hexdigest()
        except ValueError:
            await send_msg(writer, {"type": "relay.error", "error": "invalid public_key hex"})
            return
        if expected != node_id:
            await send_msg(writer, {"type": "relay.error", "error": "node_id / public_key mismatch"})
            return

        # 2. Signature proves ownership
        if not _verify_sig(pub_key, node_id.encode(), signature):
            await send_msg(writer, {"type": "relay.error", "error": "signature verification failed"})
            return

        # Register (replace any stale entry for this node_id)
        if node_id in self._nodes:
            old = self._nodes[node_id]
            self._by_writer.pop(id(old.writer), None)

        reg = _NodeRegistration(
            node_id=node_id,
            public_key=pub_key,
            qmp_host=peer_host,
            qmp_port=qmp_port,
            writer=writer,
        )
        self._nodes[node_id] = reg
        self._by_writer[id(writer)] = node_id

        logger.info(f"Relay: registered {node_id[:16]}... from {peer_host}:{qmp_port}")
        await send_msg(writer, {
            "type": "relay.registered",
            "ok": True,
            "peers_online": len(self._nodes),
        })

    async def _handle_peers(self, writer: asyncio.StreamWriter):
        """Return all currently registered peers (excluding the requester)."""
        requester_id = self._by_writer.get(id(writer))
        peers = [
            {
                "node_id": r.node_id,
                "public_key": r.public_key,
                "host": r.qmp_host,
                "port": r.qmp_port,
            }
            for r in self._nodes.values()
            if r.node_id != requester_id
        ]
        await send_msg(writer, {"type": "relay.peers", "peers": peers})

    async def _handle_route(self, msg: dict, writer: asyncio.StreamWriter):
        """Forward a QMP frame to a registered peer."""
        from_id = self._by_writer.get(id(writer))
        if not from_id:
            await send_msg(writer, {"type": "relay.error", "error": "not registered"})
            return

        to_id = msg.get("to", "")
        frame = msg.get("frame")
        if not frame or not to_id:
            await send_msg(writer, {"type": "relay.error", "error": "missing to/frame"})
            return

        target = self._nodes.get(to_id)
        if not target:
            await send_msg(writer, {
                "type": "relay.error",
                "error": f"peer {to_id[:16]}... not registered",
            })
            return

        try:
            await send_msg(target.writer, {
                "type": "relay.frame",
                "from": from_id,
                "frame": frame,
            })
        except Exception as e:
            logger.debug(f"Relay: failed to deliver frame to {to_id[:16]}...: {e}")

    # -------------------------------------------------------------------------
    # Cleanup
    # -------------------------------------------------------------------------

    def _deregister(self, writer: asyncio.StreamWriter):
        node_id = self._by_writer.pop(id(writer), None)
        if node_id:
            self._nodes.pop(node_id, None)
            logger.info(f"Relay: deregistered {node_id[:16]}...")


# ---------------------------------------------------------------------------
# CLI entry point
# ---------------------------------------------------------------------------

def main():
    import argparse

    parser = argparse.ArgumentParser(description="Titan-BOS relay server")
    parser.add_argument("--host", default="0.0.0.0", help="Bind address")
    parser.add_argument("--port", type=int, default=RELAY_PORT, help="TCP port")
    args = parser.parse_args()

    logging.basicConfig(level=logging.INFO,
                        format="%(asctime)s %(levelname)s %(message)s")

    async def _run():
        relay = RelayServer()
        host, port = await relay.start(args.host, args.port)
        print(f"Titan-BOS relay running on {host}:{port}  (Ctrl-C to stop)")
        try:
            await asyncio.Event().wait()
        except (KeyboardInterrupt, asyncio.CancelledError):
            await relay.stop()

    asyncio.run(_run())


if __name__ == "__main__":
    main()
