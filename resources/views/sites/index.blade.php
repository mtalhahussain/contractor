@extends('adminlte::page')

@section('title', 'Sites')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Sites</h1>
        <a href="{{ route('sites.create') }}" class="btn btn-primary">Add Site</a>
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
                        <th>Name</th><th>Code</th><th>Status</th><th>Description</th><th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sites as $site)
                        <tr>
                            <td>{{ $site->name }}</td>
                            <td>{{ $site->code }}</td>
                            <td>{{ $site->status }}</td>
                            <td>{{ $site->description }}</td>
                            <td class="text-nowrap">
                                <a href="{{ route('sites.edit', $site) }}" class="btn btn-sm btn-info">Edit</a>
                                <form action="{{ route('sites.destroy', $site) }}" method="POST" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-danger" onclick="return confirm('Delete site?')">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center py-3">No sites found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{ $sites->links() }}
@stop
