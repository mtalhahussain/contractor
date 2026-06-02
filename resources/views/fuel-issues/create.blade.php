@extends('adminlte::page')
@section('title', 'Add Fuel Issue')
@section('content_header')<h1>Add Fuel Issue</h1>@stop
@section('content')<div class="card"><div class="card-body"><form method="POST" action="{{ route('fuel-issues.store') }}">@csrf @include('fuel-issues.form')</form></div></div>@stop