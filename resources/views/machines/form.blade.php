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
        <select name="status" class="form-control" required>
            <option value="active" @selected(old('status', $machine?->status ?? 'active') === 'active')>Active</option>
            <option value="inactive" @selected(old('status', $machine?->status) === 'inactive')>Inactive</option>
        </select>
    </div>
</div>
@php
    $currentAssignment = $machine?->siteAssignments?->firstWhere('assigned_to', null);
@endphp
<div class="form-row">
    <div class="form-group col-md-6">
        <label>Current Site</label>
        <select name="site_id" class="form-control">
            <option value="">No Site Assigned</option>
            @foreach($sites as $site)
                <option value="{{ $site->id }}" @selected((int) old('site_id', $currentAssignment?->site_id) === $site->id)>{{ $site->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="form-group col-md-6">
        <label>Assigned From</label>
        <input type="date" name="site_assigned_from" class="form-control" value="{{ old('site_assigned_from', $currentAssignment?->assigned_from?->format('Y-m-d') ?? now()->format('Y-m-d')) }}">
        <small class="text-muted">Required if a site is selected. Changing site will close old assignment automatically.</small>
    </div>
</div>
<div class="form-group">
    <label>Notes</label>
    <textarea name="notes" class="form-control" rows="3">{{ old('notes', $machine?->notes) }}</textarea>
</div>
@if($machine && $machine->siteAssignments->count() > 0)
    <div class="card border mb-3">
        <div class="card-header">Site Assignment History</div>
        <div class="card-body table-responsive p-0">
            <table class="table table-sm mb-0">
                <thead><tr><th>Site</th><th>From</th><th>To</th></tr></thead>
                <tbody>
                    @foreach($machine->siteAssignments->sortByDesc('assigned_from') as $assignment)
                        <tr>
                            <td>{{ $assignment->site?->name }}</td>
                            <td>{{ $assignment->assigned_from?->format('Y-m-d') }}</td>
                            <td>{{ $assignment->assigned_to?->format('Y-m-d') ?? 'Present' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif
@if ($errors->any())
    <div class="alert alert-danger">
        @foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach
    </div>
@endif
<button class="btn btn-primary">Save</button>
<a href="{{ route('machines.index') }}" class="btn btn-secondary">Back</a>
