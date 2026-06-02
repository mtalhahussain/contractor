@extends('adminlte::page')
@section('title', 'Add Fuel Stock Movement')
@section('content_header')<h1>Add Fuel Stock Movement</h1>@stop
@section('content')<div class="card"><div class="card-body"><form method="POST" action="{{ route('fuel-stock-movements.store') }}">@csrf @include('fuel-stock-movements.form')</form></div></div>@stop