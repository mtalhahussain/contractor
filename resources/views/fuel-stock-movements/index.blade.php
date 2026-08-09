@extends('adminlte::page')
@section('title', 'Fuel Stock In / Out')
@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Fuel Stock In / Out</h1>
        <a href="{{ route('fuel-stock-movements.create') }}" class="btn btn-primary">Add Movement</a>
    </div>
@stop
@section('content')
    @if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if (session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
    <form method="GET" class="card card-body mb-3 p-3">
        <div class="form-row align-items-end">
            <div class="col-md-5">
                <label class="mb-1 small font-weight-bold">Tank / Store</label>
                <select name="fuel_stock_id" class="form-control form-control-sm">
                    <option value="">All Tanks</option>
                    @foreach($stocks as $s)
                        <option value="{{ $s->id }}" {{ request('fuel_stock_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="mb-1 small font-weight-bold">Month</label>
                <input type="month" name="month" class="form-control form-control-sm" value="{{ request('month') }}">
            </div>
            <div class="col-md-3">
                <button class="btn btn-primary btn-sm btn-block">Filter</button>
                @if(request()->anyFilled(['fuel_stock_id','month']))
                    <a href="{{ route('fuel-stock-movements.index') }}" class="btn btn-secondary btn-sm btn-block mt-1">Clear</a>
                @endif
            </div>
        </div>
    </form>
    <div class="alert alert-info">
        <strong>Tip:</strong> Use this screen for fuel refill/manual adjustments. For daily consumption issue, use <em>Fuel Issues</em> so stock is deducted automatically.
    </div>
    <div class="card"><div class="card-body table-responsive p-0"><table class="table table-striped mb-0"><thead><tr><th>Date</th><th>Tank / Store</th><th>Type</th><th>Quantity (L)</th><th>Balance (L)</th><th>Reference</th><th>Action</th></tr></thead><tbody>@forelse($movements as $movement)<tr><td>{{ $movement->date?->format('d-M-y') }}</td><td>{{ $movement->fuelStock?->name }}</td><td>{{ str_replace('_', ' ', ucfirst($movement->movement_type)) }}</td><td>{{ number_format((float) $movement->quantity, 2) }} L</td><td>{{ number_format((float) $movement->balance_after, 2) }} L</td><td>{{ $movement->reference }}</td><td class="text-nowrap">@if(!$movement->fuel_issue_id)<a href="{{ route('fuel-stock-movements.edit', $movement) }}" class="btn btn-sm btn-info">Edit</a><form action="{{ route('fuel-stock-movements.destroy', $movement) }}" method="POST" class="d-inline">@csrf @method('DELETE')<button class="btn btn-sm btn-danger" onclick="return confirm('Delete movement?')">Delete</button></form>@else<span class="badge badge-secondary">From Fuel Issue</span>@endif</td></tr>@empty<tr><td colspan="7" class="text-center py-3">No movement found.</td></tr>@endforelse</tbody></table></div></div>
    {{ $movements->links() }}
@stop