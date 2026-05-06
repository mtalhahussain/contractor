@extends('adminlte::page')

@section('title', 'Dashboard')

@section('css')
<style>
    /* ── Solid color overrides for AdminLTE small-box ── */
    .small-box.bg-primary        { background: #2563eb !important; }
    .small-box.bg-success        { background: #16a34a !important; }
    .small-box.bg-info           { background: #0891b2 !important; }
    .small-box.bg-warning        { background: #d97706 !important; }
    .small-box.bg-danger         { background: #dc2626 !important; }

    /* Remove any gradient on small-box inner overlay */
    .small-box > .inner          { background: transparent !important; }
    .small-box:hover             { filter: brightness(0.93); }

    /* ── Solid color overrides for action buttons ── */
    .btn-primary  { background-color: #2563eb !important; border-color: #2563eb !important; }
    .btn-primary:hover  { background-color: #1d4ed8 !important; border-color: #1d4ed8 !important; }

    .btn-success  { background-color: #16a34a !important; border-color: #16a34a !important; }
    .btn-success:hover  { background-color: #15803d !important; border-color: #15803d !important; }

    .btn-info     { background-color: #0891b2 !important; border-color: #0891b2 !important; color:#fff !important; }
    .btn-info:hover     { background-color: #0e7490 !important; border-color: #0e7490 !important; }

    .btn-warning  { background-color: #d97706 !important; border-color: #d97706 !important; color:#fff !important; }
    .btn-warning:hover  { background-color: #b45309 !important; border-color: #b45309 !important; }

    .btn-dark     { background-color: #1e293b !important; border-color: #1e293b !important; }
    .btn-dark:hover     { background-color: #0f172a !important; border-color: #0f172a !important; }
</style>
@stop

@section('content_header')
    <h1 class="mb-0">Simple Dashboard</h1>
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
