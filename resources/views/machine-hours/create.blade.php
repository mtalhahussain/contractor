@extends('adminlte::page')
@section('title', 'Add Machine Hours')
@section('content_header')<h1>Add Machine Hours</h1>@stop
@section('content')
<div class="card"><div class="card-body"><form action="{{ route('machine-hours.store') }}" method="POST">@csrf @include('machine-hours.form', ['entry' => null])</form></div></div>
@stop
