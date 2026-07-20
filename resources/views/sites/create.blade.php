@extends('adminlte::page')

@section('title', 'Add Site')

@section('content_header')
    <h1>Add Site</h1>
@stop

@section('content')
    <div class="card"><div class="card-body">
        <form action="{{ route('sites.store') }}" method="POST">
            @csrf
            @include('sites.form', ['site' => null])
        </form>
    </div></div>
@stop
