@if ($errors->any())
    <div class="alert alert-danger">
        @foreach($errors->all() as $error)
            <div>{{ $error }}</div>
        @endforeach
    </div>
@endif

@php($stock = $stock ?? null)

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
        <input type="text" name="unit" class="form-control" value="{{ old('unit', $stock?->unit ?? 'liters') }}" required>
    </div>
    <div class="form-group col-md-2">
        <label>Minimum Stock</label>
        <input type="number" step="0.01" min="0" name="minimum_stock" class="form-control" value="{{ old('minimum_stock', $stock?->minimum_stock ?? 0) }}" required>
    </div>
    <div class="form-group col-md-2">
        <label>Location</label>
        <input type="text" name="location" class="form-control" value="{{ old('location', $stock?->location) }}">
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