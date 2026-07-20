@extends('adminlte::page')

@section('title', 'Salary Reports')

@section('content_header')
    <div class="row">
        <div class="col-md-6">
            <h1>Salary Reports</h1>
        </div>
        <div class="col-md-6 text-right">
            <a href="{{ route('salary-reports.summary') }}" class="btn btn-sm btn-info">
                <i class="fas fa-users"></i> Summary Report
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
                    <h3 class="card-title">Employee Salary Report</h3>
                </div>
                <div class="card-body">
                    <form method="GET" class="form-inline">
                        <div class="form-group mr-3">
                            <label for="employee_id" class="mr-2">Employee <span class="text-danger">*</span></label>
                            <select class="form-control" id="employee_id" name="employee_id" required onchange="this.form.submit()">
                                <option value="">-- Select Employee --</option>
                                @foreach($employees as $emp)
                                    <option value="{{ $emp->id }}" {{ $selectedEmployee?->id == $emp->id ? 'selected' : '' }}>
                                        {{ $emp->employee_code }} - {{ $emp->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        @if($selectedEmployee)
                            <div class="form-group mr-3">
                                <label for="month_year" class="mr-2">Month</label>
                                <input type="month" class="form-control" id="month_year" name="month_year"
                                    value="{{ $monthYear }}" onchange="this.form.submit()">
                            </div>
                        @endif
                    </form>
                </div>
            </div>
        </div>
    </div>

    @if($reportData)
        {{-- Employee Header --}}
        <div class="row mt-3">
            <div class="col-md-12">
                <div class="card card-success">
                    <div class="card-header">
                        <h3 class="card-title">
                            {{ $reportData['employee']->name }} ({{ $reportData['employee']->employee_code }})
                            - {{ $reportData['month_year'] }}
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <strong>Designation:</strong> {{ $reportData['employee']->designation ?? 'N/A' }}
                            </div>
                            <div class="col-md-3">
                                <strong>Joining Date:</strong> {{ $reportData['employee']->joining_date?->format('d-M-Y') ?? 'N/A' }}
                            </div>
                            <div class="col-md-3">
                                <strong>Status:</strong>
                                <span class="badge badge-success">{{ ucfirst($reportData['employee']->status) }}</span>
                            </div>
                            <div class="col-md-3">
                                <strong>Bank:</strong> {{ $reportData['employee']->bank_name ?? 'N/A' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Leave Deduction Info --}}
        <div class="row mt-3">
            <div class="col-md-6">
                <div class="card card-danger">
                    <div class="card-header with-border">
                        <h3 class="card-title">
                            <i class="fas fa-calendar-times"></i> Leave Deduction
                        </h3>
                    </div>
                    <div class="card-body">
                        <table class="table table-sm">
                            <tr>
                                <td><strong><i class="fas fa-calendar-days"></i> Leave Days:</strong></td>
                                <td class="text-right"><span class="badge badge-info">{{ $reportData['leave_days'] }}</span></td>
                            </tr>
                            <tr>
                                <td><strong><i class="fas fa-minus-circle"></i> Deduction Amount:</strong></td>
                                <td class="text-right"><span class="badge badge-danger">PKR {{ number_format($reportData['leave_deduction'], 2) }}</span></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card card-warning">
                    <div class="card-header with-border">
                        <h3 class="card-title">
                            <i class="fas fa-info-circle"></i> Salary Breakdown
                        </h3>
                    </div>
                    <div class="card-body">
                        <table class="table table-sm">
                            <tr>
                                <td><strong>Basic Salary:</strong></td>
                                <td class="text-right">PKR {{ number_format($reportData['salary_amount'], 2) }}</td>
                            </tr>
                            <tr>
                                <td><strong>Approved Advances:</strong></td>
                                <td class="text-right">-PKR {{ number_format($reportData['total_approved_advances'], 2) }}</td>
                            </tr>
                            <tr>
                                <td><strong>Leave Deduction:</strong></td>
                                <td class="text-right">-PKR {{ number_format($reportData['leave_deduction'], 2) }}</td>
                            </tr>
                            <tr class="table-primary">
                                <td><strong>Net Payable:</strong></td>
                                <td class="text-right"><strong>PKR {{ number_format($reportData['remaining_balance'], 2) }}</strong></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Advances Details --}}
        <div class="row mt-3">
            <div class="col-md-12">
                <div class="card card-info">
                    <div class="card-header with-border">
                        <h3 class="card-title">
                            <i class="fas fa-list"></i> Advances Breakdown - {{ $reportData['month_year'] }}
                        </h3>
                    </div>
                    <div class="card-body">
                        <ul class="nav nav-tabs" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="approved-tab" data-toggle="tab" href="#approved" role="tab">
                                    Approved ({{ $reportData['approved_advances']->count() }})
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="pending-tab" data-toggle="tab" href="#pending" role="tab">
                                    Pending ({{ $reportData['pending_advances']->count() }})
                                </a>
                            </li>
                            @if($reportData['rejected_advances']->count() > 0)
                                <li class="nav-item">
                                    <a class="nav-link" id="rejected-tab" data-toggle="tab" href="#rejected" role="tab">
                                        Rejected ({{ $reportData['rejected_advances']->count() }})
                                    </a>
                                </li>
                            @endif
                        </ul>

                        <div class="tab-content">
                            {{-- Approved Advances --}}
                            <div class="tab-pane fade show active" id="approved" role="tabpanel">
                                <div class="table-responsive mt-3">
                                    @if($reportData['approved_advances']->count() > 0)
                                        <table class="table table-bordered table-striped">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th style="width: 20%">Date</th>
                                                    <th style="width: 25%">Amount</th>
                                                    <th style="width: 35%">Remarks</th>
                                                    <th style="width: 20%">Approved On</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($reportData['approved_advances'] as $advance)
                                                    <tr>
                                                        <td>{{ $advance->advance_date->format('d-M-Y') }}</td>
                                                        <td>
                                                            <strong>PKR {{ number_format($advance->advance_amount, 2) }}</strong>
                                                        </td>
                                                        <td>{{ $advance->remarks ?? '-' }}</td>
                                                        <td>{{ $advance->approved_at?->format('d-M-Y H:i') ?? '-' }}</td>
                                                    </tr>
                                                @endforeach
                                                <tr class="table-info">
                                                    <td colspan="1"><strong>Total Approved</strong></td>
                                                    <td>
                                                        <strong>PKR {{ number_format($reportData['total_approved_advances'], 2) }}</strong>
                                                    </td>
                                                    <td colspan="2"></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    @else
                                        <p class="text-muted mt-3">No approved advances for this month.</p>
                                    @endif
                                </div>
                            </div>

                            {{-- Pending Advances --}}
                            <div class="tab-pane fade" id="pending" role="tabpanel">
                                <div class="table-responsive mt-3">
                                    @if($reportData['pending_advances']->count() > 0)
                                        <table class="table table-bordered table-striped">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th style="width: 20%">Date</th>
                                                    <th style="width: 25%">Amount</th>
                                                    <th style="width: 35%">Remarks</th>
                                                    <th style="width: 20%">Requested On</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($reportData['pending_advances'] as $advance)
                                                    <tr>
                                                        <td>{{ $advance->advance_date->format('d-M-Y') }}</td>
                                                        <td>
                                                            <span class="badge badge-warning">
                                                                PKR {{ number_format($advance->advance_amount, 2) }}
                                                            </span>
                                                        </td>
                                                        <td>{{ $advance->remarks ?? '-' }}</td>
                                                        <td>{{ $advance->created_at->format('d-M-Y H:i') }}</td>
                                                    </tr>
                                                @endforeach
                                                <tr class="table-warning">
                                                    <td colspan="1"><strong>Total Pending</strong></td>
                                                    <td>
                                                        <strong>PKR {{ number_format($reportData['total_pending_advances'], 2) }}</strong>
                                                    </td>
                                                    <td colspan="2"></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    @else
                                        <p class="text-muted mt-3">No pending advances for this month.</p>
                                    @endif
                                </div>
                            </div>

                            {{-- Rejected Advances --}}
                            @if($reportData['rejected_advances']->count() > 0)
                                <div class="tab-pane fade" id="rejected" role="tabpanel">
                                    <div class="table-responsive mt-3">
                                        <table class="table table-bordered table-striped">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th style="width: 20%">Date</th>
                                                    <th style="width: 25%">Amount</th>
                                                    <th style="width: 35%">Remarks</th>
                                                    <th style="width: 20%">Requested On</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($reportData['rejected_advances'] as $advance)
                                                    <tr>
                                                        <td>{{ $advance->advance_date->format('d-M-Y') }}</td>
                                                        <td>
                                                            <span class="badge badge-danger">
                                                                PKR {{ number_format($advance->advance_amount, 2) }}
                                                            </span>
                                                        </td>
                                                        <td>{{ $advance->remarks ?? '-' }}</td>
                                                        <td>{{ $advance->created_at->format('d-M-Y H:i') }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Monthly History --}}
        <div class="row mt-3">
            <div class="col-md-12">
                <div class="card card-warning">
                    <div class="card-header with-border">
                        <h3 class="card-title">
                            <i class="fas fa-history"></i> Monthly History (Last 12 Months)
                        </h3>
                    </div>
                    <div class="card-body">
                        @if($reportData['months_history']->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped table-hover">
                                    <thead class="thead-dark">
                                        <tr>
                                            <th style="width: 15%">Month</th>
                                            <th style="width: 15%">Salary</th>
                                            <th style="width: 15%">Advances</th>
                                            <th style="width: 15%">Leave Ded.</th>
                                            <th style="width: 15%">Net Payable</th>
                                            <th style="width: 10%">Count</th>
                                            <th style="width: 15%">Details</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($reportData['months_history'] as $history)
                                            <tr>
                                                <td><strong>{{ $history['month'] }}</strong></td>
                                                <td>PKR {{ number_format($history['salary_amount'], 2) }}</td>
                                                <td>
                                                    <span class="badge badge-warning">
                                                        PKR {{ number_format($history['total_advances'], 2) }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge badge-danger">
                                                        PKR {{ number_format($history['leave_deduction'] ?? 0, 2) }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge badge-success">
                                                        PKR {{ number_format($history['net_payable'], 2) }}
                                                    </span>
                                                </td>
                                                <td>{{ $history['advance_count'] }}</td>
                                                <td>
                                                    <a href="{{ route('salary-reports.index', ['employee_id' => $reportData['employee']->id, 'month_year' => $history['year'] . '-' . str_pad($history['month_num'], 2, '0', STR_PAD_LEFT)]) }}"
                                                        class="btn btn-xs btn-info">
                                                        <i class="fas fa-eye"></i> View
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p class="text-muted">No history available.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @elseif(request('employee_id'))
        <div class="alert alert-info mt-3">
            <i class="fas fa-info-circle"></i>
            No salary data found for selected month.
        </div>
    @endif
@stop

@section('css')
    <style>
        .info-box {
            margin-bottom: 15px;
        }
    </style>
@stop
