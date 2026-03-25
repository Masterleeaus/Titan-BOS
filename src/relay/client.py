"""
Titan-BOS Relay Client

Connects a Node to a relay server for WAN NAT traversal.
When two nodes can't reach each other directly, the relay forwards
QMP frames between them using outbound-only TCP connections.

Key classes:

  RelayClient — connects to the relay, registers the node, handles
                inbound relay.frame messages, and sends relay.route
                messages when the node needs to reach a peer.

  RelayWriter — a fake asyncio.StreamWriter that routes outgoing
                QMP frames through the relay instead of a direct TCP
                connection. Handlers in QMPService and Node receive a
                RelayWriter and work identically to a direct connection.
"""

import asyncio
import json
import logging
from typing import Callable, Dict, Optional, TYPE_CHECKING

from src.relay import send_msg, recv_msg
from src.qmp import QMPMessage

if TYPE_CHECKING:
    from src.crypto import KeyPair
    from src.qmp import QMPService

logger = logging.getLogger(__name__)


# ---------------------------------------------------------------------------
# RelayWriter
# ---------------------------------------------------------------------------

class RelayWriter:
    """
    Fake asyncio.StreamWriter that routes QMP frames via a relay server.

    The QMPService.send() call does:
        writer.write(4-byte-len + payload)
        await writer.drain()

    RelayWriter accumulates bytes in write(), then on drain() parses
    one complete QMP frame and forwards it as a relay.route message.
    """

    def __init__(self, client: "RelayClient", peer_node_id: str):
        self._client = client
        self._peer_node_id = peer_node_id
        self._buf = b""

    # asyncio.StreamWriter interface
    def write(self, data: bytes):
        self._buf += data

    async def drain(self):
        """Flush one QMP frame from the buffer via relay.route."""
        if len(self._buf) < 4:
            return
        length = int.from_bytes(self._buf[:4], "big")
        if len(self._buf) < 4 + length:
            return  # incomplete frame — wait for more writes

        frame_bytes = self._buf[4:4 + length]
        self._buf = self._buf[4 + length:]

        try:
            frame = json.loads(frame_bytes.decode())
        except (json.JSONDecodeError, UnicodeDecodeError) as e:
            logger.warning(f"RelayWriter: could not decode frame: {e}")
            return

        try:
            await self._client._route(self._peer_node_id, frame)
        except Exception as e:
            logger.warning(f"RelayWriter: route failed for {self._peer_node_id[:16]}...: {e}")

    def get_extra_info(self, key: str, default=None):
        """Return relay pseudo-address so handlers can extract peer host."""
        if key == "peername":
            return ("relay", 0)
        return default

    def close(self):
        pass

    @property
    def is_closing(self) -> bool:
        return not self._client.is_connected


# ---------------------------------------------------------------------------
# RelayClient
# ---------------------------------------------------------------------------

class RelayClient:
    """
    Connects to a relay server and enables QMP communication via relay.

    Usage:
        client = RelayClient(node_id, keypair, qmp_service)
        await client.connect("relay.example.com", 47339)

        # Send a QMP message to a peer via relay
        await client.send_via_relay(peer_node_id, qmp_message)

        # Get a RelayWriter for a peer (used by Node internally)
        writer = client.get_relay_writer(peer_node_id)
    """

    def __init__(
        self,
        node_id: str,
        keypair: "KeyPair",
        qmp_service: "QMPService",
        qmp_port: int = 0,
    ):
        self.node_id = node_id
        self._keypair = keypair
        self._qmp = qmp_service
        self._qmp_port = qmp_port

        self._reader: Optional[asyncio.StreamReader] = None
        self._writer: Optional[asyncio.StreamWriter] = None
        self._relay_writers: Dict[str, RelayWriter] = {}
        self._read_task: Optional[asyncio.Task] = None
        self.is_connected = False

        # Called with peer_info dict when relay.peers lists a new node
        self.on_peer_discovered: Optional[Callable] = None

    # -------------------------------------------------------------------------
    # Lifecycle
    # -------------------------------------------------------------------------

    async def connect(self, host: str, port: int):
        """Connect to the relay server and register this node."""
        self._reader, self._writer = await asyncio.open_connection(host, port)
        self.is_connected = True
        logger.info(f"Relay client: connected to {host}:{port}")

        # Register
        sig = self._keypair.sign(self.node_id.encode())
        await send_msg(self._writer, {
            "type": "relay.register",
            "node_id": self.node_id,
            "public_key": self._keypair.public_key_hex,
            "qmp_port": self._qmp_port,
            "signature": sig,
        })

        # Start background read loop
        self._read_task = asyncio.create_task(self._read_loop())

    async def disconnect(self):
        """Close the relay connection."""
        self.is_connected = False
        if self._read_task:
            self._read_task.cancel()
            try:
                await self._read_task
            except asyncio.CancelledError:
                pass
        if self._writer:
            self._writer.close()
            try:
                await self._writer.wait_closed()
            except Exception:
                pass
        logger.info("Relay client: disconnected")

    # -------------------------------------------------------------------------
    # Send API
    # -------------------------------------------------------------------------

    def get_relay_writer(self, peer_node_id: str) -> RelayWriter:
        """Return (or create) a RelayWriter for the given peer node_id."""
        if peer_node_id not in self._relay_writers:
            self._relay_writers[peer_node_id] = RelayWriter(self, peer_node_id)
        return self._relay_writers[peer_node_id]

    async def send_via_relay(self, to_node_id: str, message: QMPMessage):
        """Route a QMP message to a peer via the relay server."""
        if not self.is_connected or not self._writer:
            raise RuntimeError("Not connected to relay")
        await self._route(to_node_id, message.to_dict())

    async def request_peers(self):
        """Ask the relay for the list of currently registered peers."""
        if self.is_connected and self._writer:
            await send_msg(self._writer, {"type": "relay.peers"})

    # -------------------------------------------------------------------------
    # Internal
    # -------------------------------------------------------------------------

    async def _route(self, to_node_id: str, frame: dict):
        """Send a relay.route message to the server."""
        if self._writer and not self._writer.is_closing():
            await send_msg(self._writer, {
                "type": "relay.route",
                "to": to_node_id,
                "frame": frame,
            })

    async def _read_loop(self):
        """Background task that reads messages from the relay server."""
        try:
            while self.is_connected:
                msg = await recv_msg(self._reader)
                await self._handle_server_msg(msg)
        except (asyncio.IncompleteReadError, ConnectionResetError, json.JSONDecodeError):
            logger.info("Relay client: server closed connection")
        except asyncio.CancelledError:
            pass
        finally:
            self.is_connected = False

    async def _handle_server_msg(self, msg: dict):
        t = msg.get("type")

        if t == "relay.registered":
            peers_online = msg.get("peers_online", 0)
            logger.info(f"Relay: registered — {peers_online} peer(s) online")
            # Immediately ask for the peer list so we can discover nodes
            await self.request_peers()

        elif t == "relay.peers":
            peers = msg.get("peers", [])
            logger.info(f"Relay: {len(peers)} peer(s) available")
            for peer_info in peers:
                if peer_info.get("node_id") == self.node_id:
                    continue
                if self.on_peer_discovered:
                    asyncio.create_task(self.on_peer_discovered(peer_info))

        elif t == "relay.frame":
            await self._handle_inbound_frame(msg)

        elif t == "relay.error":
            logger.warning(f"Relay error: {msg.get('error')}")

        else:
            logger.debug(f"Relay client: unknown message type '{t}'")

    async def _handle_inbound_frame(self, msg: dict):
        """
        A QMP frame arrived via relay.

        We dispatch it through QMPService._process_message() with a
        RelayWriter as the writer — so all handlers (handshake, git
        announce, DIDN sync) work exactly as with a direct connection.
        """
        from_id = msg.get("from", "unknown")
        frame = msg.get("frame")
        if not frame:
            return

        try:
            qmp_msg = QMPMessage.from_dict(frame)
        except Exception as e:
            logger.warning(f"Relay client: malformed QMP frame from {from_id[:16]}...: {e}")
            return

        relay_writer = self.get_relay_writer(from_id)
        await self._qmp._process_message(qmp_msg, relay_writer)
