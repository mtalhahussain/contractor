@extends('adminlte::page')
@section('title', 'Add Diesel Usage')
@section('content_header')<h1>Add Diesel Usage</h1>@stop
@section('content')
<div class="card"><div class="card-body"><form action="{{ route('diesel-usage.store') }}" method="POST">@csrf @include('diesel-usage.form', ['entry' => null])</form></div></div>
@stop
