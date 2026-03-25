@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>Contractors</h1>
        <div>
            <a href="{{ route('admin.contractors.create') }}" class="btn btn-primary">New</a>
            <button id="aiSuggestBtn" class="btn btn-outline-secondary">Create with AI</button>
        </div>
    </div>
    <table class="table table-striped">
        <thead><tr><th>Name</th><th>Company</th><th>Rate</th><th>Status</th><th></th></tr></thead>
        <tbody>
            @foreach($items as $s)
            <tr>
                <td><a href="{{ route('admin.contractors.show', $s) }}">{{ $s->name }}</a></td>
                <td>{{ $s->company }}</td>
                <td>{{ $s->rate_type }} {{ number_format($s->rate_amount,2) }}</td>
                <td>{{ $s->status }}</td>
                <td><a href="{{ route('admin.contractors.edit', $s) }}" class="btn btn-sm btn-link">Edit</a></td>
            </tr>
            @endforeach
        </tbody>
    </table>
    {{ $items->links() }}
</div>

<script>
document.getElementById('aiSuggestBtn').addEventListener('click', async () => {
    const prompt = window.prompt('Describe the contractor you need:');
    if(!prompt) return;
    const res = await fetch('{{ route('admin.contractors.ai.suggest') }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ prompt })
    });
    const data = await res.json();
    alert('AI Suggestion: ' + JSON.stringify(data.suggestion, null, 2));
});
</script>
@endsection
