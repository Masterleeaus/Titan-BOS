"""Tests for the Titan-BOS Relay Server (src/relay/__init__.py)."""

import asyncio
import json
import pytest

from src.relay import RelayServer, send_msg, recv_msg
from src.crypto import KeyPair


# ---------------------------------------------------------------------------
# Helpers
# ---------------------------------------------------------------------------

async def _connect(server_port: int):
    """Open a raw TCP connection to the relay and return (reader, writer)."""
    return await asyncio.open_connection("127.0.0.1", server_port)


def _registration_msg(kp: KeyPair, qmp_port: int = 9000) -> dict:
    return {
        "type": "relay.register",
        "node_id": kp.node_id,
        "public_key": kp.public_key_hex,
        "qmp_port": qmp_port,
        "signature": kp.sign(kp.node_id.encode()),
    }


async def _register(reader, writer, kp: KeyPair, qmp_port: int = 9000) -> dict:
    """Send relay.register and return the server's response."""
    await send_msg(writer, _registration_msg(kp, qmp_port))
    return await recv_msg(reader)


# ---------------------------------------------------------------------------
# Fixtures
# ---------------------------------------------------------------------------

@pytest.fixture
async def relay():
    server = RelayServer()
    host, port = await server.start("127.0.0.1", 0)
    yield server, port
    await server.stop()


# ---------------------------------------------------------------------------
# Server lifecycle
# ---------------------------------------------------------------------------

@pytest.mark.asyncio
async def test_relay_starts_and_stops():
    server = RelayServer()
    host, port = await server.start("127.0.0.1", 0)
    assert port > 0
    await server.stop()


@pytest.mark.asyncio
async def test_relay_accepts_connections(relay):
    _, port = relay
    r, w = await _connect(port)
    w.close()
    await w.wait_closed()


# ---------------------------------------------------------------------------
# relay.register
# ---------------------------------------------------------------------------

@pytest.mark.asyncio
async def test_register_valid_node(relay):
    _, port = relay
    kp = KeyPair.generate()
    r, w = await _connect(port)
    resp = await _register(r, w, kp)
    assert resp["type"] == "relay.registered"
    assert resp["ok"] is True
    w.close()


@pytest.mark.asyncio
async def test_register_increments_peer_count(relay):
    server, port = relay
    kp = KeyPair.generate()
    r, w = await _connect(port)
    await _register(r, w, kp)
    assert server.registered_count == 1
    w.close()


@pytest.mark.asyncio
async def test_register_rejects_bad_signature(relay):
    _, port = relay
    kp = KeyPair.generate()
    r, w = await _connect(port)
    await send_msg(w, {
        "type": "relay.register",
        "node_id": kp.node_id,
        "public_key": kp.public_key_hex,
        "qmp_port": 9000,
        "signature": "deadbeef" * 16,
    })
    resp = await recv_msg(r)
    assert resp["type"] == "relay.error"
    w.close()


@pytest.mark.asyncio
async def test_register_rejects_id_key_mismatch(relay):
    _, port = relay
    kp1 = KeyPair.generate()
    kp2 = KeyPair.generate()
    r, w = await _connect(port)
    await send_msg(w, {
        "type": "relay.register",
        "node_id": kp1.node_id,       # ID from kp1
        "public_key": kp2.public_key_hex,  # key from kp2 — mismatch
        "qmp_port": 9000,
        "signature": kp2.sign(kp1.node_id.encode()),
    })
    resp = await recv_msg(r)
    assert resp["type"] == "relay.error"
    w.close()


@pytest.mark.asyncio
async def test_register_deregisters_on_disconnect(relay):
    server, port = relay
    kp = KeyPair.generate()
    r, w = await _connect(port)
    await _register(r, w, kp)
    assert server.registered_count == 1
    w.close()
    await asyncio.sleep(0.05)  # allow cleanup
    assert server.registered_count == 0


# ---------------------------------------------------------------------------
# relay.peers
# ---------------------------------------------------------------------------

@pytest.mark.asyncio
async def test_peers_empty_initially(relay):
    _, port = relay
    kp = KeyPair.generate()
    r, w = await _connect(port)
    await _register(r, w, kp)
    # Consume relay.registered response, then ask for peers
    await send_msg(w, {"type": "relay.peers"})
    resp = await recv_msg(r)
    assert resp["type"] == "relay.peers"
    assert resp["peers"] == []
    w.close()


@pytest.mark.asyncio
async def test_peers_excludes_self(relay):
    """A node should not see itself in the peer list."""
    _, port = relay
    kp = KeyPair.generate()
    r, w = await _connect(port)
    await _register(r, w, kp)
    await send_msg(w, {"type": "relay.peers"})
    resp = await recv_msg(r)
    assert all(p["node_id"] != kp.node_id for p in resp["peers"])
    w.close()


@pytest.mark.asyncio
async def test_peers_lists_other_nodes(relay):
    _, port = relay
    kp_a = KeyPair.generate()
    kp_b = KeyPair.generate()

    ra, wa = await _connect(port)
    rb, wb = await _connect(port)

    await _register(ra, wa, kp_a)
    await _register(rb, wb, kp_b)

    await send_msg(wa, {"type": "relay.peers"})
    resp = await recv_msg(ra)
    peer_ids = [p["node_id"] for p in resp["peers"]]
    assert kp_b.node_id in peer_ids
    assert kp_a.node_id not in peer_ids

    wa.close()
    wb.close()


# ---------------------------------------------------------------------------
# relay.route
# ---------------------------------------------------------------------------

@pytest.mark.asyncio
async def test_route_delivers_frame(relay):
    _, port = relay
    kp_a = KeyPair.generate()
    kp_b = KeyPair.generate()

    ra, wa = await _connect(port)
    rb, wb = await _connect(port)

    await _register(ra, wa, kp_a)
    await _register(rb, wb, kp_b)

    # A sends a frame to B
    frame = {"content": {"hello": "world"}, "sender_id": kp_a.node_id,
             "message_type": "test.ping", "timestamp": 1.0, "signature": None}
    await send_msg(wa, {"type": "relay.route", "to": kp_b.node_id, "frame": frame})

    # B receives relay.frame
    delivered = await recv_msg(rb)
    assert delivered["type"] == "relay.frame"
    assert delivered["from"] == kp_a.node_id
    assert delivered["frame"]["message_type"] == "test.ping"

    wa.close()
    wb.close()


@pytest.mark.asyncio
async def test_route_unknown_peer_returns_error(relay):
    _, port = relay
    kp = KeyPair.generate()
    r, w = await _connect(port)
    await _register(r, w, kp)

    frame = {"content": {}, "sender_id": kp.node_id,
             "message_type": "test", "timestamp": 1.0, "signature": None}
    await send_msg(w, {"type": "relay.route", "to": "0" * 64, "frame": frame})
    resp = await recv_msg(r)
    assert resp["type"] == "relay.error"
    w.close()


@pytest.mark.asyncio
async def test_route_requires_registration(relay):
    """Unregistered connection should get an error when routing."""
    _, port = relay
    r, w = await _connect(port)
    frame = {"content": {}, "sender_id": "x",
             "message_type": "test", "timestamp": 1.0, "signature": None}
    await send_msg(w, {"type": "relay.route", "to": "0" * 64, "frame": frame})
    resp = await recv_msg(r)
    assert resp["type"] == "relay.error"
    w.close()
