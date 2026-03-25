@extends('layouts.app')
@section('content')
<div class="container">
  <h1>Contractor Activity Logs</h1>
  <table class="table table-striped">
    <thead><tr><th>ID</th><th>Contractor</th><th>User</th><th>Action</th><th>Meta</th><th>At</th></tr></thead>
    <tbody>
      @foreach($items as $r)
        <tr>
          <td>{{ $r->id }}</td>
          <td>{{ $r->contractor_id }}</td>
          <td>{{ $r->user_id }}</td>
          <td>{{ $r->action }}</td>
          <td><pre class="m-0 small">{{ $r->meta }}</pre></td>
          <td>{{ $r->created_at }}</td>
        </tr>
      @endforeach
    </tbody>
  </table>
  {{ $items->links() }}
</div>
@endsection
