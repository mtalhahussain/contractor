@extends('adminlte::page')
@section('title', 'Edit Diesel Usage')
@section('content_header')<h1>Edit Diesel Usage</h1>@stop
@section('content')
<div class="card"><div class="card-body"><form action="{{ route('diesel-usage.update', $entry) }}" method="POST">@csrf @method('PUT') @include('diesel-usage.form', ['entry' => $entry])</form></div></div>
@stop
