@extends('adminlte::page')
@section('title', 'Edit Payment')
@section('content_header')<h1>Edit Payment</h1>@stop
@section('content')<div class="card"><div class="card-body"><form action="{{ route('payments.update', $payment) }}" method="POST">@csrf @method('PUT') @include('payments.form', ['payment' => $payment])</form></div></div>@stop
