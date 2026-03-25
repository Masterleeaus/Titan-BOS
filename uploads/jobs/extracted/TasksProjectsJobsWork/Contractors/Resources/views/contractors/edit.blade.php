@extends('layouts.app')
@section('content')
<div class="container">
  @include('contractor::contractors.partials.alerts')
  <h1>Edit Contractor</h1>
  <form method="POST" action="{ route('contractor.update', $item) }">
    @csrf @method('PUT')
    @include('contractor::contractors.partials.form', ['item' => $item])
    <button class="btn btn-primary">Save</button>
    <a href="{ route('contractor.index') }" class="btn btn-light">Cancel</a>
  </form>
</div>
@endsection

<hr>
<h3>Tags</h3>
<form method="POST" action="{{ route('contractor.tag.add', $item->id) }}" class="d-flex gap-2">
  @csrf
  <input type="text" name="tag" class="form-control" placeholder="Add tag">
  <button class="btn btn-outline-secondary">Add</button>
</form>
@php
  $tags = DB::table('contractor_tags')->where('contractor_id', $item->id)->pluck('tag')->all();
@endphp
<p class="mt-2">
  @forelse($tags as $tg)
    <span class="badge bg-info text-dark me-1">{{ $tg }}</span>
  @empty
    <span class="text-muted">No tags yet.</span>
  @endforelse
</p>

<hr>
<h3>Notes</h3>
<form method="POST" action="{{ route('contractor.note.add', $item->id) }}">
  @csrf
  <textarea name="note" class="form-control" rows="3" placeholder="Add a note"></textarea>
  <button class="btn btn-outline-secondary mt-2">Save Note</button>
</form>
@php
  $notes = DB::table('contractor_notes')->where('contractor_id', $item->id)->orderBy('id','desc')->get();
@endphp
<ul class="list-group mt-2">
  @forelse($notes as $n)
    <li class="list-group-item">
      <div class="small text-muted">{{ $n->created_at }}</div>
      <div>{{ $n->note }}</div>
    </li>
  @empty
    <li class="list-group-item text-muted">No notes yet.</li>
  @endforelse
</ul>
