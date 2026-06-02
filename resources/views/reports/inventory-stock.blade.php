@extends('adminlte::page')
@section('title', 'Inventory Stock Report')
@section('content_header')<h1>Inventory Stock Report</h1>@stop
@section('content')
<form class="card card-body mb-3" method="GET">
    <div class="form-row align-items-center">
        <div class="col-md-3">
            <div class="form-check mt-2">
                <input class="form-check-input" type="checkbox" name="low_stock_only" value="1" id="low_stock_only" @checked($lowStockOnly)>
                <label class="form-check-label" for="low_stock_only">Low stock only</label>
            </div>
        </div>
        <div class="col-md-3"><button class="btn btn-primary btn-block">Filter</button></div>
    </div>
</form>
<div class="mb-2"><a class="btn btn-sm btn-success" href="{{ route('reports.export', ['report' => 'inventory-stock', 'format' => 'excel'] + request()->query()) }}">Excel</a> <a class="btn btn-sm btn-danger" href="{{ route('reports.export', ['report' => 'inventory-stock', 'format' => 'pdf'] + request()->query()) }}">PDF</a></div>
<div class="card"><div class="card-body table-responsive p-0"><table class="table table-striped"><thead><tr><th>Part</th><th>Part No</th><th>Category</th><th>Current Stock</th><th>Minimum Stock</th><th>Unit</th><th>Location</th><th>Status</th></tr></thead><tbody>@foreach($rows as $row)<tr><td>{{ $row['part'] }}</td><td>{{ $row['part_number'] }}</td><td>{{ $row['category'] }}</td><td>{{ number_format($row['current_stock'], 2) }}</td><td>{{ number_format($row['minimum_stock'], 2) }}</td><td>{{ $row['unit'] }}</td><td>{{ $row['location'] }}</td><td>@if($row['low_stock'])<span class="badge badge-danger">Low Stock</span>@else<span class="badge badge-success">OK</span>@endif</td></tr>@endforeach</tbody></table></div></div>
@stop