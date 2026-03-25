<div class="mb-3">
  <label class="form-label">Name</label>
  <input type="text" name="name" class="form-control" value="{{ old('name', optional($item)->name) }}" required>
  @error('name') <div class="text-danger small">{{ $message }}</div> @enderror
</div>
<div class="mb-3">
  <label class="form-label">Email</label>
  <input type="email" name="email" class="form-control" value="{{ old('email', optional($item)->email) }}">
  @error('email') <div class="text-danger small">{{ $message }}</div> @enderror
</div>
<div class="mb-3">
  <label class="form-label">Phone</label>
  <input type="text" name="phone" class="form-control" value="{{ old('phone', optional($item)->phone) }}">
</div>
<div class="mb-3">
  <label class="form-label">Status</label>
  <select name="status" class="form-select">
    @php $status = old('status', optional($item)->status ?? 'active'); @endphp
    <option value="active" {{ $status==='active' ? 'selected' : '' }}>Active</option>
    <option value="inactive" {{ $status==='inactive' ? 'selected' : '' }}>Inactive</option>
  </select>
</div>
