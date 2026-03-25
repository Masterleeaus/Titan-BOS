@extends('layouts.app')

@section('content')
<div class="container">
    <h1>New Contractor</h1>
    <form method="POST" action="{{ route('admin.contractors.store') }}">
        @csrf
        <div class="mb-3"><label>Name</label><input name="name" class="form-control" required></div>
        <div class="mb-3"><label>Email</label><input name="email" type="email" class="form-control"></div>
        <div class="mb-3"><label>Phone</label><input name="phone" class="form-control"></div>
        <div class="mb-3"><label>Company</label><input name="company" class="form-control"></div>
        <div class="mb-3"><label>ABN</label><input name="abn" class="form-control"></div>
        <div class="mb-3"><label>Rate Type</label>
            <select name="rate_type" class="form-select">
                <option value="hourly">Hourly</option>
                <option value="fixed">Fixed</option>
            </select>
        </div>
        <div class="mb-3"><label>Rate Amount</label><input name="rate_amount" type="number" step="0.01" class="form-control"></div>
        <div class="mb-3"><label>Status</label><input name="status" class="form-control" value="active"></div>
        <div class="mb-3"><label>Notes</label><textarea name="notes" class="form-control"></textarea></div>
        <button class="btn btn-primary">Save</button>
        <button id="aiFill" type="button" class="btn btn-outline-secondary">Fill with AI</button>
    </form>
</div>
<script>
document.getElementById('aiFill').addEventListener('click', async () => {
    const prompt = window.prompt('Describe the contractor to auto-fill:');
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
    const s = data.suggestion;
    for (const [k,v] of Object.entries(s)) {
        const el = document.querySelector('[name="'+k+'"]');
        if(el) el.value = v;
    }
});
</script>
@endsection
