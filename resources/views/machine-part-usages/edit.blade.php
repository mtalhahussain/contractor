@extends('adminlte::page')

@section('title', 'Edit Part Usage')

@section('content_header')
	<h1>Edit Part Usage</h1>
@stop

@section('content')
	<div class="card">
		<div class="card-body">
			<form action="{{ route('machine-part-usages.update', $entry) }}" method="POST">
				@csrf
				@method('PUT')
				@include('machine-part-usages.form', ['entry' => $entry])
			</form>
		</div>
	</div>
@stop