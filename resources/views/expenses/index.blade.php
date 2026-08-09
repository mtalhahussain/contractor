@extends('adminlte::page')
@section('title', 'Expenses')
@section('content_header')
<div class="d-flex justify-content-between align-items-center"><h1>Expenses</h1><a href="{{ route('expenses.create') }}" class="btn btn-primary">Add Expense</a></div>
@stop
@section('content')
@if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
<form method="GET" class="card card-body mb-3 p-3">
    <div class="form-row align-items-end">
        <div class="col-md-3">
            <label class="mb-1 small font-weight-bold">Machine</label>
            <select name="machine_id" class="form-control form-control-sm">
                <option value="">All Machines</option>
                @foreach($machines as $m)
                    <option value="{{ $m->id }}" {{ request('machine_id') == $m->id ? 'selected' : '' }}>{{ $m->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="mb-1 small font-weight-bold">Type</label>
            <select name="expense_type" class="form-control form-control-sm">
                <option value="">All Types</option>
                @foreach(['Fuel','Repair & Maintenance','Spare Parts','Tyre & Tube','Labour','Transport','Miscellaneous'] as $t)
                    <option value="{{ $t }}" {{ request('expense_type') === $t ? 'selected' : '' }}>{{ $t }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="mb-1 small font-weight-bold">Month</label>
            <input type="month" name="month" class="form-control form-control-sm" value="{{ request('month') }}">
        </div>
        <div class="col-md-3">
            <button class="btn btn-primary btn-sm btn-block">Filter</button>
            @if(request()->anyFilled(['machine_id','expense_type','month']))
                <a href="{{ route('expenses.index') }}" class="btn btn-secondary btn-sm btn-block mt-1">Clear</a>
            @endif
        </div>
    </div>
</form>
<div class="card"><div class="card-body table-responsive p-0"><table class="table table-striped mb-0"><thead><tr><th>Date</th><th>Machine</th><th>Type</th><th>Amount</th><th>Action</th></tr></thead><tbody>@foreach($expenses as $expense)<tr><td>{{ $expense->date->format('d-M-y') }}</td><td>{{ $expense->machine?->name }}</td><td>{{ $expense->expense_type }}</td><td>{{ number_format($expense->amount, 2) }}</td><td><a class="btn btn-sm btn-info" href="{{ route('expenses.edit', $expense) }}">Edit</a> <form class="d-inline" action="{{ route('expenses.destroy', $expense) }}" method="POST">@csrf @method('DELETE')<button class="btn btn-sm btn-danger" onclick="return confirm('Delete expense?')">Delete</button></form></td></tr>@endforeach</tbody></table></div></div>
{{ $expenses->links() }}
@stop
