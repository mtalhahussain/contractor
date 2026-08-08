@extends('adminlte::page')

@section('title', 'Diesel Rates')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Diesel Rate History</h1>
        <a href="{{ route('diesel-rates.create') }}" class="btn btn-primary">Add New Diesel Rate</a>
    </div>
@stop

@section('content')
    @if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    <div class="card"><div class="card-body table-responsive p-0">
        <table class="table table-striped mb-0">
            <thead><tr><th>Rate / Liter</th><th>Effective From</th><th>Action</th></tr></thead>
            <tbody>
                @foreach($rates as $rate)
                    <tr>
                        <td>{{ number_format($rate->rate_per_liter, 2) }}</td>
                        <td>{{ $rate->effective_from_date->format('d-M-y') }}</td>
                        <td>
                            <form action="{{ route('diesel-rates.destroy', $rate) }}" method="POST">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-danger" onclick="return confirm('Delete this rate record?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div></div>
    {{ $rates->links() }}
@stop
