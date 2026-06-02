@if ($errors->any())
    <div class="alert alert-danger">
        @foreach($errors->all() as $error)
            <div>{{ $error }}</div>
        @endforeach
    </div>
@endif

<div class="form-row">
    <div class="form-group col-md-3">
        <label>Date</label>
        <input type="date" name="date" class="form-control" value="{{ old('date', $movement?->date?->format('Y-m-d') ?? now()->toDateString()) }}" required>
    </div>
    <div class="form-group col-md-3">
        <label>Part</label>
        <select name="spare_part_id" class="form-control" required>
            <option value="">-- Select Part --</option>
            @foreach($parts as $part)
                <option value="{{ $part->id }}" @selected(old('spare_part_id', $movement?->spare_part_id ?? $selectedPartId ?? null) == $part->id)>
                    {{ $part->name }} ({{ number_format((float) $part->current_stock, 2) }} {{ $part->unit }})
                </option>
            @endforeach
        </select>
    </div>
    <div class="form-group col-md-3">
        <label>Movement Type</label>
        <select name="movement_type" class="form-control" required>
            <option value="stock_in" @selected(old('movement_type', $movement?->movement_type ?? 'stock_in') === 'stock_in')>Stock In</option>
            <option value="stock_out" @selected(old('movement_type', $movement?->movement_type) === 'stock_out')>Stock Out</option>
        </select>
    </div>
    <div class="form-group col-md-3">
        <label>Quantity</label>
        <input type="number" step="0.01" min="0.01" name="quantity" class="form-control" value="{{ old('quantity', $movement?->quantity) }}" required>
    </div>
</div>

<div class="form-row">
    <div class="form-group col-md-4">
        <label>Machine (Optional)</label>
        <select name="machine_id" class="form-control">
            <option value="">-- Select Machine --</option>
            @foreach($machines as $machine)
                <option value="{{ $machine->id }}" @selected(old('machine_id', $movement?->machine_id) == $machine->id)>{{ $machine->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="form-group col-md-4">
        <label>Reference</label>
        <input type="text" name="reference" class="form-control" value="{{ old('reference', $movement?->reference) }}">
    </div>
    <div class="form-group col-md-4">
        <label>Remarks</label>
        <input type="text" name="remarks" class="form-control" value="{{ old('remarks', $movement?->remarks) }}">
    </div>
</div>

<button class="btn btn-primary">Save</button>
<a class="btn btn-secondary" href="{{ route('part-stock-movements.index') }}">Back</a>