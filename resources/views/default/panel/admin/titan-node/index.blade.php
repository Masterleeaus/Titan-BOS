@extends('panel.layout.app', ['disable_tblr' => true])

@section('title', __('Titan-BOS Node'))

@section('titlebar_actions')
    <div class="flex items-center gap-3">
        @if ($nodeOnline)
            <span class="inline-flex items-center gap-1.5 rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-700 dark:bg-green-900/30 dark:text-green-400">
                <span class="size-2 animate-pulse rounded-full bg-green-500"></span>
                {{ __('Node Online') }}
            </span>
        @else
            <span class="inline-flex items-center gap-1.5 rounded-full bg-red-100 px-3 py-1 text-xs font-semibold text-red-700 dark:bg-red-900/30 dark:text-red-400">
                <span class="size-2 rounded-full bg-red-500"></span>
                {{ __('Node Offline') }}
            </span>
        @endif

        <x-button
            variant="outline"
            size="sm"
            onclick="window.titanNode.refresh()"
        >
            <x-tabler-refresh class="size-4" />
            {{ __('Refresh') }}
        </x-button>
    </div>
@endsection

@section('content')
<div
    class="py-10"
    x-data="titanNodeDashboard()"
    x-init="init()"
>

    {{-- ------------------------------------------------------------------ --}}
    {{-- Offline banner --}}
    {{-- ------------------------------------------------------------------ --}}
    @unless ($nodeOnline)
        <x-alert variant="danger" class="mb-8">
            <p class="font-semibold">{{ __('Titan-BOS node is not reachable.') }}</p>
            <p class="mt-1 text-sm">
                {!! __('Make sure the Python node process is running and <code>TITAN_NODE_API_URL</code> is set correctly in your <code>.env</code> file.') !!}
            </p>
            <p class="mt-1 text-sm font-mono">
                TITAN_NODE_API_URL={{ config('titan_node.api_url') }}
            </p>
        </x-alert>
    @endunless

    {{-- ------------------------------------------------------------------ --}}
    {{-- Identity card --}}
    {{-- ------------------------------------------------------------------ --}}
    @if ($nodeOnline && $status)
        <div class="mb-8 rounded-2xl border border-border bg-card p-6 shadow-sm">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <h3 class="mb-1 text-sm font-semibold uppercase tracking-wider text-foreground/50">
                        {{ __('Node Identity') }}
                    </h3>
                    <p class="break-all font-mono text-sm text-foreground">
                        {{ $status['node_id'] ?? '—' }}
                    </p>
                </div>
                <div class="flex gap-6 text-center">
                    <div>
                        <p class="text-2xl font-bold text-foreground">{{ $peerCount }}</p>
                        <p class="text-xs text-foreground/50">{{ __('Peers') }}</p>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-foreground">{{ $repoCount }}</p>
                        <p class="text-xs text-foreground/50">{{ __('Repos') }}</p>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-foreground">{{ $status['identities'] ?? 0 }}</p>
                        <p class="text-xs text-foreground/50">{{ __('Identities') }}</p>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-foreground">{{ $status['peers_connected'] ?? 0 }}</p>
                        <p class="text-xs text-foreground/50">{{ __('Connected') }}</p>
                    </div>
                </div>
            </div>

            <div class="mt-4 flex flex-wrap gap-6 text-xs text-foreground/60">
                <span>
                    <span class="font-semibold">{{ __('QMP Port') }}:</span>
                    {{ $status['qmp_port'] ?? '—' }}
                </span>
                <span>
                    <span class="font-semibold">{{ __('Git Port') }}:</span>
                    {{ $status['git_port'] ?? '—' }}
                </span>
                <span>
                    <span class="font-semibold">{{ __('Git Daemon') }}:</span>
                    {{ ($status['git_daemon_running'] ?? false) ? __('Running') : __('Stopped') }}
                </span>
            </div>
        </div>
    @endif

    {{-- ------------------------------------------------------------------ --}}
    {{-- Two-column grid: peers + repos --}}
    {{-- ------------------------------------------------------------------ --}}
    <div class="grid gap-8 lg:grid-cols-2">

        {{-- ---------------------------------------------------------------- --}}
        {{-- Peers panel --}}
        {{-- ---------------------------------------------------------------- --}}
        <div class="flex flex-col gap-6">
            <x-card>
                <x-slot:head class="flex items-center justify-between px-5 py-3.5">
                    <h4 class="m-0 text-base font-medium">{{ __('Peers') }}</h4>
                    <span
                        class="rounded-full bg-foreground/10 px-2.5 py-0.5 text-xs font-semibold"
                        x-text="peers.length"
                    >{{ $peerCount }}</span>
                </x-slot:head>

                {{-- Peer list --}}
                <div class="divide-y divide-border">
                    <template x-if="peers.length === 0">
                        <p class="px-5 py-8 text-center text-sm text-foreground/50">
                            {{ __('No peers discovered yet. Add one below or enable LAN discovery.') }}
                        </p>
                    </template>
                    <template x-for="peer in peers" :key="peer.node_id">
                        <div class="flex items-start justify-between gap-3 px-5 py-3">
                            <div class="min-w-0">
                                <p
                                    class="truncate font-mono text-xs text-foreground"
                                    :title="peer.node_id"
                                    x-text="peer.node_id.substring(0, 16) + '...'"
                                ></p>
                                <p
                                    class="mt-0.5 text-xs text-foreground/50"
                                    x-text="peer.host + ':' + peer.port"
                                ></p>
                            </div>
                            <div class="flex shrink-0 flex-wrap gap-1">
                                <template x-for="cap in peer.capabilities">
                                    <span
                                        class="rounded bg-foreground/10 px-1.5 py-0.5 text-[10px] font-medium"
                                        x-text="cap"
                                    ></span>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>

                {{-- Connect form --}}
                <div class="border-t border-border px-5 py-4">
                    <h5 class="mb-3 text-xs font-semibold uppercase tracking-wider text-foreground/50">
                        {{ __('Connect to Peer') }}
                    </h5>
                    <form @submit.prevent="connectPeer" class="flex gap-2">
                        <input
                            class="h-9 grow rounded-lg border border-border bg-background px-3 text-sm focus:outline-none focus:ring-2 focus:ring-primary/40"
                            type="text"
                            x-model="connectForm.host"
                            placeholder="{{ __('Host / IP') }}"
                            required
                        />
                        <input
                            class="h-9 w-24 rounded-lg border border-border bg-background px-3 text-sm focus:outline-none focus:ring-2 focus:ring-primary/40"
                            type="number"
                            x-model.number="connectForm.port"
                            placeholder="{{ __('Port') }}"
                            min="1"
                            max="65535"
                            required
                        />
                        <x-button
                            variant="primary"
                            size="sm"
                            type="submit"
                            ::disabled="connectForm.loading"
                        >
                            <span x-show="!connectForm.loading">{{ __('Connect') }}</span>
                            <span x-show="connectForm.loading">{{ __('…') }}</span>
                        </x-button>
                    </form>
                    <p
                        class="mt-2 text-xs text-green-600 dark:text-green-400"
                        x-show="connectForm.success"
                        x-text="connectForm.success"
                    ></p>
                    <p
                        class="mt-2 text-xs text-red-600 dark:text-red-400"
                        x-show="connectForm.error"
                        x-text="connectForm.error"
                    ></p>
                </div>
            </x-card>
        </div>

        {{-- ---------------------------------------------------------------- --}}
        {{-- Repos panel --}}
        {{-- ---------------------------------------------------------------- --}}
        <div class="flex flex-col gap-6">
            <x-card>
                <x-slot:head class="flex items-center justify-between px-5 py-3.5">
                    <h4 class="m-0 text-base font-medium">{{ __('Repositories') }}</h4>
                    <div class="flex items-center gap-2">
                        <span
                            class="rounded-full bg-foreground/10 px-2.5 py-0.5 text-xs font-semibold"
                            x-text="repos.length"
                        >{{ $repoCount }}</span>
                        <x-button
                            variant="ghost"
                            size="sm"
                            @click="syncRepos"
                            ::disabled="repoForm.syncing"
                            title="{{ __('Sync all repos from peers') }}"
                        >
                            <x-tabler-cloud-download class="size-4" :class="{'animate-spin': repoForm.syncing}" />
                        </x-button>
                    </div>
                </x-slot:head>

                {{-- Repo list --}}
                <div class="divide-y divide-border">
                    <template x-if="repos.length === 0">
                        <p class="px-5 py-8 text-center text-sm text-foreground/50">
                            {{ __('No repositories yet. Create one below.') }}
                        </p>
                    </template>
                    <template x-for="repo in repos" :key="repo.name">
                        <div class="flex items-start justify-between gap-3 px-5 py-3">
                            <div class="min-w-0">
                                <p
                                    class="flex items-center gap-1.5 font-medium text-foreground"
                                    x-text="repo.name"
                                ></p>
                                <p
                                    class="mt-0.5 truncate text-xs text-foreground/50"
                                    x-text="repo.description || '—'"
                                ></p>
                            </div>
                            <div class="flex shrink-0 items-center gap-1.5">
                                <template x-if="repo.is_local">
                                    <span class="rounded bg-blue-100 px-1.5 py-0.5 text-[10px] font-semibold text-blue-700 dark:bg-blue-900/30 dark:text-blue-300">
                                        {{ __('local') }}
                                    </span>
                                </template>
                                <template x-if="!repo.is_local">
                                    <span class="rounded bg-foreground/10 px-1.5 py-0.5 text-[10px] font-medium text-foreground/60">
                                        {{ __('remote') }}
                                    </span>
                                </template>
                                <span
                                    class="text-[10px] text-foreground/40"
                                    :title="repo.owner_node_id"
                                    x-text="repo.owner_node_id_short"
                                ></span>
                            </div>
                        </div>
                    </template>
                </div>

                {{-- Create repo form --}}
                <div class="border-t border-border px-5 py-4">
                    <h5 class="mb-3 text-xs font-semibold uppercase tracking-wider text-foreground/50">
                        {{ __('Create Repository') }}
                    </h5>
                    <form @submit.prevent="createRepo" class="flex flex-col gap-2">
                        <input
                            class="h-9 rounded-lg border border-border bg-background px-3 text-sm focus:outline-none focus:ring-2 focus:ring-primary/40"
                            type="text"
                            x-model="repoForm.name"
                            placeholder="{{ __('repo-name') }}"
                            pattern="[a-zA-Z0-9_\-\.]+"
                            required
                        />
                        <div class="flex gap-2">
                            <input
                                class="h-9 grow rounded-lg border border-border bg-background px-3 text-sm focus:outline-none focus:ring-2 focus:ring-primary/40"
                                type="text"
                                x-model="repoForm.description"
                                placeholder="{{ __('Description (optional)') }}"
                            />
                            <x-button
                                variant="primary"
                                size="sm"
                                type="submit"
                                ::disabled="repoForm.loading"
                            >
                                <x-tabler-plus class="size-4" />
                                <span x-show="!repoForm.loading">{{ __('Create') }}</span>
                                <span x-show="repoForm.loading">{{ __('…') }}</span>
                            </x-button>
                        </div>
                    </form>
                    <p
                        class="mt-2 text-xs text-green-600 dark:text-green-400"
                        x-show="repoForm.success"
                        x-text="repoForm.success"
                    ></p>
                    <p
                        class="mt-2 text-xs text-red-600 dark:text-red-400"
                        x-show="repoForm.error"
                        x-text="repoForm.error"
                    ></p>
                </div>
            </x-card>
        </div>

    </div>{{-- /grid --}}

</div>{{-- /x-data --}}
@endsection

@push('script')
<script>
function titanNodeDashboard() {
    return {
        peers: @json($peers),
        repos: @json($repos),
        nodeOnline: @json($nodeOnline),

        connectForm: { host: '', port: 47338, loading: false, success: '', error: '' },
        repoForm:    { name: '', description: '', loading: false, success: '', error: '', syncing: false },

        init() {
            window.titanNode = this;
            // Auto-refresh every 30 seconds
            setInterval(() => this.refresh(), 30000);
        },

        async refresh() {
            try {
                const [peersRes, reposRes, statusRes] = await Promise.all([
                    fetch('{{ route("dashboard.admin.titan-node.peers") }}'),
                    fetch('{{ route("dashboard.admin.titan-node.repos") }}'),
                    fetch('{{ route("dashboard.admin.titan-node.status") }}'),
                ]);
                if (peersRes.ok)  { const d = await peersRes.json();  this.peers = d.peers  ?? []; }
                if (reposRes.ok)  { const d = await reposRes.json();  this.repos = d.repos  ?? []; }
                if (statusRes.ok) { const d = await statusRes.json(); this.nodeOnline = !d.error; }
            } catch (e) {
                this.nodeOnline = false;
            }
        },

        async connectPeer() {
            this.connectForm.loading = true;
            this.connectForm.success = '';
            this.connectForm.error   = '';
            try {
                const res = await fetch('{{ route("dashboard.admin.titan-node.connect") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({
                        host: this.connectForm.host,
                        port: this.connectForm.port,
                    }),
                });
                const data = await res.json();
                if (res.ok && !data.error) {
                    this.connectForm.success = '{{ __("Connection queued. Peer will appear if successful.") }}';
                    this.connectForm.host = '';
                    setTimeout(() => this.refresh(), 2000);
                } else {
                    this.connectForm.error = data.error ?? '{{ __("Failed to connect.") }}';
                }
            } catch (e) {
                this.connectForm.error = '{{ __("Network error.") }}';
            } finally {
                this.connectForm.loading = false;
            }
        },

        async createRepo() {
            this.repoForm.loading = true;
            this.repoForm.success = '';
            this.repoForm.error   = '';
            try {
                const res = await fetch('{{ route("dashboard.admin.titan-node.repos.create") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({
                        name:        this.repoForm.name,
                        description: this.repoForm.description,
                    }),
                });
                const data = await res.json();
                if ((res.ok || res.status === 201) && !data.error) {
                    this.repoForm.success = `{{ __("Created") }}: ${data.name}`;
                    this.repoForm.name        = '';
                    this.repoForm.description = '';
                    await this.refresh();
                } else {
                    this.repoForm.error = data.error ?? '{{ __("Failed to create repo.") }}';
                }
            } catch (e) {
                this.repoForm.error = '{{ __("Network error.") }}';
            } finally {
                this.repoForm.loading = false;
            }
        },

        async syncRepos() {
            this.repoForm.syncing = true;
            try {
                await fetch('{{ route("dashboard.admin.titan-node.repos.sync") }}', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                });
                await this.refresh();
            } finally {
                this.repoForm.syncing = false;
            }
        },
    };
}
</script>
@endpush
