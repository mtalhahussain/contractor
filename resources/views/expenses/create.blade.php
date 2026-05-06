@extends('adminlte::page')
@section('title', 'Add Expense')
@section('content_header')<h1>Add Expense</h1>@stop
@section('content')<div class="card"><div class="card-body"><form action="{{ route('expenses.store') }}" method="POST">@csrf @include('expenses.form', ['expense' => null])</form></div></div>@stop
