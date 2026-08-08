@extends('adminlte::page')

@section('title', 'Diesel Usage')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1>Diesel Usage</h1>
    <a href="{{ route('diesel-usage.create') }}" class="btn btn-primary">Add Diesel</a>
</div>
@stop

@section('content')
@if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
<div class="card mb-3">
    <div class="card-body">
        <form method="GET" action="{{ route('diesel-usage.index') }}" class="form-row align-items-end">
            <div class="form-group col-md-4">
                <label>Machine</label>
                <select name="machine_id" class="form-control">
                    <option value="">All Machines</option>
                    @foreach($machines as $machine)
                        <option value="{{ $machine->id }}" @selected((string) $selectedMachineId === (string) $machine->id)>{{ $machine->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group col-md-3">
                <label>Month</label>
                <input type="month" name="month" class="form-control" value="{{ $selectedMonth }}">
            </div>
            <div class="form-group col-md-5">
                <button type="submit" class="btn btn-primary">Filter</button>
                <a href="{{ route('diesel-usage.index') }}" class="btn btn-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>
<div class="card"><div class="card-body table-responsive p-0">
<table class="table table-striped mb-0">
    <thead><tr><th>Date</th><th>Machine</th><th>Liters</th><th>Remarks</th><th>Action</th></tr></thead>
    <tbody>
    @foreach($entries as $entry)
        <tr>
            <td>{{ $entry->date->format('d-M-y') }}</td>
            <td>{{ $entry->machine?->name }}</td>
            <td>{{ number_format($entry->diesel_liters, 2) }}</td>
            <td>{{ $entry->remarks }}</td>
            <td>
                <a class="btn btn-sm btn-info" href="{{ route('diesel-usage.edit', $entry) }}">Edit</a>
                <form action="{{ route('diesel-usage.destroy', $entry) }}" method="POST" class="d-inline">@csrf @method('DELETE')
                    <button class="btn btn-sm btn-danger" onclick="return confirm('Delete record?')">Delete</button>
                </form>
            </td>
        </tr>
    @endforeach
    @if($entries->isEmpty())
        <tr>
            <td colspan="5" class="text-center text-muted py-3">No entries found for selected filters.</td>
        </tr>
    @endif
    </tbody>
</table>
</div></div>
{{ $entries->links() }}
@stop
