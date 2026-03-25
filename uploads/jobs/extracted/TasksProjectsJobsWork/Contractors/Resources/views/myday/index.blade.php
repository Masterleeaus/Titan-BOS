@extends('layouts.app')
@section('content')
<div class="container" style="max-width:680px">
  <h2>My Day — {{ $date }}</h2>
  <form class="d-flex gap-2 mb-3" method="get">
    <input class="form-control" type="date" name="date" value="{{ $date }}">
    <button class="btn btn-primary">Go</button>
  </form>
  @forelse($rows as $a)
    <div class="card mb-2">
      <div class="card-body">
        <div class="d-flex justify-content-between">
          <strong>WO #{{ $a->work_order_id }}</strong>
          <span class="badge bg-secondary">{{ $a->status }}</span>
        </div>
        <div class="text-muted small">Scheduled: {{ $a->scheduled_at }} ({{ $a->duration_minutes ?? 60 }} min)</div>
        <div class="mt-2 d-flex flex-wrap gap-2">
          <form method="post" action="{{ route('contractors.myday.status', $a->id) }}">@csrf
            <input type="hidden" name="action" value="accept"><button class="btn btn-sm btn-outline-primary">Accept</button>
          </form>
          <form method="post" action="{{ route('contractors.myday.status', $a->id) }}">@csrf
            <input type="hidden" name="action" value="en_route"><button class="btn btn-sm btn-outline-primary">En Route</button>
          </form>
          <form method="post" action="{{ route('contractors.myday.status', $a->id) }}">@csrf
            <input type="hidden" name="action" value="start"><button class="btn btn-sm btn-outline-primary">Start</button>
          </form>
          <form method="post" action="{{ route('contractors.myday.status', $a->id) }}">@csrf
            <input type="hidden" name="action" value="complete"><button class="btn btn-sm btn-success">Complete</button>
          </form>
        </div>
      </div>
    </div>
  @empty
    <div class="alert alert-info">No assignments for this day.</div>
  @endforelse
</div>
@endsection
