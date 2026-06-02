@extends('adminlte::page')

@section('title', 'Add Spare Part')

@section('content_header')
    <h1>Add Spare Part</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <form action="{{ route('spare-parts.store') }}" method="POST">
                @csrf
                @include('spare-parts.form', ['part' => null])
            </form>
        </div>
    </div>
@stop