@extends('adminlte::page')

@section('title', 'Add Machine Rate')

@section('content_header')
    <h1>Add Machine Rate</h1>
@stop

@section('content')
    <div class="card"><div class="card-body">
        <form action="{{ route('machine-rates.store') }}" method="POST">
            @csrf
            <div class="form-row">
                <div class="form-group col-md-4">
                    <label>Machine</label>
                    <select name="machine_id" class="form-control" required>
                        @foreach($machines as $machine)
                            <option value="{{ $machine->id }}">{{ $machine->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col-md-4">
                    <label>Hourly Rate</label>
                    <input type="number" step="0.01" name="hourly_rate" class="form-control" required>
                </div>
                <div class="form-group col-md-4">
                    <label>Effective From Date</label>
                    <input type="date" name="effective_from_date" class="form-control" value="{{ now()->toDateString() }}" required>
                </div>
            </div>
            @if ($errors->any())<div class="alert alert-danger">@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>@endif
            <button class="btn btn-primary">Save</button>
            <a href="{{ route('machine-rates.index') }}" class="btn btn-secondary">Back</a>
        </form>
    </div></div>
@stop
