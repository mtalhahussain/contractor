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
    <div class="card"><div class="card-body table-responsive p-0"><table class="table table-striped mb-0"><thead><tr><th>Date</th><th>Tank / Store</th><th>Type</th><th>Quantity (L)</th><th>Balance (L)</th><th>Reference</th><th>Action</th></tr></thead><tbody>@forelse($movements as $movement)<tr><td>{{ $movement->date?->format('Y-m-d') }}</td><td>{{ $movement->fuelStock?->name }}</td><td>{{ str_replace('_', ' ', ucfirst($movement->movement_type)) }}</td><td>{{ number_format((float) $movement->quantity, 2) }} L</td><td>{{ number_format((float) $movement->balance_after, 2) }} L</td><td>{{ $movement->reference }}</td><td class="text-nowrap">@if(!$movement->fuel_issue_id)<a href="{{ route('fuel-stock-movements.edit', $movement) }}" class="btn btn-sm btn-info">Edit</a><form action="{{ route('fuel-stock-movements.destroy', $movement) }}" method="POST" class="d-inline">@csrf @method('DELETE')<button class="btn btn-sm btn-danger" onclick="return confirm('Delete movement?')">Delete</button></form>@else<span class="badge badge-secondary">From Fuel Issue</span>@endif</td></tr>@empty<tr><td colspan="7" class="text-center py-3">No movement found.</td></tr>@endforelse</tbody></table></div></div>
    {{ $movements->links() }}
@stop