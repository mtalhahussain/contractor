@extends('adminlte::page')
@section('title', 'Edit Fuel Stock')
@section('content_header')<h1>Edit Fuel Stock</h1>@stop
@section('content')<div class="card"><div class="card-body"><form method="POST" action="{{ route('fuel-stocks.update', $stock) }}">@csrf @method('PUT') @include('fuel-stocks.form')</form></div></div>@stop