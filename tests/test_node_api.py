"""Tests for the Titan-BOS Node HTTP API (src/node/api.py)."""

import json
import pytest
from unittest.mock import AsyncMock, MagicMock, patch

from src.node import Node, NodeConfig
from src.node.api import NodeAPIServer
from src.crypto import KeyPair


@pytest.fixture
def node(tmp_path):
    cfg = NodeConfig(
        storage_path=str(tmp_path),
        qmp_port=0,
        git_port=9419,
        api_port=0,
        enable_lan_discovery=False,
        enable_git_daemon=False,
    )
    return Node(cfg)


@pytest.fixture
def api(node):
    return NodeAPIServer(node)


# ---------------------------------------------------------------------------
# Route resolution helpers — build a fake aiohttp Request
# ---------------------------------------------------------------------------

def _fake_request(app, method="GET", path="/", body=None):
    """Create a minimal aiohttp.web.Request-like object for handler testing."""
    from aiohttp.test_utils import make_mocked_request
    req = make_mocked_request(method, path, app=app)
    if body is not None:
        # Patch json() to return the body dict
        req.json = AsyncMock(return_value=body)
    return req


# ---------------------------------------------------------------------------
# /api/node/status
# ---------------------------------------------------------------------------

@pytest.mark.asyncio
async def test_status_returns_node_id(api):
    app = api._build_app()
    req = _fake_request(app, path="/api/node/status")
    resp = await api._status(req)
    data = json.loads(resp.text)
    assert "node_id" in data
    assert len(data["node_id"]) == 64


@pytest.mark.asyncio
async def test_status_returns_counts(api):
    app = api._build_app()
    req = _fake_request(app, path="/api/node/status")
    resp = await api._status(req)
    data = json.loads(resp.text)
    assert "repos" in data
    assert "peers_known" in data
    assert "identities" in data


# ---------------------------------------------------------------------------
# /api/node/peers
# ---------------------------------------------------------------------------

@pytest.mark.asyncio
async def test_peers_empty_initially(api):
    req = _fake_request(api._build_app(), path="/api/node/peers")
    resp = await api._peers(req)
    data = json.loads(resp.text)
    assert data["count"] == 0
    assert data["peers"] == []


@pytest.mark.asyncio
async def test_peers_reflects_registry(api):
    kp = KeyPair.generate()
    api.node.registry.add_peer("10.0.0.1", 9000, kp.node_id, kp.public_key_hex, ["git"])
    req = _fake_request(api._build_app(), path="/api/node/peers")
    resp = await api._peers(req)
    data = json.loads(resp.text)
    assert data["count"] == 1
    assert data["peers"][0]["node_id"] == kp.node_id
    assert data["peers"][0]["host"] == "10.0.0.1"


# ---------------------------------------------------------------------------
# /api/node/repos
# ---------------------------------------------------------------------------

@pytest.mark.asyncio
async def test_repos_empty_initially(api):
    req = _fake_request(api._build_app(), path="/api/node/repos")
    resp = await api._repos(req)
    data = json.loads(resp.text)
    assert data["count"] == 0


@pytest.mark.asyncio
async def test_repos_after_create(api):
    api.node.create_repo("test-repo", "A test repository")
    req = _fake_request(api._build_app(), path="/api/node/repos")
    resp = await api._repos(req)
    data = json.loads(resp.text)
    assert data["count"] == 1
    assert data["repos"][0]["name"] == "test-repo"
    assert data["repos"][0]["is_local"] is True


# ---------------------------------------------------------------------------
# /api/node/identities
# ---------------------------------------------------------------------------

@pytest.mark.asyncio
async def test_identities_empty_initially(api):
    req = _fake_request(api._build_app(), path="/api/node/identities")
    resp = await api._identities(req)
    data = json.loads(resp.text)
    assert data["count"] == 0


@pytest.mark.asyncio
async def test_identities_after_register(api):
    kp = KeyPair.generate()
    iid = api.node.didn.register_identity(kp.public_key_hex, kp.sign(kp.public_key_hex.encode()))
    req = _fake_request(api._build_app(), path="/api/node/identities")
    resp = await api._identities(req)
    data = json.loads(resp.text)
    assert data["count"] == 1
    assert data["identities"][0]["identity_id"] == iid
    # private keys must NOT appear in the response
    assert "seed" not in json.dumps(data)
    assert "private" not in json.dumps(data).lower()


# ---------------------------------------------------------------------------
# POST /api/node/connect
# ---------------------------------------------------------------------------

@pytest.mark.asyncio
async def test_connect_queues_task(api):
    req = _fake_request(
        api._build_app(),
        method="POST",
        path="/api/node/connect",
        body={"host": "192.168.1.5", "port": 47338},
    )
    with patch("asyncio.create_task") as mock_task:
        resp = await api._connect(req)
    data = json.loads(resp.text)
    assert data["queued"] is True
    assert data["host"] == "192.168.1.5"
    mock_task.assert_called_once()


@pytest.mark.asyncio
async def test_connect_rejects_missing_host(api):
    req = _fake_request(
        api._build_app(),
        method="POST",
        path="/api/node/connect",
        body={"port": 47338},
    )
    resp = await api._connect(req)
    assert resp.status == 400
    data = json.loads(resp.text)
    assert "error" in data


@pytest.mark.asyncio
async def test_connect_rejects_bad_json(api):
    req = _fake_request(api._build_app(), method="POST", path="/api/node/connect")
    req.json = AsyncMock(side_effect=Exception("bad json"))
    resp = await api._connect(req)
    assert resp.status == 400


# ---------------------------------------------------------------------------
# POST /api/node/repos/create
# ---------------------------------------------------------------------------

@pytest.mark.asyncio
async def test_create_repo_returns_201(api):
    req = _fake_request(
        api._build_app(),
        method="POST",
        path="/api/node/repos/create",
        body={"name": "my-repo", "description": "Test"},
    )
    resp = await api._create_repo(req)
    assert resp.status == 201
    data = json.loads(resp.text)
    assert data["created"] is True
    assert data["name"] == "my-repo"


@pytest.mark.asyncio
async def test_create_repo_rejects_missing_name(api):
    req = _fake_request(
        api._build_app(),
        method="POST",
        path="/api/node/repos/create",
        body={"description": "No name"},
    )
    resp = await api._create_repo(req)
    assert resp.status == 400


# ---------------------------------------------------------------------------
# POST /api/node/repos/sync
# ---------------------------------------------------------------------------

@pytest.mark.asyncio
async def test_sync_repos_returns_results(api):
    api.node.create_repo("sync-test")
    req = _fake_request(api._build_app(), method="POST", path="/api/node/repos/sync")
    resp = await api._sync_repos(req)
    data = json.loads(resp.text)
    assert "synced" in data
