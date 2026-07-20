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
