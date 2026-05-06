<div class="form-row">
    <div class="form-group col-md-4"><label>Date</label><input type="date" name="date" class="form-control" value="{{ old('date', $entry?->date?->format('Y-m-d') ?? now()->toDateString()) }}" required></div>
    <div class="form-group col-md-4"><label>Machine</label><select name="machine_id" class="form-control" required>@foreach($machines as $machine)<option value="{{ $machine->id }}" @selected(old('machine_id', $entry?->machine_id) == $machine->id)>{{ $machine->name }}</option>@endforeach</select></div>
    <div class="form-group col-md-4"><label>Working Hours</label><input type="number" step="0.01" name="working_hours" class="form-control" value="{{ old('working_hours', $entry?->working_hours) }}" required></div>
</div>
<div class="form-group"><label>Remarks</label><textarea name="remarks" class="form-control" rows="3">{{ old('remarks', $entry?->remarks) }}</textarea></div>
@if ($errors->any())<div class="alert alert-danger">@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>@endif
<button class="btn btn-primary">Save</button> <a class="btn btn-secondary" href="{{ route('machine-hours.index') }}">Back</a>
