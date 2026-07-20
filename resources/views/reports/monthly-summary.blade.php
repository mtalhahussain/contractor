@extends('adminlte::page')
@section('title', 'Monthly Summary')
@section('content_header')<h1>Monthly Summary</h1>@stop
@section('content')
<form class="card card-body mb-3" method="GET"><div class="form-row"><div class="col-md-2"><input type="date" name="from" class="form-control" value="{{ request('from', $from) }}"></div><div class="col-md-2"><input type="date" name="to" class="form-control" value="{{ request('to', $to) }}"></div><div class="col-md-6"></div><div class="col-md-2"><button class="btn btn-primary btn-block">Filter</button></div></div></form>
<div class="mb-2"><a class="btn btn-sm btn-success" href="{{ route('reports.export', ['report' => 'monthly-summary', 'format' => 'excel'] + request()->query()) }}">Excel</a> <a class="btn btn-sm btn-danger" href="{{ route('reports.export', ['report' => 'monthly-summary', 'format' => 'pdf'] + request()->query()) }}">PDF</a></div>
<div class="card"><div class="card-body table-responsive p-0"><table class="table table-striped"><thead><tr><th>Month</th><th>Total Hours</th><th>Total Diesel</th><th>Total Earning</th><th>Cost</th><th>Balance</th></tr></thead><tbody>@foreach($rows as $row)<tr><td>{{ $row['month'] }}</td><td>{{ number_format($row['total_hours'], 2) }}</td><td>{{ number_format($row['total_diesel'], 2) }}</td><td>{{ number_format($row['total_earning'], 2) }}</td><td>{{ number_format($row['cost'], 2) }}</td><td>{{ number_format($row['balance'], 2) }}</td></tr>@endforeach</tbody></table></div></div>
@stop
