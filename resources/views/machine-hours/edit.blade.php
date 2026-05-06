@extends('adminlte::page')
@section('title', 'Edit Machine Hours')
@section('content_header')<h1>Edit Machine Hours</h1>@stop
@section('content')
<div class="card"><div class="card-body"><form action="{{ route('machine-hours.update', $entry) }}" method="POST">@csrf @method('PUT') @include('machine-hours.form', ['entry' => $entry])</form></div></div>
@stop
