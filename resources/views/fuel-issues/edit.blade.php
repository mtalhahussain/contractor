@extends('adminlte::page')
@section('title', 'Edit Fuel Issue')
@section('content_header')<h1>Edit Fuel Issue</h1>@stop
@section('content')<div class="card"><div class="card-body"><form method="POST" action="{{ route('fuel-issues.update', $issue) }}">@csrf @method('PUT') @include('fuel-issues.form')</form></div></div>@stop