@extends('adminlte::page')
@section('title', 'Edit Fuel Stock Movement')
@section('content_header')<h1>Edit Fuel Stock Movement</h1>@stop
@section('content')<div class="card"><div class="card-body"><form method="POST" action="{{ route('fuel-stock-movements.update', $movement) }}">@csrf @method('PUT') @include('fuel-stock-movements.form')</form></div></div>@stop