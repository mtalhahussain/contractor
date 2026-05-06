@extends('adminlte::page')

@section('title', 'Machine Hours')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1>Machine Hours</h1>
    <a href="{{ route('machine-hours.create') }}" class="btn btn-primary">Add Machine Hours</a>
</div>
@stop

@section('content')
@if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
<div class="card"><div class="card-body table-responsive p-0">
<table class="table table-striped mb-0">
    <thead><tr><th>Date</th><th>Machine</th><th>Hours</th><th>Remarks</th><th>Action</th></tr></thead>
    <tbody>
    @foreach($entries as $entry)
        <tr>
            <td>{{ $entry->date->format('Y-m-d') }}</td>
            <td>{{ $entry->machine?->name }}</td>
            <td>{{ number_format($entry->working_hours, 2) }}</td>
            <td>{{ $entry->remarks }}</td>
            <td>
                <a class="btn btn-sm btn-info" href="{{ route('machine-hours.edit', $entry) }}">Edit</a>
                <form action="{{ route('machine-hours.destroy', $entry) }}" method="POST" class="d-inline">@csrf @method('DELETE')
                    <button class="btn btn-sm btn-danger" onclick="return confirm('Delete record?')">Delete</button>
                </form>
            </td>
        </tr>
    @endforeach
    </tbody>
</table>
</div></div>
{{ $entries->links() }}
@stop
