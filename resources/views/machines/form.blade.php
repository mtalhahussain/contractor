<div class="form-row">
    <div class="form-group col-md-6">
        <label>Name</label>
        <input type="text" name="name" class="form-control" value="{{ old('name', $machine?->name) }}" required>
    </div>
    <div class="form-group col-md-6">
        <label>Type</label>
        <select name="type" class="form-control" required>
            @foreach(['Excavator','Dozer','Roller','Grader','Water Tanker','Hilux','Dumper'] as $type)
                <option value="{{ $type }}" @selected(old('type', $machine?->type) === $type)>{{ $type }}</option>
            @endforeach
        </select>
    </div>
</div>
<div class="form-row">
    <div class="form-group col-md-4">
        <label>Owner Category</label>
        <select name="owner_category" class="form-control" required>
            @foreach(['Company','Haji','Foji'] as $owner)
                <option value="{{ $owner }}" @selected(old('owner_category', $machine?->owner_category) === $owner)>{{ $owner }}</option>
            @endforeach
        </select>
    </div>
    <div class="form-group col-md-4">
        <label>Machine Code</label>
        <input type="text" name="machine_code" class="form-control" value="{{ old('machine_code', $machine?->machine_code) }}">
    </div>
    <div class="form-group col-md-4">
        <label>Status</label>
        <input type="text" name="status" class="form-control" value="{{ old('status', $machine?->status ?? 'active') }}" required>
    </div>
</div>
<div class="form-group">
    <label>Notes</label>
    <textarea name="notes" class="form-control" rows="3">{{ old('notes', $machine?->notes) }}</textarea>
</div>
@if ($errors->any())
    <div class="alert alert-danger">
        @foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach
    </div>
@endif
<button class="btn btn-primary">Save</button>
<a href="{{ route('machines.index') }}" class="btn btn-secondary">Back</a>
