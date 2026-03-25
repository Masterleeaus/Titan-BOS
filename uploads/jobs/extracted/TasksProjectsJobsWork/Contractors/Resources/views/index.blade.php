@extends('layouts.app')

@section('content')
<div class="container">
  <h1>Contractor Module</h1>
  <p class="text-muted">Worksuite patch applied successfully.</p>

  @if(!is_null($count))
    <div class="alert alert-info">There are <strong>{ $count }</strong> contractors in the database.</div>
  @else
    <div class="alert alert-warning">The <code>contractors</code> table is missing. Run migrations.</div>
  @endif

  <p><a href="{ route('contractor.ping') }" class="btn btn-primary">Ping JSON</a></p>
</div>
@endsection
