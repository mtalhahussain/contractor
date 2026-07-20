@extends('adminlte::page')
@section('title', 'Machine-wise Part Consumption')
@section('content_header')<h1>Machine-wise Part Consumption</h1>@stop
@section('content')
<form class="card card-body mb-3" method="GET">
    <div class="form-row">
        <div class="col-md-2"><input type="date" name="from" class="form-control" value="{{ request('from', $from) }}"></div>
        <div class="col-md-2"><input type="date" name="to" class="form-control" value="{{ request('to', $to) }}"></div>
        <div class="col-md-3"><select name="machine_id" class="form-control"><option value="">All Machines</option>@foreach($machines as $machine)<option value="{{ $machine->id }}" @selected(request('machine_id') == $machine->id)>{{ $machine->name }}</option>@endforeach</select></div>
        <div class="col-md-3"><select name="spare_part_id" class="form-control"><option value="">All Parts</option>@foreach($parts as $part)<option value="{{ $part->id }}" @selected(request('spare_part_id') == $part->id)>{{ $part->name }}</option>@endforeach</select></div>
        <div class="col-md-2"><button class="btn btn-primary btn-block">Filter</button></div>
    </div>
</form>
<div class="mb-2"><a class="btn btn-sm btn-success" href="{{ route('reports.export', ['report' => 'machine-parts', 'format' => 'excel'] + request()->query()) }}">Excel</a> <a class="btn btn-sm btn-danger" href="{{ route('reports.export', ['report' => 'machine-parts', 'format' => 'pdf'] + request()->query()) }}">PDF</a></div>
<div class="card"><div class="card-body table-responsive p-0"><table class="table table-striped"><thead><tr><th>Machine</th><th>Part</th><th>Transactions</th><th>Total Quantity</th></tr></thead><tbody>@foreach($rows as $row)<tr><td>{{ $row['machine'] }}</td><td>{{ $row['part'] }}</td><td>{{ $row['usage_count'] }}</td><td>{{ number_format($row['total_quantity'], 2) }}</td></tr>@endforeach</tbody></table></div></div>
@stop