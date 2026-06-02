@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <h1 class="mb-0">Dashboard</h1>
@stop

@section('content')
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="row">
        <div class="col-6 col-md-4 col-lg-2 mb-3">
            <a href="{{ route('machine-hours.create') }}" class="btn btn-primary btn-lg btn-block py-4">
                <i class="fas fa-clock d-block mb-2"></i>
                Add Machine Hours
            </a>
        </div>
        <div class="col-6 col-md-4 col-lg-2 mb-3">
            <a href="{{ route('diesel-usage.create') }}" class="btn btn-success btn-lg btn-block py-4">
                <i class="fas fa-oil-can d-block mb-2"></i>
                Add Diesel
            </a>
        </div>
        <div class="col-6 col-md-4 col-lg-2 mb-3">
            <a href="{{ route('payments.create') }}" class="btn btn-info btn-lg btn-block py-4">
                <i class="fas fa-hand-holding-usd d-block mb-2"></i>
                Add Payment
            </a>
        </div>
        <div class="col-6 col-md-4 col-lg-2 mb-3">
            <a href="{{ route('expenses.create') }}" class="btn btn-warning btn-lg btn-block py-4 text-dark">
                <i class="fas fa-file-invoice-dollar d-block mb-2"></i>
                Add Expense
            </a>
        </div>
        <div class="col-6 col-md-4 col-lg-2 mb-3">
            <a href="{{ route('reports.index') }}" class="btn btn-dark btn-lg btn-block py-4">
                <i class="fas fa-chart-line d-block mb-2"></i>
                View Reports
            </a>
        </div>
    </div>

    <div class="row mt-2">
        <div class="col-6 col-md-3">
            <div class="small-box bg-primary">
                <div class="inner">
                    <h4>{{ number_format($todayHours, 2) }}</h4>
                    <p>Today Total Hours</p>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="small-box bg-success">
                <div class="inner">
                    <h4>{{ number_format($todayDiesel, 2) }}</h4>
                    <p>Today Diesel</p>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="small-box bg-info">
                <div class="inner">
                    <h4>{{ number_format($monthlyEarning, 2) }}</h4>
                    <p>Monthly Earning</p>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h4>{{ number_format($monthlyBalance, 2) }}</h4>
                    <p>Monthly Balance</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card card-outline card-secondary">
                <div class="card-body">
                    <strong>Monthly Diesel Cost:</strong> {{ number_format($monthlyDieselCost, 2) }}
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card card-outline card-secondary">
                <div class="card-body">
                    Flow: Login -> Click Button -> Enter Data -> Save
                </div>
            </div>
        </div>
    </div>
@stop
