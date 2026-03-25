@extends('layouts.app')
@section('content')
<div class="container">
  @include('contractor::contractors.partials.alerts')
  <form method="GET" class="row g-2 mb-3">
  <div class="col-md-4">
    <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="Search name/email/phone">
  </div>
  <div class="col-md-3">
    <select name="status" class="form-select">
      <option value="">All statuses</option>
      <option value="active" {{ request('status')==='active' ? 'selected' : '' }}>Active</option>
      <option value="inactive" {{ request('status')==='inactive' ? 'selected' : '' }}>Inactive</option>
    </select>
  </div>
  <div class="col-md-3">
    <input type="text" name="company_id" value="{{ request('company_id') }}" class="form-control" placeholder="Company ID">
  </div>
  <div class="col-md-2 d-grid">
    <button class="btn btn-secondary">Filter</button>
  </div>
</form>
<div class="mb-3">
  <a href="{{ route('contractor.export', request()->query()) }}" class="btn btn-outline-primary">Export CSV</a>
</div>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h1>Contractors</h1>
    <a href="{ route('contractor.create') }" class="btn btn-primary">Add Contractor</a>
  </div>

  @if(session('status'))
    <div class="alert alert-success">{ session('status') }</div>
  @endif

  <form id="bulkForm" method="POST" action="{{ route('contractor.bulk.status') }}">
  @csrf
  <div class="d-flex gap-2 mb-2">
    <select name="status" class="form-select form-select-sm" style="max-width:180px;">
      <option value="active">Set Active</option>
      <option value="inactive">Set Inactive</option>
    </select>
    <button class="btn btn-sm btn-secondary">Apply</button>
    <button formaction="{{ route('contractor.bulk.delete') }}" class="btn btn-sm btn-danger" onclick="return confirm('Delete selected?')">Delete</button>
    <a href="{{ route('contractor.export.xlsx', request()->query()) }}" class="btn btn-sm btn-outline-primary">Export XLSX</a>
    <a href="{{ route('contractor.logs') }}" class="btn btn-sm btn-outline-dark">View Activity Logs</a>
  </div>

  <table class="table table-striped">
    <thead><tr><th><input type="checkbox" onclick="document.querySelectorAll('.rowchk').forEach(c=>c.checked=this.checked)"></th><th>ID</th><th>Name</th><th>Email</th><th>Phone</th><th>Status</th><th></th></tr></thead>
    <tbody>
      @forelse($items as $row)
        <tr>
          <td>{ $row->id }</td>
          <td>{ $row->name }</td>
          <td>{ $row->email }</td>
          <td>{ $row->phone }</td>
          <td>{ $row->status }</td>
          <td class="text-end">
            <a href="{ route('contractor.edit', $row) }" class="btn btn-sm btn-secondary">Edit</a>
            <form action="{ route('contractor.destroy', $row) }" method="POST" class="d-inline">
              @csrf @method('DELETE')
              <button class="btn btn-sm btn-danger" onclick="return confirm('Delete this contractor?')">Delete</button>
            </form>
          </td>
        </tr>
      @empty
        <tr><td colspan="6" class="text-muted">No contractors yet. Get started by adding your first contractor.</td></tr>
      @endforelse
    </tbody>
  </table>

  { $items->links() }
</div>
@endsection

</form>
