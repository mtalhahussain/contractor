@extends('adminlte::page')

@section('title', 'Add Machine')

@section('content_header')
    <h1>Add Machine</h1>
@stop

@section('content')
    <div class="card"><div class="card-body">
        <form action="{{ route('machines.store') }}" method="POST">
            @csrf
            @include('machines.form', ['machine' => null])
        </form>
    </div></div>
@stop
