@extends('adminlte::page')
@section('title', 'Part Usage Report')
@section('content_header')<h1>Part Usage Report</h1>@stop
@section('content')
<form class="card card-body mb-3" method="GET">
    <div class="form-row">
        <div class="col-md-2"><input type="date" name="from" class="form-control" value="{{ request('from', $from) }}"></div>
        <div class="col-md-2"><input type="date" name="to" class="form-control" value="{{ request('to', $to) }}"></div>
        <div class="col-md-2"><select name="machine_id" class="form-control"><option value="">All Machines</option>@foreach($machines as $machine)<option value="{{ $machine->id }}" @selected(request('machine_id') == $machine->id)>{{ $machine->name }}</option>@endforeach</select></div>
        <div class="col-md-2"><select name="spare_part_id" class="form-control"><option value="">All Parts</option>@foreach($parts as $part)<option value="{{ $part->id }}" @selected(request('spare_part_id') == $part->id)>{{ $part->name }}</option>@endforeach</select></div>
        <div class="col-md-2"><select name="usage_type" class="form-control"><option value="">All Types</option><option value="maintenance" @selected(request('usage_type') === 'maintenance')>Maintenance</option><option value="repair" @selected(request('usage_type') === 'repair')>Repair</option><option value="replacement" @selected(request('usage_type') === 'replacement')>Replacement</option><option value="other" @selected(request('usage_type') === 'other')>Other</option></select></div>
        <div class="col-md-2"><button class="btn btn-primary btn-block">Filter</button></div>
    </div>
</form>
<div class="mb-2"><a class="btn btn-sm btn-success" href="{{ route('reports.export', ['report' => 'part-usage', 'format' => 'excel'] + request()->query()) }}">Excel</a> <a class="btn btn-sm btn-danger" href="{{ route('reports.export', ['report' => 'part-usage', 'format' => 'pdf'] + request()->query()) }}">PDF</a></div>
<div class="card"><div class="card-body table-responsive p-0"><table class="table table-striped"><thead><tr><th>Date</th><th>Machine</th><th>Part</th><th>Type</th><th>Quantity</th><th>Reference</th><th>Remarks</th></tr></thead><tbody>@foreach($rows as $row)<tr><td>{{ \Carbon\Carbon::parse($row['date'])->format('d-M-y') }}</td><td>{{ $row['machine'] }}</td><td>{{ $row['part'] }}</td><td>{{ ucfirst($row['usage_type']) }}</td><td>{{ number_format($row['quantity'], 2) }}</td><td>{{ $row['reference'] }}</td><td>{{ $row['remarks'] }}</td></tr>@endforeach</tbody></table></div></div>
@stop
