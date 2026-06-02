@extends('adminlte::page')

@section('title', 'Fuel Stocks')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Fuel Stocks</h1>
        <a href="{{ route('fuel-stocks.create') }}" class="btn btn-primary">Add Fuel Stock</a>
    </div>
@stop

@section('content')
    @if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if (session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

    <div class="card">
        <div class="card-body table-responsive p-0">
            <table class="table table-striped mb-0">
                <thead>
                    <tr>
                        <th>Tank / Store</th><th>Code</th><th>Stock</th><th>Min Stock</th><th>Location</th><th>Status</th><th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($stocks as $stock)
                        <tr>
                            <td>{{ $stock->name }}</td>
                            <td>{{ $stock->code }}</td>
                            <td>{{ number_format((float) $stock->current_stock, 2) }} {{ $stock->unit }}</td>
                            <td>{{ number_format((float) $stock->minimum_stock, 2) }} {{ $stock->unit }}</td>
                            <td>{{ $stock->location }}</td>
                            <td>
                                @if ((float) $stock->current_stock <= (float) $stock->minimum_stock)
                                    <span class="badge badge-danger">Low Stock</span>
                                @else
                                    <span class="badge badge-success">OK</span>
                                @endif
                            </td>
                            <td class="text-nowrap">
                                <a href="{{ route('fuel-stock-movements.create', ['fuel_stock_id' => $stock->id]) }}" class="btn btn-sm btn-secondary">Add In/Out</a>
                                <a href="{{ route('fuel-stocks.edit', $stock) }}" class="btn btn-sm btn-info">Edit</a>
                                <form action="{{ route('fuel-stocks.destroy', $stock) }}" method="POST" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-danger" onclick="return confirm('Delete fuel stock?')">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center py-3">No fuel stock found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{ $stocks->links() }}
@stop