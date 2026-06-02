@extends('adminlte::page')
@section('title', 'Add Fuel Stock')
@section('content_header')<h1>Add Fuel Stock</h1>@stop
@section('content')<div class="card"><div class="card-body"><form method="POST" action="{{ route('fuel-stocks.store') }}">@csrf @include('fuel-stocks.form')</form></div></div>@stop