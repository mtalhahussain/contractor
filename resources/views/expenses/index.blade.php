@extends('adminlte::page')
@section('title', 'Expenses')
@section('content_header')
<div class="d-flex justify-content-between align-items-center"><h1>Expenses</h1><a href="{{ route('expenses.create') }}" class="btn btn-primary">Add Expense</a></div>
@stop
@section('content')
@if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
<div class="card"><div class="card-body table-responsive p-0"><table class="table table-striped mb-0"><thead><tr><th>Date</th><th>Machine</th><th>Type</th><th>Amount</th><th>Action</th></tr></thead><tbody>@foreach($expenses as $expense)<tr><td>{{ $expense->date->format('Y-m-d') }}</td><td>{{ $expense->machine?->name }}</td><td>{{ $expense->expense_type }}</td><td>{{ number_format($expense->amount, 2) }}</td><td><a class="btn btn-sm btn-info" href="{{ route('expenses.edit', $expense) }}">Edit</a> <form class="d-inline" action="{{ route('expenses.destroy', $expense) }}" method="POST">@csrf @method('DELETE')<button class="btn btn-sm btn-danger" onclick="return confirm('Delete expense?')">Delete</button></form></td></tr>@endforeach</tbody></table></div></div>
{{ $expenses->links() }}
@stop
