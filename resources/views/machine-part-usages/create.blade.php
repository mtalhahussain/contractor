@extends('adminlte::page')

@section('title', 'Add Part Usage')

@section('content_header')
	<h1>Add Part Usage</h1>
@stop

@section('content')
	<div class="card">
		<div class="card-body">
			<form action="{{ route('machine-part-usages.store') }}" method="POST">
				@csrf
				@include('machine-part-usages.form', ['entry' => null])
			</form>
		</div>
	</div>
@stop