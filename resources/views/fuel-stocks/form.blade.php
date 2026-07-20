@if ($errors->any())
    <div class="alert alert-danger">
        @foreach($errors->all() as $error)
            <div>{{ $error }}</div>
        @endforeach
    </div>
@endif

<div class="alert alert-light border">
    <strong>How to use:</strong> Create tank/store master here. Use <em>Fuel Stock In / Out</em> for refill and <em>Fuel Issues</em> for consumption.
</div>

@php($stock = $stock ?? null)
@php($selectedUnit = old('unit', $stock?->unit ?? 'liters'))

<div class="form-row">
    <div class="form-group col-md-4">
        <label>Name</label>
        <input type="text" name="name" class="form-control" value="{{ old('name', $stock?->name) }}" required>
    </div>
    <div class="form-group col-md-2">
        <label>Code</label>
        <input type="text" name="code" class="form-control" value="{{ old('code', $stock?->code) }}">
    </div>
    <div class="form-group col-md-2">
        <label>Unit</label>
        <select name="unit" class="form-control" required>
            @foreach($units as $unit)
                <option value="{{ $unit }}" @selected($selectedUnit === $unit)>{{ strtoupper($unit) }}</option>
            @endforeach
            @if($selectedUnit && !in_array($selectedUnit, $units, true))
                <option value="{{ $selectedUnit }}" selected>{{ strtoupper($selectedUnit) }} (legacy)</option>
            @endif
        </select>
        <small class="form-text text-muted">Usually: liters</small>
    </div>
    <div class="form-group col-md-2">
        <label>Minimum Stock</label>
        <input type="number" step="0.01" min="0" name="minimum_stock" class="form-control" value="{{ old('minimum_stock', $stock?->minimum_stock ?? 0) }}" required>
    </div>
    <div class="form-group col-md-2">
        <label>Storage Location</label>
        <input type="text" name="location" class="form-control" value="{{ old('location', $stock?->location) }}" placeholder="e.g. Main Tank Yard, Site-B Diesel Point">
    </div>
</div>

@if(!isset($stock))
<div class="form-row">
    <div class="form-group col-md-3">
        <label>Opening Stock</label>
        <input type="number" step="0.01" min="0" name="current_stock" class="form-control" value="{{ old('current_stock', 0) }}" required>
    </div>
</div>
@else
<div class="form-row">
    <div class="form-group col-md-3">
        <label>Current Stock</label>
        <input type="text" class="form-control" value="{{ number_format((float) $stock->current_stock, 2) }} {{ $stock->unit }}" readonly>
    </div>
    <div class="form-group col-md-6 d-flex align-items-end">
        <a href="{{ route('fuel-stock-movements.create', ['fuel_stock_id' => $stock->id]) }}" class="btn btn-outline-secondary">Add Stock In / Out Entry</a>
    </div>
</div>
@endif

<div class="form-group">
    <label>Notes</label>
    <textarea name="notes" class="form-control" rows="3">{{ old('notes', $stock?->notes) }}</textarea>
</div>

<button class="btn btn-primary">Save</button>
<a class="btn btn-secondary" href="{{ route('fuel-stocks.index') }}">Back</a>