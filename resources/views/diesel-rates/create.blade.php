@extends('adminlte::page')

@section('title', 'Add Diesel Rate')

@section('content_header')
    <h1>Add Diesel Rate</h1>
@stop

@section('content')
    <div class="card"><div class="card-body">
        <form action="{{ route('diesel-rates.store') }}" method="POST">
            @csrf
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label>Rate Per Liter</label>
                    <input type="number" step="0.01" name="rate_per_liter" class="form-control" required>
                </div>
                <div class="form-group col-md-6">
                    <label>Effective From Date</label>
                    <input type="date" name="effective_from_date" class="form-control" value="{{ now()->toDateString() }}" required>
                </div>
            </div>
            @if ($errors->any())<div class="alert alert-danger">@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>@endif
            <button class="btn btn-primary">Save</button>
            <a href="{{ route('diesel-rates.index') }}" class="btn btn-secondary">Back</a>
        </form>
    </div></div>
@stop
