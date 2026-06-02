@extends('adminlte::page')

@section('title', 'Add Stock Movement')

@section('content_header')
	<h1>Add Stock Movement</h1>
@stop

@section('content')
	<div class="card">
		<div class="card-body">
			<form action="{{ route('part-stock-movements.store') }}" method="POST">
				@csrf
				@include('part-stock-movements.form', ['movement' => null, 'selectedPartId' => $selectedPartId ?? null])
			</form>
		</div>
	</div>
@stop