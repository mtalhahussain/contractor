@extends('adminlte::page')

@section('title', 'Edit Stock Movement')

@section('content_header')
	<h1>Edit Stock Movement</h1>
@stop

@section('content')
	<div class="card">
		<div class="card-body">
			<form action="{{ route('part-stock-movements.update', $movement) }}" method="POST">
				@csrf
				@method('PUT')
				@include('part-stock-movements.form', ['movement' => $movement])
			</form>
		</div>
	</div>
@stop