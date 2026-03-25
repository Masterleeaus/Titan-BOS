"""Tests for RelayClient and RelayWriter (src/relay/client.py)."""

import asyncio
import json
import pytest
from unittest.mock import AsyncMock, MagicMock, patch

from src.relay import RelayServer
from src.relay.client import RelayClient, RelayWriter
from src.crypto import KeyPair
from src.qmp import QMPMessage, QMPService


# ---------------------------------------------------------------------------
# RelayWriter unit tests
# ---------------------------------------------------------------------------

class _FakeRelayClient:
    """Minimal RelayClient stand-in for RelayWriter tests."""
    def __init__(self):
        self.routed: list[tuple] = []  # (to_id, frame)
        self.is_connected = True

    async def _route(self, to_id: str, frame: dict):
        self.routed.append((to_id, frame))


def _make_writer(peer_id="b" * 64) -> tuple[RelayWriter, _FakeRelayClient]:
    client = _FakeRelayClient()
    writer = RelayWriter(client, peer_id)
    return writer, client


def _qmp_bytes(msg: dict) -> bytes:
    data = json.dumps(msg).encode()
    return len(data).to_bytes(4, "big") + data


@pytest.mark.asyncio
async def test_relay_writer_routes_frame():
    writer, client = _make_writer("peer1")
    msg = {"content": {}, "sender_id": "a" * 64,
           "message_type": "test.msg", "timestamp": 1.0, "signature": None}
    writer.write(_qmp_bytes(msg))
    await writer.drain()
    assert len(client.routed) == 1
    to_id, frame = client.routed[0]
    assert to_id == "peer1"
    assert frame["message_type"] == "test.msg"


@pytest.mark.asyncio
async def test_relay_writer_partial_write_no_route():
    writer, client = _make_writer()
    # Only write 2 bytes — not a complete frame
    writer.write(b"\x00\x00")
    await writer.drain()
    assert client.routed == []


@pytest.mark.asyncio
async def test_relay_writer_multiple_frames():
    writer, client = _make_writer("peer2")
    for i in range(3):
        msg = {"content": {}, "sender_id": "a",
               "message_type": f"msg.{i}", "timestamp": float(i), "signature": None}
        writer.write(_qmp_bytes(msg))
        await writer.drain()
    assert len(client.routed) == 3


def test_relay_writer_get_extra_info():
    writer, _ = _make_writer()
    assert writer.get_extra_info("peername") == ("relay", 0)
    assert writer.get_extra_info("unknown", "default") == "default"


def test_relay_writer_is_closing_reflects_connection():
    writer, client = _make_writer()
    client.is_connected = True
    assert writer.is_closing is False
    client.is_connected = False
    assert writer.is_closing is True


# ---------------------------------------------------------------------------
# RelayClient integration tests (real relay server in-process)
# ---------------------------------------------------------------------------

@pytest.fixture
async def relay_server():
    server = RelayServer()
    _, port = await server.start("127.0.0.1", 0)
    yield server, port
    await server.stop()


@pytest.fixture
def qmp_service():
    svc = QMPService(node_id="a" * 64)
    return svc


@pytest.mark.asyncio
async def test_client_connects_and_registers(relay_server):
    server, port = relay_server
    kp = KeyPair.generate()
    qmp = QMPService(node_id=kp.node_id)
    client = RelayClient(kp.node_id, kp, qmp, qmp_port=9000)

    await client.connect("127.0.0.1", port)
    await asyncio.sleep(0.05)  # allow read loop to process relay.registered

    assert client.is_connected
    assert server.registered_count == 1
    await client.disconnect()


@pytest.mark.asyncio
async def test_client_discovers_peers(relay_server):
    server, port = relay_server
    kp_a = KeyPair.generate()
    kp_b = KeyPair.generate()

    discovered = []

    qmp_a = QMPService(node_id=kp_a.node_id)
    client_a = RelayClient(kp_a.node_id, kp_a, qmp_a, qmp_port=9001)
    client_a.on_peer_discovered = AsyncMock(side_effect=lambda p: discovered.append(p))

    qmp_b = QMPService(node_id=kp_b.node_id)
    client_b = RelayClient(kp_b.node_id, kp_b, qmp_b, qmp_port=9002)

    # B registers first
    await client_b.connect("127.0.0.1", port)
    await asyncio.sleep(0.05)

    # A registers second — should discover B
    await client_a.connect("127.0.0.1", port)
    await asyncio.sleep(0.1)  # allow relay.peers to arrive

    assert any(p["node_id"] == kp_b.node_id for p in discovered)

    await client_a.disconnect()
    await client_b.disconnect()


@pytest.mark.asyncio
async def test_client_routes_qmp_frame_end_to_end(relay_server):
    """A QMP handshake sent via relay by node A arrives at node B's handler."""
    _, port = relay_server
    kp_a = KeyPair.generate()
    kp_b = KeyPair.generate()

    received_messages = []

    qmp_a = QMPService(node_id=kp_a.node_id)
    qmp_b = QMPService(node_id=kp_b.node_id)

    # Register a handler on B that captures what arrives
    async def capture(msg, writer):
        received_messages.append((msg, writer))

    qmp_b.register_handler("test.ping", capture)

    client_a = RelayClient(kp_a.node_id, kp_a, qmp_a, qmp_port=9001)
    client_b = RelayClient(kp_b.node_id, kp_b, qmp_b, qmp_port=9002)

    await client_a.connect("127.0.0.1", port)
    await client_b.connect("127.0.0.1", port)
    await asyncio.sleep(0.05)

    # A sends a test.ping to B via relay
    ping = qmp_a.create_message({"hello": "B"}, "test.ping")
    await client_a.send_via_relay(kp_b.node_id, ping)
    await asyncio.sleep(0.1)

    assert len(received_messages) == 1
    msg, writer = received_messages[0]
    assert msg.message_type == "test.ping"
    assert isinstance(writer, RelayWriter)

    await client_a.disconnect()
    await client_b.disconnect()


@pytest.mark.asyncio
async def test_relay_writer_reply_routes_back(relay_server):
    """
    When B's handler replies via the RelayWriter it received,
    the reply arrives at A's QMP service.
    """
    _, port = relay_server
    kp_a = KeyPair.generate()
    kp_b = KeyPair.generate()

    a_received = []

    qmp_a = QMPService(node_id=kp_a.node_id)
    qmp_b = QMPService(node_id=kp_b.node_id)

    async def a_pong_handler(msg, writer):
        a_received.append(msg)

    qmp_a.register_handler("test.pong", a_pong_handler)

    async def b_ping_handler(msg, writer):
        # Reply back through the RelayWriter
        pong = qmp_b.create_message({"reply": "pong"}, "test.pong")
        await qmp_b.send(writer, pong)

    qmp_b.register_handler("test.ping", b_ping_handler)

    client_a = RelayClient(kp_a.node_id, kp_a, qmp_a, qmp_port=9001)
    client_b = RelayClient(kp_b.node_id, kp_b, qmp_b, qmp_port=9002)

    await client_a.connect("127.0.0.1", port)
    await client_b.connect("127.0.0.1", port)
    await asyncio.sleep(0.05)

    ping = qmp_a.create_message({"hello": "B"}, "test.ping")
    await client_a.send_via_relay(kp_b.node_id, ping)
    await asyncio.sleep(0.2)

    assert len(a_received) == 1
    assert a_received[0].message_type == "test.pong"

    await client_a.disconnect()
    await client_b.disconnect()
