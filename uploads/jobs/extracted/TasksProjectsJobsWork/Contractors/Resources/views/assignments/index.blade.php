@extends('layouts.app')

@section('content')
<div class="container">
  <h1>Assignments</h1>

  @if(session('status'))
    <div class="alert alert-success">{{ session('status') }}</div>
  @endif

  <form method="post" action="{{ route('contractors.assignments.store') }}" class="row g-2 mb-4">
    @csrf
    <div class="col-md-3"><input class="form-control" type="number" name="work_order_id" placeholder="Work Order ID" required></div>
    <div class="col-md-3"><input class="form-control" type="number" name="contractor_id" placeholder="Contractor ID" required></div>
    <div class="col-md-2"><input class="form-control" type="text" name="role" placeholder="Role"></div>
    <div class="col-md-2"><input class="form-control" type="datetime-local" name="scheduled_at"></div>
    <div class="col-md-2"><button class="btn btn-primary w-100" type="submit">Assign</button></div>
  </form>

  <div class="table-responsive">
    <table class="table align-middle">
      <thead><tr>
        <th>ID</th><th>Work Order</th><th>Contractor</th><th>Role</th><th>Scheduled</th><th>Status</th><th></th>
      </tr></thead>
      <tbody>
      @forelse($rows as $a)
        <tr>
          <td>{{ $a->id }}</td>
          <td>{{ $a->work_order_id }}</td>
          <td>{{ $a->contractor_id }}</td>
          <td>{{ $a->role }}</td>
          <td>{{ $a->scheduled_at }}</td>
          <td>{{ $a->status }}</td>
          <td>
            <form method="post" action="{{ route('contractors.assignments.destroy', $a->id) }}">
              @csrf @method('DELETE')
              <button class="btn btn-sm btn-outline-danger">Remove</button>
            </form>
          </td>
        </tr>
      @empty
        <tr><td colspan="7">No assignments yet.</td></tr>
      @endforelse
      </tbody>
    </table>
  </div>
  {{ $rows->links() }}
</div>
@endsection
