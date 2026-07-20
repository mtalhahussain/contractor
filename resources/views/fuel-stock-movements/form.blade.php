@if ($errors->any())
    <div class="alert alert-danger">
        @foreach($errors->all() as $error)
            <div>{{ $error }}</div>
        @endforeach
    </div>
@endif

<div class="alert alert-light border">
    <strong>How to use:</strong> Use this for refill/manual adjustments. For day-to-day fuel consumption, use <em>Fuel Issues</em> screen.
</div>

@php($movement = $movement ?? null)
@php($selectedStockId = $selectedStockId ?? null)

<div class="form-row">
    <div class="form-group col-md-3">
        <label>Date</label>
        <input type="date" name="date" class="form-control" value="{{ old('date', $movement?->date?->format('Y-m-d') ?? now()->toDateString()) }}" required>
    </div>
    <div class="form-group col-md-4">
        <label>Tank / Store</label>
        <select name="fuel_stock_id" class="form-control" required>
            <option value="">-- Select Tank / Store --</option>
            @foreach($stocks as $stock)
                <option value="{{ $stock->id }}" @selected(old('fuel_stock_id', $movement?->fuel_stock_id ?? $selectedStockId ?? null) == $stock->id)>
                    {{ $stock->name }} ({{ number_format((float) $stock->current_stock, 2) }} {{ $stock->unit }})
                </option>
            @endforeach
        </select>
    </div>
    <div class="form-group col-md-3">
        <label>Movement Type</label>
        <select name="movement_type" class="form-control" required>
            <option value="stock_in" @selected(old('movement_type', $movement?->movement_type ?? 'stock_in') === 'stock_in')>Stock In (Refill)</option>
            <option value="stock_out" @selected(old('movement_type', $movement?->movement_type) === 'stock_out')>Stock Out (Adjustment)</option>
        </select>
    </div>
    <div class="form-group col-md-2">
        <label>Quantity (Liters)</label>
        <input type="number" step="0.01" min="0.01" name="quantity" class="form-control" value="{{ old('quantity', $movement?->quantity) }}" required>
    </div>
</div>

<div class="form-row">
    <div class="form-group col-md-6">
        <label>Reference</label>
        <input type="text" name="reference" class="form-control" value="{{ old('reference', $movement?->reference) }}">
    </div>
    <div class="form-group col-md-6">
        <label>Remarks</label>
        <input type="text" name="remarks" class="form-control" value="{{ old('remarks', $movement?->remarks) }}">
    </div>
</div>

<button class="btn btn-primary">Save</button>
<a class="btn btn-secondary" href="{{ route('fuel-stock-movements.index') }}">Back</a>