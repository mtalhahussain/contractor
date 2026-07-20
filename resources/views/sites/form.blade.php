@if ($errors->any())
    <div class="alert alert-danger">
        @foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach
    </div>
@endif

<div class="form-row">
    <div class="form-group col-md-4">
        <label>Site Name</label>
        <input type="text" name="name" class="form-control" value="{{ old('name', $site?->name) }}" required>
    </div>
    <div class="form-group col-md-4">
        <label>Code</label>
        <input type="text" name="code" class="form-control" value="{{ old('code', $site?->code) }}">
    </div>
    <div class="form-group col-md-4">
        <label>Status</label>
        <select name="status" class="form-control" required>
            <option value="active" @selected(old('status', $site?->status ?? 'active') === 'active')>Active</option>
            <option value="inactive" @selected(old('status', $site?->status) === 'inactive')>Inactive</option>
        </select>
    </div>
</div>
<div class="form-group">
    <label>Description</label>
    <textarea name="description" class="form-control" rows="3">{{ old('description', $site?->description) }}</textarea>
</div>

<button class="btn btn-primary">Save</button>
<a class="btn btn-secondary" href="{{ route('sites.index') }}">Back</a>
