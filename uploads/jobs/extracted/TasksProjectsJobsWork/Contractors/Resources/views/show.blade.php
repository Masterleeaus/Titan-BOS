@extends('layouts.app')

@section('content')
<div class="container">
    <h1>{{ $contractor->name }}</h1>
    <p><strong>Company:</strong> {{ $contractor->company }}</p>
    <p><strong>Email:</strong> {{ $contractor->email }}</p>
    <p><strong>Phone:</strong> {{ $contractor->phone }}</p>
    <p><strong>Rate:</strong> {{ $contractor->rate_type }} {{ number_format($contractor->rate_amount, 2) }}</p>
    <p><strong>Status:</strong> {{ $contractor->status }}</p>
    <p><strong>Notes:</strong> {{ $contractor->notes }}</p>
</div>
@endsection
