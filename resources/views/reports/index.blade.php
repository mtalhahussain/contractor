@extends('adminlte::page')
@section('title', 'Reports')
@section('content_header')<h1>Reports</h1>@stop
@section('css')
<style>
    .report-btn {
        padding-top: 1rem;
        padding-bottom: 1rem;
        font-size: 1.25rem;
        font-weight: 600;
        border-width: 2px;
    }
</style>
@stop
@section('content')
<div class="row">
    <div class="col-md-4 mb-3"><a href="{{ route('reports.machine-hours') }}" class="btn btn-outline-primary btn-lg btn-block report-btn">Machine Hours</a></div>
    <div class="col-md-4 mb-3"><a href="{{ route('reports.diesel-usage') }}" class="btn btn-outline-success btn-lg btn-block report-btn">Diesel Usage</a></div>
    <div class="col-md-4 mb-3"><a href="{{ route('reports.complete-hisab') }}" class="btn btn-outline-dark btn-lg btn-block report-btn">Complete Hisab</a></div>
    <div class="col-md-4 mb-3"><a href="{{ route('reports.machine-ledger') }}" class="btn btn-outline-info btn-lg btn-block report-btn">Machine Ledger</a></div>
    <div class="col-md-4 mb-3"><a href="{{ route('reports.daily-summary') }}" class="btn btn-outline-warning btn-lg btn-block report-btn">Daily Summary</a></div>
    <div class="col-md-4 mb-3"><a href="{{ route('reports.monthly-summary') }}" class="btn btn-outline-secondary btn-lg btn-block report-btn">Monthly Summary</a></div>
    <div class="col-md-4 mb-3"><a href="{{ route('reports.inventory-stock') }}" class="btn btn-outline-danger btn-lg btn-block report-btn">Inventory Stock</a></div>
    <div class="col-md-4 mb-3"><a href="{{ route('reports.part-usage') }}" class="btn btn-outline-primary btn-lg btn-block report-btn">Part Usage History</a></div>
    <div class="col-md-4 mb-3"><a href="{{ route('reports.machine-parts') }}" class="btn btn-outline-success btn-lg btn-block report-btn">Machine-wise Part Consumption</a></div>
    <div class="col-md-4 mb-3"><a href="{{ route('reports.fuel-stock') }}" class="btn btn-outline-info btn-lg btn-block report-btn">Fuel Stock</a></div>
    <div class="col-md-4 mb-3"><a href="{{ route('reports.fuel-issues') }}" class="btn btn-outline-warning btn-lg btn-block report-btn">Fuel Issue History</a></div>
    <div class="col-md-4 mb-3"><a href="{{ route('reports.fuel-consumption') }}" class="btn btn-outline-secondary btn-lg btn-block report-btn">Consumer-wise Fuel Consumption</a></div>
</div>
@stop
