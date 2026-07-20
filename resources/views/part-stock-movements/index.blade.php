@extends('adminlte::page')

@section('title', 'Stock In / Out')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1>Stock In / Out</h1>
    <a href="{{ route('part-stock-movements.create') }}" class="btn btn-primary">Add Stock Movement</a>
</div>
@stop

@section('content')
@if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if (session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
<div class="alert alert-info">
    <strong>Tip:</strong> Use <em>Stock In</em> when new parts arrive and <em>Stock Out</em> for manual deductions. For machine jobs, prefer <em>Machine Part Usage</em> so stock updates automatically.
</div>
<div class="card"><div class="card-body table-responsive p-0">
<table class="table table-striped mb-0">
    <thead><tr><th>Date</th><th>Part</th><th>Type</th><th>Qty</th><th>Machine</th><th>Balance After</th><th>Reference</th><th>Action</th></tr></thead>
    <tbody>
    @foreach($movements as $movement)
        <tr>
            <td>{{ $movement->date->format('Y-m-d') }}</td>
            <td>{{ $movement->sparePart?->name }}</td>
            <td>
                @if ($movement->movement_type === 'usage')
                    <span class="badge badge-warning">Usage</span>
                @elseif ($movement->movement_type === 'stock_in')
                    <span class="badge badge-success">Stock In</span>
                @else
                    <span class="badge badge-danger">Stock Out</span>
                @endif
            </td>
            <td>{{ number_format((float) $movement->quantity, 2) }}</td>
            <td>{{ $movement->machine?->name }}</td>
            <td>{{ number_format((float) $movement->balance_after, 2) }}</td>
            <td>{{ $movement->reference }}</td>
            <td class="text-nowrap">
                @if ($movement->machine_part_usage_id)
                    <span class="text-muted">Managed by usage entry</span>
                @else
                    <a class="btn btn-sm btn-info" href="{{ route('part-stock-movements.edit', $movement) }}">Edit</a>
                    <form action="{{ route('part-stock-movements.destroy', $movement) }}" method="POST" class="d-inline">@csrf @method('DELETE')
                        <button class="btn btn-sm btn-danger" onclick="return confirm('Delete stock movement?')">Delete</button>
                    </form>
                @endif
            </td>
        </tr>
    @endforeach
    </tbody>
</table>
</div></div>
{{ $movements->links() }}
@stop