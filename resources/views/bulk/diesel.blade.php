@extends('adminlte::page')
@section('title', 'Bulk Diesel Entry')
@section('content_header')<h1>Bulk Diesel Entry</h1>@stop
@section('content')
@if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
<div class="card"><div class="card-body">
<form action="{{ route('bulk.diesel.store') }}" method="POST">@csrf
<div class="form-group"><label>Date</label><input type="date" name="date" class="form-control" value="{{ old('date', now()->toDateString()) }}" required></div>
<div class="table-responsive"><table class="table table-bordered"><thead><tr><th>Machine</th><th>Diesel Liters</th><th>Remarks</th></tr></thead><tbody>
@foreach($machines as $machine)
<tr>
    <td>{{ $machine->name }}</td>
    <td><input type="number" step="0.01" min="0" name="liters[{{ $machine->id }}]" class="form-control" placeholder="0.00"></td>
    <td><input type="text" name="remarks[{{ $machine->id }}]" class="form-control" placeholder="Optional"></td>
</tr>
@endforeach
</tbody></table></div>
<button class="btn btn-primary btn-lg">Save All</button>
</form>
</div></div>
@stop
