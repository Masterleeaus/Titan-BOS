@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Edit Contractor</h1>
    <form method="POST" action="{{ route('admin.contractors.update', $contractor) }}">
        @csrf
        @method('PUT')
        <div class="mb-3"><label>Name</label><input name="name" class="form-control" value="{{ $contractor->name }}" required></div>
        <div class="mb-3"><label>Email</label><input name="email" type="email" class="form-control" value="{{ $contractor->email }}"></div>
        <div class="mb-3"><label>Phone</label><input name="phone" class="form-control" value="{{ $contractor->phone }}"></div>
        <div class="mb-3"><label>Company</label><input name="company" class="form-control" value="{{ $contractor->company }}"></div>
        <div class="mb-3"><label>ABN</label><input name="abn" class="form-control" value="{{ $contractor->abn }}"></div>
        <div class="mb-3"><label>Rate Type</label>
            <select name="rate_type" class="form-select">
                <option value="hourly" {{ $contractor->rate_type=='hourly'?'selected':'' }}>Hourly</option>
                <option value="fixed" {{ $contractor->rate_type=='fixed'?'selected':'' }}>Fixed</option>
            </select>
        </div>
        <div class="mb-3"><label>Rate Amount</label><input name="rate_amount" type="number" step="0.01" class="form-control" value="{{ $contractor->rate_amount }}"></div>
        <div class="mb-3"><label>Status</label><input name="status" class="form-control" value="{{ $contractor->status }}"></div>
        <div class="mb-3"><label>Notes</label><textarea name="notes" class="form-control">{{ $contractor->notes }}</textarea></div>
        <button class="btn btn-primary">Update</button>
    </form>
</div>
@endsection
