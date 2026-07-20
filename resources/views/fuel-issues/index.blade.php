@extends('adminlte::page')
@section('title', 'Fuel Issues')
@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Fuel Issues</h1>
        <a href="{{ route('fuel-issues.create') }}" class="btn btn-primary">Add Fuel Issue</a>
    </div>
@stop
@section('content')
    @if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if (session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
    <div class="alert alert-info">
        <strong>Tip:</strong> Record each fuel issue here (machine/generator/vehicle). This entry will automatically reduce stock from selected tank/store.
    </div>
    <div class="card"><div class="card-body table-responsive p-0"><table class="table table-striped mb-0"><thead><tr><th>Date</th><th>Tank / Store</th><th>Consumer</th><th>Quantity</th><th>Reference</th><th>Action</th></tr></thead><tbody>@forelse($issues as $issue)<tr><td>{{ $issue->date?->format('Y-m-d') }}</td><td>{{ $issue->fuelStock?->name }}</td><td>{{ $issue->consumer_type === 'machine' ? ($issue->machine?->name ?? '-') : ($issue->consumer_name ?? '-') }} <small class="text-muted d-block">{{ ucfirst($issue->consumer_type) }}</small></td><td>{{ number_format((float) $issue->quantity, 2) }}</td><td>{{ $issue->reference }}</td><td class="text-nowrap"><a href="{{ route('fuel-issues.edit', $issue) }}" class="btn btn-sm btn-info">Edit</a><form action="{{ route('fuel-issues.destroy', $issue) }}" method="POST" class="d-inline">@csrf @method('DELETE')<button class="btn btn-sm btn-danger" onclick="return confirm('Delete fuel issue?')">Delete</button></form></td></tr>@empty<tr><td colspan="6" class="text-center py-3">No fuel issue found.</td></tr>@endforelse</tbody></table></div></div>
    {{ $issues->links() }}
@stop