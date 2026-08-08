@php
	$methodOptions = [
		'cash' => 'Cash',
		'bank_transfer' => 'Bank Transfer',
		'cheque' => 'Cheque',
		'easypaisa' => 'EasyPaisa',
		'jazzcash' => 'JazzCash',
	];

	$storedMethod = old('payment_method', $payment?->payment_method);
	$storedOtherMethod = old('payment_method_other');

	if ($storedOtherMethod !== null) {
		$selectedMethod = $storedMethod;
		$otherMethod = $storedOtherMethod;
	} elseif ($storedMethod && !array_key_exists($storedMethod, $methodOptions)) {
		$selectedMethod = 'other';
		$otherMethod = $storedMethod;
	} else {
		$selectedMethod = $storedMethod;
		$otherMethod = null;
	}
@endphp

<div class="form-row"><div class="form-group col-md-4"><label>Date</label><input type="date" name="date" class="form-control" value="{{ old('date', $payment?->date?->format('Y-m-d') ?? now()->toDateString()) }}" required></div><div class="form-group col-md-4"><label>Machine (Optional)</label><select name="machine_id" class="form-control"><option value="">-- Select --</option>@foreach($machines as $machine)<option value="{{ $machine->id }}" @selected(old('machine_id', $payment?->machine_id) == $machine->id)>{{ $machine->name }}</option>@endforeach</select></div><div class="form-group col-md-4"><label>Party Name</label><input type="text" name="party_name" class="form-control" value="{{ old('party_name', $payment?->party_name) }}" required></div></div>
<div class="form-row">
	<div class="form-group col-md-4"><label>Amount Received</label><input type="number" step="0.01" name="amount_received" class="form-control" value="{{ old('amount_received', $payment?->amount_received) }}" required></div>
	<div class="form-group col-md-4"><label>Payment Method</label><select name="payment_method" id="payment_method" class="form-control"><option value="">-- Select --</option>@foreach($methodOptions as $methodKey => $methodLabel)<option value="{{ $methodKey }}" @selected($selectedMethod === $methodKey)>{{ $methodLabel }}</option>@endforeach<option value="other" @selected($selectedMethod === 'other')>Other</option></select></div>
	<div class="form-group col-md-4" id="payment_method_other_wrap" style="display: {{ $selectedMethod === 'other' ? 'block' : 'none' }};"><label>Other Payment Method</label><input type="text" name="payment_method_other" id="payment_method_other" class="form-control" value="{{ $otherMethod }}" placeholder="Enter payment method"></div>
</div>
<div class="form-group"><label>Remarks</label><textarea name="remarks" class="form-control" rows="3">{{ old('remarks', $payment?->remarks) }}</textarea></div>
@if ($errors->any())<div class="alert alert-danger">@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>@endif
<button class="btn btn-primary">Save</button> <a class="btn btn-secondary" href="{{ route('payments.index') }}">Back</a>

<script>
document.addEventListener('DOMContentLoaded', function () {
	const paymentMethod = document.getElementById('payment_method');
	const otherWrap = document.getElementById('payment_method_other_wrap');
	const otherInput = document.getElementById('payment_method_other');

	const toggleOtherInput = () => {
		const isOther = paymentMethod && paymentMethod.value === 'other';
		otherWrap.style.display = isOther ? 'block' : 'none';
		otherInput.required = isOther;

		if (!isOther) {
			otherInput.value = '';
		}
	};

	if (paymentMethod) {
		paymentMethod.addEventListener('change', toggleOtherInput);
		toggleOtherInput();
	}
});
</script>
