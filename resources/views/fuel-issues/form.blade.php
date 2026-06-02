@if ($errors->any())
    <div class="alert alert-danger">
        @foreach($errors->all() as $error)
            <div>{{ $error }}</div>
        @endforeach
    </div>
@endif

@php($issue = $issue ?? null)

<div class="form-row">
    <div class="form-group col-md-2">
        <label>Date</label>
        <input type="date" name="date" class="form-control" value="{{ old('date', $issue?->date?->format('Y-m-d') ?? now()->toDateString()) }}" required>
    </div>
    <div class="form-group col-md-4">
        <label>Tank / Store</label>
        <select name="fuel_stock_id" class="form-control" required>
            <option value="">-- Select Tank / Store --</option>
            @foreach($stocks as $stock)
                <option value="{{ $stock->id }}" @selected(old('fuel_stock_id', $issue?->fuel_stock_id) == $stock->id)>
                    {{ $stock->name }} ({{ number_format((float) $stock->current_stock, 2) }} {{ $stock->unit }})
                </option>
            @endforeach
        </select>
    </div>
    <div class="form-group col-md-3">
        <label>Consumer Type</label>
        <select name="consumer_type" id="consumer_type" class="form-control" required>
            @php($consumerType = old('consumer_type', $issue?->consumer_type ?? 'machine'))
            <option value="machine" @selected($consumerType === 'machine')>Machine</option>
            <option value="generator" @selected($consumerType === 'generator')>Generator</option>
            <option value="vehicle" @selected($consumerType === 'vehicle')>Vehicle</option>
            <option value="equipment" @selected($consumerType === 'equipment')>Equipment</option>
            <option value="other" @selected($consumerType === 'other')>Other</option>
        </select>
    </div>
    <div class="form-group col-md-3">
        <label>Quantity</label>
        <input type="number" step="0.01" min="0.01" name="quantity" class="form-control" value="{{ old('quantity', $issue?->quantity) }}" required>
    </div>
</div>

<div class="form-row">
    <div class="form-group col-md-4" id="machine_field">
        <label>Machine</label>
        <select name="machine_id" class="form-control">
            <option value="">-- Select Machine --</option>
            @foreach($machines as $machine)
                <option value="{{ $machine->id }}" @selected(old('machine_id', $issue?->machine_id) == $machine->id)>{{ $machine->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="form-group col-md-4" id="consumer_name_field">
        <label>Consumer Name</label>
        <input type="text" name="consumer_name" class="form-control" value="{{ old('consumer_name', $issue?->consumer_name) }}" placeholder="e.g. DG-1, Pickup-2, Pump Set">
    </div>
    <div class="form-group col-md-4">
        <label>Reference</label>
        <input type="text" name="reference" class="form-control" value="{{ old('reference', $issue?->reference) }}">
    </div>
</div>

<div class="form-group">
    <label>Remarks</label>
    <textarea name="remarks" class="form-control" rows="2">{{ old('remarks', $issue?->remarks) }}</textarea>
</div>

<button class="btn btn-primary">Save</button>
<a class="btn btn-secondary" href="{{ route('fuel-issues.index') }}">Back</a>

@push('js')
<script>
    (function () {
        function toggleConsumerFields() {
            const type = document.getElementById('consumer_type')?.value;
            const machineField = document.getElementById('machine_field');
            const consumerNameField = document.getElementById('consumer_name_field');

            if (!machineField || !consumerNameField) {
                return;
            }

            if (type === 'machine') {
                machineField.style.display = '';
                consumerNameField.style.display = 'none';
            } else {
                machineField.style.display = 'none';
                consumerNameField.style.display = '';
            }
        }

        document.addEventListener('DOMContentLoaded', function () {
            const select = document.getElementById('consumer_type');
            if (select) {
                select.addEventListener('change', toggleConsumerFields);
            }
            toggleConsumerFields();
        });
    })();
</script>
@endpush