@extends('adminlte::page')
@section('title', 'Add Payment')
@section('content_header')<h1>Add Payment</h1>@stop
@section('content')<div class="card"><div class="card-body"><form action="{{ route('payments.store') }}" method="POST">@csrf @include('payments.form', ['payment' => null])</form></div></div>@stop
