@extends('layouts.app')

@section('content')
<div class="container">
  <h1>Schedule — {{ $date }}</h1>

  <form class="row g-2 mb-3" method="get" action="{{ route('contractors.schedule.day') }}">
    <div class="col-auto">
      <input class="form-control" type="date" name="date" value="{{ $date }}">
    </div>
    <div class="col-auto">
      <button class="btn btn-primary" type="submit">Go</button>
    </div>
  </form>

  @php
    $grouped = $rows->groupBy('contractor_id');
  @endphp

  @forelse($grouped as $cid => $items)
    <div class="card mb-3">
      <div class="card-header d-flex justify-content-between">
        <strong>Contractor #{{ $cid }}</strong>
        @if(!empty($conflicts[$cid]))
          <span class="badge bg-danger">Conflicts</span>
        @endif
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-sm mb-0">
            <thead><tr><th>Time</th><th>Duration</th><th>Work Order</th><th>Role</th><th>Status</th></tr></thead>
            <tbody>
              @foreach($items as $a)
              <tr>
                <td>{{ $a->scheduled_at }}</td>
                <td>{{ $a->duration_minutes ?? 60 }} min</td>
                <td>#{{ $a->work_order_id }}</td>
                <td>{{ $a->role }}</td>
                <td>{{ $a->status }}</td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>
  @empty
    <div class="alert alert-info">No assignments for this date.</div>
  @endforelse
</div>
@endsection
