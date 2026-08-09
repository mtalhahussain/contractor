@extends('adminlte::page')

@section('title', 'Machines')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Machines</h1>
        <a href="{{ route('machines.create') }}" class="btn btn-primary">Add Machine</a>
    </div>
@stop

@section('content')
    @if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

    <form method="GET" class="card card-body mb-3 p-3">
        <div class="form-row align-items-end">
            <div class="col-md-4">
                <label class="mb-1 small font-weight-bold">Search</label>
                <input type="text" name="q" class="form-control form-control-sm" value="{{ request('q') }}" placeholder="Machine name or code">
            </div>
            <div class="col-md-3">
                <label class="mb-1 small font-weight-bold">Type</label>
                <select name="type" class="form-control form-control-sm">
                    <option value="">All Types</option>
                    @foreach(['Excavator','Dozer','Roller','Grader','Water Tanker','Hilux','Dumper'] as $t)
                        <option value="{{ $t }}" {{ request('type') === $t ? 'selected' : '' }}>{{ $t }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="mb-1 small font-weight-bold">Status</label>
                <select name="status" class="form-control form-control-sm">
                    <option value="">All</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            <div class="col-md-3">
                <button class="btn btn-primary btn-sm btn-block">Filter</button>
                @if(request()->anyFilled(['q','type','status']))
                    <a href="{{ route('machines.index') }}" class="btn btn-secondary btn-sm btn-block mt-1">Clear</a>
                @endif
            </div>
        </div>
    </form>

    <div class="card">
        <div class="card-body table-responsive p-0">
            <table class="table table-striped mb-0">
                <thead>
                    <tr>
                        <th>Name</th><th>Type</th><th>Owner</th><th>Site</th><th>Code</th><th>Status</th><th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($machines as $machine)
                        <tr>
                            <td>{{ $machine->name }}</td>
                            <td>{{ $machine->type }}</td>
                            <td>{{ $machine->owner_category }}</td>
                            <td>{{ $machine->currentSiteAssignment?->site?->name ?? '-' }}</td>
                            <td>{{ $machine->machine_code }}</td>
                            <td>{{ $machine->status }}</td>
                            <td class="text-nowrap">
                                <a href="{{ route('machines.edit', $machine) }}" class="btn btn-sm btn-info">Edit</a>
                                <form action="{{ route('machines.destroy', $machine) }}" method="POST" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-danger" onclick="return confirm('Delete machine?')">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{ $machines->links() }}
@stop
