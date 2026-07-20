@extends('adminlte::page')

@section('title', 'Salary Summary Report')

@section('content_header')
    <div class="row">
        <div class="col-md-6">
            <h1>Salary Summary Report</h1>
        </div>
        <div class="col-md-6 text-right">
            <a href="{{ route('salary-reports.index') }}" class="btn btn-sm btn-info">
                <i class="fas fa-user"></i> Individual Report
            </a>
            <a href="{{ route('employees.index') }}" class="btn btn-sm btn-secondary">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">All Employees - Salary Summary</h3>
                </div>
                <div class="card-body">
                    <form method="GET" id="filterForm">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="month_year">Select Month <span class="text-danger">*</span></label>
                                    <input type="month" class="form-control" id="month_year" name="month_year"
                                        value="{{ $monthYear }}" onchange="document.getElementById('filterForm').submit()">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="employee_filter">Filter by Employee</label>
                                    <select class="form-control" id="employee_filter">
                                        <option value="">-- All Employees --</option>
                                        @foreach($reportData as $data)
                                            <option value="{{ $data['employee_code'] }}">
                                                {{ $data['employee_name'] }} ({{ $data['employee_code'] }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="row mt-3">
        <div class="col-sm-4">
            <div class="info-box">
                <span class="info-box-icon bg-success"><i class="fas fa-money-bill-wave"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total Salary</span>
                    <span class="info-box-number">
                        PKR {{ number_format($totalSalary, 2) }}
                    </span>
                </div>
            </div>
        </div>

        <div class="col-sm-4">
            <div class="info-box">
                <span class="info-box-icon bg-warning"><i class="fas fa-minus-circle"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total Advances</span>
                    <span class="info-box-number">
                        PKR {{ number_format($totalAdvances, 2) }}
                    </span>
                </div>
            </div>
        </div>

        <div class="col-sm-4">
            <div class="info-box">
                <span class="info-box-icon bg-info"><i class="fas fa-balance-scale"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Net Payable</span>
                    <span class="info-box-number">
                        PKR {{ number_format($totalNetPayable, 2) }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    {{-- Detailed Report Table --}}
    <div class="row mt-3">
        <div class="col-md-12">
            <div class="card card-info">
                <div class="card-header with-border">
                    <h3 class="card-title">
                        <i class="fas fa-list"></i> Salary Details - {{ $month_year }}
                    </h3>
                </div>
                <div class="card-body">
                    @if($reportData->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-hover" id="salaryTable">
                                <thead class="thead-dark">
                                    <tr>
                                        <th style="width: 8%">Code</th>
                                        <th style="width: 15%">Employee Name</th>
                                        <th style="width: 12%">Designation</th>
                                        <th style="width: 12%">Salary</th>
                                        <th style="width: 12%">Advances</th>
                                        <th style="width: 12%">Leave Ded.</th>
                                        <th style="width: 12%">Net Payable</th>
                                        <th style="width: 7%">Count</th>
                                        <th style="width: 10%">View</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($reportData as $data)
                                        <tr>
                                            <td>
                                                <span class="badge badge-primary">{{ $data['employee_code'] }}</span>
                                            </td>
                                            <td><strong>{{ $data['employee_name'] }}</strong></td>
                                            <td>{{ $data['designation'] }}</td>
                                            <td>
                                                <strong>PKR {{ number_format($data['salary_amount'], 2) }}</strong>
                                            </td>
                                            <td>
                                                <span class="badge badge-warning">
                                                    PKR {{ number_format($data['total_advances'], 2) }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge badge-danger">
                                                    PKR {{ number_format($data['leave_deduction'] ?? 0, 2) }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge badge-success">
                                                    PKR {{ number_format($data['net_payable'], 2) }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge badge-secondary">{{ $data['advance_count'] }}</span>
                                            </td>
                                            <td>
                                                <a href="{{ route('salary-reports.index', ['employee_id' => null]) }}#" class="btn btn-xs btn-info" title="View Details">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                    <tr class="table-primary" style="font-weight: bold;">
                                        <td colspan="3">TOTAL</td>
                                        <td>PKR {{ number_format($totalSalary, 2) }}</td>
                                        <td>PKR {{ number_format($totalAdvances, 2) }}</td>
                                        <td>PKR {{ number_format($reportData->sum('leave_deduction'), 2) }}</td>
                                        <td>PKR {{ number_format($totalNetPayable, 2) }}</td>
                                        <td colspan="2"></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            No salary data found for selected month.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.25/css/dataTables.bootstrap4.min.css">
    <style>
        .info-box {
            margin-bottom: 15px;
        }
    </style>
@stop

@section('js')
    <script src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.25/js/dataTables.bootstrap4.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#salaryTable').DataTable({
                "paging": false,
                "searching": true,
                "ordering": true,
            });

            // Employee filter functionality
            $('#employee_filter').on('change', function() {
                let filter = $(this).val().toLowerCase();
                let totalSalary = 0;
                let totalAdvances = 0;
                let totalNetPayable = 0;

                $('#salaryTable tbody tr').each(function() {
                    let code = $(this).find('td:first').text().toLowerCase();
                    
                    if (filter === '' || code.includes(filter)) {
                        $(this).show();
                        if (!$(this).hasClass('table-primary')) {
                            totalSalary += parseFloat($(this).find('td:eq(3)').text().replace(/[^\d.-]/g, '')) || 0;
                            totalAdvances += parseFloat($(this).find('td:eq(4)').text().replace(/[^\d.-]/g, '')) || 0;
                            totalNetPayable += parseFloat($(this).find('td:eq(5)').text().replace(/[^\d.-]/g, '')) || 0;
                        }
                    } else {
                        $(this).hide();
                    }
                });

                // Update totals row
                let totalsRow = $('#salaryTable tbody tr.table-primary');
                if (filter === '') {
                    totalsRow.show();
                } else {
                    totalsRow.find('td:eq(3)').text('PKR ' + totalSalary.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ','));
                    totalsRow.find('td:eq(4)').text('PKR ' + totalAdvances.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ','));
                    totalsRow.find('td:eq(5)').text('PKR ' + totalNetPayable.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ','));
                    totalsRow.show();
                }
            });
        });
    </script>
@stop
