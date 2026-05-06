@extends('adminlte::page')
@section('title', 'Edit Expense')
@section('content_header')<h1>Edit Expense</h1>@stop
@section('content')<div class="card"><div class="card-body"><form action="{{ route('expenses.update', $expense) }}" method="POST">@csrf @method('PUT') @include('expenses.form', ['expense' => $expense])</form></div></div>@stop
