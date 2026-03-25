<?php

declare(strict_types=1);

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;
use Throwable;

/**
 * Titan-BOS Node Dashboard Controller
 *
 * Proxies requests to the local Python node HTTP API and renders
 * the node dashboard inside the existing admin panel layout.
 *
 * Configure the node API URL via:
 *   TITAN_NODE_API_URL=http://127.0.0.1:8765  (in .env)
 */
class TitanNodeController extends Controller
{
    private string $apiBase;

    private int $timeout;

    public function __construct()
    {
        $this->apiBase = rtrim(config('titan_node.api_url', 'http://127.0.0.1:8765'), '/');
        $this->timeout = (int) config('titan_node.timeout', 5);
    }

    // -------------------------------------------------------------------------
    // Views
    // -------------------------------------------------------------------------

    public function index(): View
    {
        $status = $this->fetchJson('/api/node/status');
        $peers  = $this->fetchJson('/api/node/peers');
        $repos  = $this->fetchJson('/api/node/repos');

        return view('panel.admin.titan-node.index', [
            'status'    => $status,
            'peers'     => $peers['peers']    ?? [],
            'peerCount' => $peers['count']    ?? 0,
            'repos'     => $repos['repos']    ?? [],
            'repoCount' => $repos['count']    ?? 0,
            'nodeOnline' => ($status !== null && ! isset($status['error'])),
            'apiBase'   => route('dashboard.admin.titan-node.index'),
        ]);
    }

    // -------------------------------------------------------------------------
    // JSON proxy endpoints (called by Alpine.js on the dashboard page)
    // -------------------------------------------------------------------------

    public function status(): JsonResponse
    {
        return $this->proxyGet('/api/node/status');
    }

    public function peers(): JsonResponse
    {
        return $this->proxyGet('/api/node/peers');
    }

    public function repos(): JsonResponse
    {
        return $this->proxyGet('/api/node/repos');
    }

    public function identities(): JsonResponse
    {
        return $this->proxyGet('/api/node/identities');
    }

    public function connect(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'host' => 'required|string|max:253',
            'port' => 'required|integer|min:1|max:65535',
        ]);

        return $this->proxyPost('/api/node/connect', $validated);
    }

    public function createRepo(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:100|regex:/^[a-zA-Z0-9_\-\.]+$/',
            'description' => 'nullable|string|max:500',
        ]);

        return $this->proxyPost('/api/node/repos/create', $validated);
    }

    public function syncRepos(): JsonResponse
    {
        return $this->proxyPost('/api/node/repos/sync', []);
    }

    // -------------------------------------------------------------------------
    // Internal helpers
    // -------------------------------------------------------------------------

    private function fetchJson(string $path): ?array
    {
        try {
            $response = Http::timeout($this->timeout)->get($this->apiBase . $path);

            return $response->successful() ? $response->json() : null;
        } catch (Throwable) {
            return null;
        }
    }

    private function proxyGet(string $path): JsonResponse
    {
        try {
            $response = Http::timeout($this->timeout)->get($this->apiBase . $path);

            return response()->json(
                $response->json(),
                $response->successful() ? $response->status() : 502
            );
        } catch (Throwable $e) {
            return response()->json(
                ['error' => 'Node API unreachable: ' . $e->getMessage()],
                503
            );
        }
    }

    private function proxyPost(string $path, array $data): JsonResponse
    {
        try {
            $response = Http::timeout($this->timeout)
                ->post($this->apiBase . $path, $data);

            return response()->json(
                $response->json(),
                $response->successful() ? $response->status() : 502
            );
        } catch (Throwable $e) {
            return response()->json(
                ['error' => 'Node API unreachable: ' . $e->getMessage()],
                503
            );
        }
    }
}
