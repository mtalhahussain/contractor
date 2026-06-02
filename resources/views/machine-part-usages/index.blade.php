@extends('adminlte::page')

@section('title', 'Machine Part Usage')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1>Machine Part Usage</h1>
    <a href="{{ route('machine-part-usages.create') }}" class="btn btn-primary">Add Part Usage</a>
</div>
@stop

@section('content')
@if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if (session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
<div class="card"><div class="card-body table-responsive p-0">
<table class="table table-striped mb-0">
    <thead><tr><th>Date</th><th>Machine</th><th>Part</th><th>Type</th><th>Quantity</th><th>Reference</th><th>Remarks</th><th>Action</th></tr></thead>
    <tbody>
    @foreach($entries as $entry)
        <tr>
            <td>{{ $entry->date->format('Y-m-d') }}</td>
            <td>{{ $entry->machine?->name }}</td>
            <td>{{ $entry->sparePart?->name }}</td>
            <td>{{ ucfirst($entry->usage_type) }}</td>
            <td>{{ number_format((float) $entry->quantity, 2) }}</td>
            <td>{{ $entry->reference }}</td>
            <td>{{ $entry->remarks }}</td>
            <td class="text-nowrap">
                <a class="btn btn-sm btn-info" href="{{ route('machine-part-usages.edit', $entry) }}">Edit</a>
                <form action="{{ route('machine-part-usages.destroy', $entry) }}" method="POST" class="d-inline">@csrf @method('DELETE')
                    <button class="btn btn-sm btn-danger" onclick="return confirm('Delete part usage?')">Delete</button>
                </form>
            </td>
        </tr>
    @endforeach
    </tbody>
</table>
</div></div>
{{ $entries->links() }}
@stop