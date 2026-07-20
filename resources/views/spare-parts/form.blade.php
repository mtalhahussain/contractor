@if ($errors->any())
    <div class="alert alert-danger">
        @foreach($errors->all() as $error)
            <div>{{ $error }}</div>
        @endforeach
    </div>
@endif

<div class="alert alert-light border">
    <strong>How to use:</strong> Create part master once. After that, update quantities from <em>Stock In / Out</em> and <em>Machine Part Usage</em> screens.
</div>

<div class="form-row">
    @php($selectedCategory = old('category', $part?->category))
    @php($selectedUnit = old('unit', $part?->unit ?? 'pcs'))
    <div class="form-group col-md-4">
        <label>Part Name</label>
        <input type="text" name="name" class="form-control" value="{{ old('name', $part?->name) }}" required>
    </div>
    <div class="form-group col-md-4">
        <label>Part Number</label>
        <input type="text" name="part_number" class="form-control" value="{{ old('part_number', $part?->part_number) }}">
    </div>
    <div class="form-group col-md-4">
        <label>Category</label>
        <select name="category" class="form-control">
            <option value="">-- Select Category --</option>
            @foreach($categories as $category)
                <option value="{{ $category }}" @selected($selectedCategory === $category)>{{ $category }}</option>
            @endforeach
            @if($selectedCategory && !in_array($selectedCategory, $categories, true))
                <option value="{{ $selectedCategory }}" selected>{{ $selectedCategory }} (legacy)</option>
            @endif
        </select>
    </div>
</div>

<div class="form-row">
    <div class="form-group col-md-3">
        <label>Unit</label>
        <select name="unit" class="form-control" required>
            @foreach($units as $unit)
                <option value="{{ $unit }}" @selected($selectedUnit === $unit)>{{ strtoupper($unit) }}</option>
            @endforeach
            @if($selectedUnit && !in_array($selectedUnit, $units, true))
                <option value="{{ $selectedUnit }}" selected>{{ strtoupper($selectedUnit) }} (legacy)</option>
            @endif
        </select>
        <small class="form-text text-muted">Example: pcs, set, box</small>
    </div>
    <div class="form-group col-md-3">
        <label>{{ $part ? 'Current Stock' : 'Opening Stock' }}</label>
        @if ($part)
            <input type="text" class="form-control" value="{{ number_format((float) ($part?->current_stock ?? 0), 2) }}" readonly>
            <small class="form-text text-muted">Use Stock In / Out to change current stock.</small>
            <a href="{{ route('part-stock-movements.create', ['spare_part_id' => $part->id]) }}" class="btn btn-sm btn-outline-primary mt-2">Add Stock In / Out</a>
        @else
            <input type="number" step="0.01" min="0" name="current_stock" class="form-control" value="{{ old('current_stock', 0) }}" required>
        @endif
    </div>
    <div class="form-group col-md-3">
        <label>Minimum Stock</label>
        <input type="number" step="0.01" min="0" name="minimum_stock" class="form-control" value="{{ old('minimum_stock', $part?->minimum_stock ?? 0) }}" required>
    </div>
    <div class="form-group col-md-3">
        <label>Storage Location</label>
        <input type="text" name="location" class="form-control" value="{{ old('location', $part?->location) }}" placeholder="e.g. Main Store, Site-A Container, Rack B2">
    </div>
</div>

<div class="form-group">
    <label>Notes</label>
    <textarea name="notes" class="form-control" rows="3">{{ old('notes', $part?->notes) }}</textarea>
</div>

<button class="btn btn-primary">Save</button>
<a class="btn btn-secondary" href="{{ route('spare-parts.index') }}">Back</a>