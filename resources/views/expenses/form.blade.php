@if ($errors->any())
    <div class="alert alert-danger">
        @foreach($errors->all() as $error)
            <div>{{ $error }}</div>
        @endforeach
    </div>
@endif

@php($selectedExpenseType = old('expense_type', $expense?->expense_type))

<div class="form-row">
    <div class="form-group col-md-6">
        <label>Date</label>
        <input
            type="date"
            name="date"
            class="form-control"
            value="{{ old('date', $expense?->date?->format('Y-m-d') ?? now()->toDateString()) }}"
            required
        >
    </div>

    <div class="form-group col-md-6">
        <label>Machine (Optional)</label>
        <select name="machine_id" class="form-control">
            <option value="">-- Select --</option>
            @foreach($machines as $machine)
                <option value="{{ $machine->id }}" @selected(old('machine_id', $expense?->machine_id) == $machine->id)>
                    {{ $machine->name }}
                </option>
            @endforeach
        </select>
    </div>
</div>

<div class="form-row">
    <div class="form-group col-md-6">
        <label>Expense Type</label>
        <select name="expense_type" class="form-control" required>
            <option value="">-- Select Expense Type --</option>
            @foreach($expenseTypes as $expenseType)
                <option value="{{ $expenseType }}" @selected($selectedExpenseType === $expenseType)>
                    {{ $expenseType }}
                </option>
            @endforeach
            @if($selectedExpenseType && !in_array($selectedExpenseType, $expenseTypes, true))
                <option value="{{ $selectedExpenseType }}" selected>{{ $selectedExpenseType }} (legacy)</option>
            @endif
        </select>
    </div>

    <div class="form-group col-md-6">
        <label>Amount</label>
        <input
            type="number"
            step="0.01"
            name="amount"
            class="form-control"
            value="{{ old('amount', $expense?->amount) }}"
            required
        >
    </div>
</div>

<div class="form-group">
    <label>Remarks</label>
    <textarea name="remarks" class="form-control" rows="3">{{ old('remarks', $expense?->remarks) }}</textarea>
</div>

<button class="btn btn-primary">Save</button>
<a class="btn btn-secondary" href="{{ route('expenses.index') }}">Back</a>
