@if ($errors->any())
    <div class="alert alert-danger">
        @foreach($errors->all() as $error)
            <div>{{ $error }}</div>
        @endforeach
    </div>
@endif

<div class="alert alert-light border">
    <strong>How to use:</strong> Record only actual part consumption on machines. Stock will reduce automatically after save.
</div>

<div class="form-row">
    <div class="form-group col-md-3">
        <label>Date</label>
        <input type="date" name="date" class="form-control" value="{{ old('date', $entry?->date?->format('Y-m-d') ?? now()->toDateString()) }}" required>
    </div>
    <div class="form-group col-md-3">
        <label>Machine</label>
        <select name="machine_id" class="form-control" required>
            <option value="">-- Select Machine --</option>
            @foreach($machines as $machine)
                <option value="{{ $machine->id }}" @selected(old('machine_id', $entry?->machine_id) == $machine->id)>{{ $machine->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="form-group col-md-3">
        <label>Part</label>
        <select name="spare_part_id" class="form-control" required>
            <option value="">-- Select Part --</option>
            @foreach($parts as $part)
                <option value="{{ $part->id }}" @selected(old('spare_part_id', $entry?->spare_part_id) == $part->id)>{{ $part->name }} ({{ number_format((float) $part->current_stock, 2) }} {{ $part->unit }})</option>
            @endforeach
        </select>
    </div>
    <div class="form-group col-md-3">
        <label>Quantity</label>
        <input type="number" step="0.01" min="0.01" name="quantity" class="form-control" value="{{ old('quantity', $entry?->quantity) }}" required>
        <small class="form-text text-muted">Enter consumed quantity only.</small>
    </div>
</div>

<div class="form-row">
    <div class="form-group col-md-4">
        <label>Usage Type</label>
        <select name="usage_type" class="form-control" required>
            <option value="maintenance" @selected(old('usage_type', $entry?->usage_type ?? 'maintenance') === 'maintenance')>Maintenance</option>
            <option value="repair" @selected(old('usage_type', $entry?->usage_type) === 'repair')>Repair</option>
            <option value="replacement" @selected(old('usage_type', $entry?->usage_type) === 'replacement')>Replacement</option>
            <option value="other" @selected(old('usage_type', $entry?->usage_type) === 'other')>Other</option>
        </select>
    </div>
    <div class="form-group col-md-4">
        <label>Reference</label>
        <input type="text" name="reference" class="form-control" value="{{ old('reference', $entry?->reference) }}">
    </div>
    <div class="form-group col-md-4">
        <label>Remarks</label>
        <input type="text" name="remarks" class="form-control" value="{{ old('remarks', $entry?->remarks) }}">
    </div>
</div>

<button class="btn btn-primary">Save</button>
<a class="btn btn-secondary" href="{{ route('machine-part-usages.index') }}">Back</a>