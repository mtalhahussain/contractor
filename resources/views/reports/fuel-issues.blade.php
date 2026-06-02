@extends('adminlte::page')
@section('title', 'Fuel Issue Report')
@section('content_header')<h1>Fuel Issue Report</h1>@stop
@section('content')
<form class="card card-body mb-3" method="GET">
    <div class="form-row">
        <div class="col-md-2"><input type="date" name="from" class="form-control" value="{{ request('from', $from) }}"></div>
        <div class="col-md-2"><input type="date" name="to" class="form-control" value="{{ request('to', $to) }}"></div>
        <div class="col-md-3"><select name="fuel_stock_id" class="form-control"><option value="">All Tanks / Stores</option>@foreach($stocks as $stock)<option value="{{ $stock->id }}" @selected(request('fuel_stock_id') == $stock->id)>{{ $stock->name }}</option>@endforeach</select></div>
        <div class="col-md-2"><select name="consumer_type" class="form-control"><option value="">All Types</option><option value="machine" @selected(request('consumer_type') === 'machine')>Machine</option><option value="generator" @selected(request('consumer_type') === 'generator')>Generator</option><option value="vehicle" @selected(request('consumer_type') === 'vehicle')>Vehicle</option><option value="equipment" @selected(request('consumer_type') === 'equipment')>Equipment</option><option value="other" @selected(request('consumer_type') === 'other')>Other</option></select></div>
        <div class="col-md-3"><select name="machine_id" class="form-control"><option value="">All Machines</option>@foreach($machines as $machine)<option value="{{ $machine->id }}" @selected(request('machine_id') == $machine->id)>{{ $machine->name }}</option>@endforeach</select></div>
    </div>
    <div class="form-row mt-2"><div class="col-md-2"><button class="btn btn-primary btn-block">Filter</button></div></div>
</form>
<div class="mb-2"><a class="btn btn-sm btn-success" href="{{ route('reports.export', ['report' => 'fuel-issues', 'format' => 'excel'] + request()->query()) }}">Excel</a> <a class="btn btn-sm btn-danger" href="{{ route('reports.export', ['report' => 'fuel-issues', 'format' => 'pdf'] + request()->query()) }}">PDF</a></div>
<div class="card"><div class="card-body table-responsive p-0"><table class="table table-striped"><thead><tr><th>Date</th><th>Tank / Store</th><th>Consumer Type</th><th>Consumer</th><th>Quantity</th><th>Reference</th><th>Remarks</th></tr></thead><tbody>@forelse($rows as $row)<tr><td>{{ $row['date'] }}</td><td>{{ $row['stock'] }}</td><td>{{ ucfirst($row['consumer_type']) }}</td><td>{{ $row['consumer'] }}</td><td>{{ number_format($row['quantity'], 2) }}</td><td>{{ $row['reference'] }}</td><td>{{ $row['remarks'] }}</td></tr>@empty<tr><td colspan="7" class="text-center py-3">No records found.</td></tr>@endforelse</tbody></table></div></div>
@stop