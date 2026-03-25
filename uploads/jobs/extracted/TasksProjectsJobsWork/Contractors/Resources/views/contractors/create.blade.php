@extends('layouts.app')
@section('content')
<div class="container">
  @include('contractor::contractors.partials.alerts')
  <h1>New Contractor</h1>
  <form method="POST" action="{ route('contractor.store') }">
    @csrf
    @include('contractor::contractors.partials.form', ['item' => null])
    <button class="btn btn-primary">Create</button>
    <a href="{ route('contractor.index') }" class="btn btn-light">Cancel</a>
  </form>
</div>
@endsection
