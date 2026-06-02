@extends('adminlte::page')

@section('title', 'Spare Parts')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Spare Parts</h1>
        <a href="{{ route('spare-parts.create') }}" class="btn btn-primary">Add Spare Part</a>
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
                        <th>Part</th><th>Part No</th><th>Category</th><th>Stock</th><th>Min Stock</th><th>Location</th><th>Status</th><th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($parts as $part)
                        <tr>
                            <td>{{ $part->name }}</td>
                            <td>{{ $part->part_number }}</td>
                            <td>{{ $part->category }}</td>
                            <td>{{ number_format((float) $part->current_stock, 2) }} {{ $part->unit }}</td>
                            <td>{{ number_format((float) $part->minimum_stock, 2) }} {{ $part->unit }}</td>
                            <td>{{ $part->location }}</td>
                            <td>
                                @if ((float) $part->current_stock <= (float) $part->minimum_stock)
                                    <span class="badge badge-danger">Low Stock</span>
                                @else
                                    <span class="badge badge-success">OK</span>
                                @endif
                            </td>
                            <td class="text-nowrap">
                                <a href="{{ route('spare-parts.edit', $part) }}" class="btn btn-sm btn-info">Edit</a>
                                <form action="{{ route('spare-parts.destroy', $part) }}" method="POST" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-danger" onclick="return confirm('Delete spare part?')">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{ $parts->links() }}
@stop