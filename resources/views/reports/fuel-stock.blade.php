@extends('adminlte::page')
@section('title', 'Fuel Stock Report')
@section('content_header')<h1>Fuel Stock Report</h1>@stop
@section('content')
<form class="card card-body mb-3" method="GET">
    <div class="form-row">
        <div class="col-md-3"><div class="custom-control custom-checkbox mt-2"><input type="checkbox" name="low_stock_only" value="1" class="custom-control-input" id="low_stock_only" @checked($lowStockOnly)><label class="custom-control-label" for="low_stock_only">Show only low stock</label></div></div>
        <div class="col-md-2"><button class="btn btn-primary btn-block">Filter</button></div>
    </div>
</form>
<div class="mb-2"><a class="btn btn-sm btn-success" href="{{ route('reports.export', ['report' => 'fuel-stock', 'format' => 'excel'] + request()->query()) }}">Excel</a> <a class="btn btn-sm btn-danger" href="{{ route('reports.export', ['report' => 'fuel-stock', 'format' => 'pdf'] + request()->query()) }}">PDF</a></div>
<div class="card"><div class="card-body table-responsive p-0"><table class="table table-striped"><thead><tr><th>Tank / Store</th><th>Code</th><th>Current</th><th>Minimum</th><th>Unit</th><th>Location</th><th>Status</th></tr></thead><tbody>@forelse($rows as $row)<tr><td>{{ $row['stock'] }}</td><td>{{ $row['code'] }}</td><td>{{ number_format($row['current_stock'], 2) }}</td><td>{{ number_format($row['minimum_stock'], 2) }}</td><td>{{ $row['unit'] }}</td><td>{{ $row['location'] }}</td><td>@if($row['low_stock'])<span class="badge badge-danger">Low Stock</span>@else<span class="badge badge-success">OK</span>@endif</td></tr>@empty<tr><td colspan="7" class="text-center py-3">No records found.</td></tr>@endforelse</tbody></table></div></div>
@stop