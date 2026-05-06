@extends('adminlte::page')
@section('title', 'Payments')
@section('content_header')
<div class="d-flex justify-content-between align-items-center"><h1>Payments (Wasool)</h1><a href="{{ route('payments.create') }}" class="btn btn-primary">Add Payment</a></div>
@stop
@section('content')
@if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
<div class="card"><div class="card-body table-responsive p-0"><table class="table table-striped mb-0"><thead><tr><th>Date</th><th>Machine</th><th>Party</th><th>Amount</th><th>Method</th><th>Action</th></tr></thead><tbody>@foreach($payments as $payment)<tr><td>{{ $payment->date->format('Y-m-d') }}</td><td>{{ $payment->machine?->name }}</td><td>{{ $payment->party_name }}</td><td>{{ number_format($payment->amount_received, 2) }}</td><td>{{ $payment->payment_method }}</td><td><a class="btn btn-sm btn-info" href="{{ route('payments.edit', $payment) }}">Edit</a> <form class="d-inline" action="{{ route('payments.destroy', $payment) }}" method="POST">@csrf @method('DELETE')<button class="btn btn-sm btn-danger" onclick="return confirm('Delete payment?')">Delete</button></form></td></tr>@endforeach</tbody></table></div></div>
{{ $payments->links() }}
@stop
