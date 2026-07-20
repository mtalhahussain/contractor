@extends('adminlte::page')

@section('title', 'Edit Site')

@section('content_header')
    <h1>Edit Site</h1>
@stop

@section('content')
    <div class="card"><div class="card-body">
        <form action="{{ route('sites.update', $site) }}" method="POST">
            @csrf
            @method('PUT')
            @include('sites.form', ['site' => $site])
        </form>
    </div></div>
@stop
