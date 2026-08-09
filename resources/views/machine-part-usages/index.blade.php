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
<form method="GET" class="card card-body mb-3 p-3">
    <div class="form-row align-items-end">
        <div class="col-md-5">
            <label class="mb-1 small font-weight-bold">Machine</label>
            <select name="machine_id" class="form-control form-control-sm">
                <option value="">All Machines</option>
                @foreach($machines as $m)
                    <option value="{{ $m->id }}" {{ request('machine_id') == $m->id ? 'selected' : '' }}>{{ $m->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4">
            <label class="mb-1 small font-weight-bold">Month</label>
            <input type="month" name="month" class="form-control form-control-sm" value="{{ request('month') }}">
        </div>
        <div class="col-md-3">
            <button class="btn btn-primary btn-sm btn-block">Filter</button>
            @if(request()->anyFilled(['machine_id','month']))
                <a href="{{ route('machine-part-usages.index') }}" class="btn btn-secondary btn-sm btn-block mt-1">Clear</a>
            @endif
        </div>
    </div>
</form>
<div class="alert alert-info">
    <strong>Tip:</strong> Add usage when a part is consumed on a machine. Stock balance will be reduced automatically and reflected in movement history.
</div>
<div class="card"><div class="card-body table-responsive p-0">
<table class="table table-striped mb-0">
    <thead><tr><th>Date</th><th>Machine</th><th>Part</th><th>Type</th><th>Quantity</th><th>Reference</th><th>Remarks</th><th>Action</th></tr></thead>
    <tbody>
    @foreach($entries as $entry)
        <tr>
            <td>{{ $entry->date->format('d-M-y') }}</td>
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