@extends('adminlte::page')

@section('title', 'Edit Machine')

@section('content_header')
    <h1>Edit Machine</h1>
@stop

@section('content')
    <div class="card"><div class="card-body">
        <form action="{{ route('machines.update', $machine) }}" method="POST">
            @csrf
            @method('PUT')
            @include('machines.form', ['machine' => $machine])
        </form>
    </div></div>
@stop
