@extends('adminlte::page')

@section('title', 'Edit Spare Part')

@section('content_header')
    <h1>Edit Spare Part</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <form action="{{ route('spare-parts.update', $part) }}" method="POST">
                @csrf
                @method('PUT')
                @include('spare-parts.form', ['part' => $part])
            </form>
        </div>
    </div>
@stop