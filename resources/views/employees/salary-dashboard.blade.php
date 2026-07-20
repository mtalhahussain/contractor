@extends('adminlte::page')

@section('title', 'Salary Management - ' . $employee->name)

@section('content_header')
    <div class="row">
        <div class="col-md-6">
            <h1>
                <i class="fas fa-money-bill-wave"></i> Salary Management
                <small>{{ $employee->name }}</small>
            </h1>
        </div>
        <div class="col-md-6 text-right">
            <a href="{{ route('employees.index') }}" class="btn btn-sm btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Employees
            </a>
            <a href="{{ route('employees.edit', $employee) }}" class="btn btn-sm btn-primary">
                <i class="fas fa-edit"></i> Edit Employee
            </a>
        </div>
    </div>
@stop

@section('content')
    <div class="container-fluid">
        {{-- Employee Info Card --}}
        <div class="row mb-3">
            <div class="col-md-12">
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title">Employee Information</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <strong>Employee Code:</strong> {{ $employee->employee_code }}
                            </div>
                            <div class="col-md-3">
                                <strong>Designation:</strong> {{ $employee->designation ?? 'N/A' }}
                            </div>
                            <div class="col-md-3">
                                <strong>Status:</strong>
                                <span class="badge badge-{{ $employee->status === 'active' ? 'success' : 'danger' }}">
                                    {{ ucfirst($employee->status) }}
                                </span>
                            </div>
                            <div class="col-md-3">
                                <strong>Joining Date:</strong> {{ $employee->joining_date?->format('d-M-Y') ?? 'N/A' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            {{-- Add New Salary Card --}}
            <div class="col-md-6">
                <div class="card card-primary">
                    <div class="card-header with-border">
                        <h3 class="card-title">
                            <i class="fas fa-plus-circle"></i> Add New Salary Record
                        </h3>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('salary-histories.store', $employee) }}" method="POST">
                            @csrf
                            <div class="form-group">
                                <label for="effective_from">Effective From Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control @error('effective_from') is-invalid @enderror"
                                    id="effective_from" name="effective_from" required value="{{ old('effective_from') }}">
                                @error('effective_from')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                                <small class="form-text text-muted">
                                    Use first day of the month (e.g., 2026-07-01)
                                </small>
                            </div>

                            <div class="form-group">
                                <label for="salary_amount">Salary Amount (PKR) <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" class="form-control @error('salary_amount') is-invalid @enderror"
                                    id="salary_amount" name="salary_amount" required value="{{ old('salary_amount') }}" min="0">
                                @error('salary_amount')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="salary_type">Salary Type <span class="text-danger">*</span></label>
                                <select class="form-control @error('salary_type') is-invalid @enderror"
                                    id="salary_type" name="salary_type" required>
                                    <option value="">-- Select Type --</option>
                                    <option value="monthly" {{ old('salary_type') === 'monthly' ? 'selected' : '' }}>Monthly</option>
                                    <option value="hourly" {{ old('salary_type') === 'hourly' ? 'selected' : '' }}>Hourly</option>
                                </select>
                                @error('salary_type')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="notes">Notes</label>
                                <textarea class="form-control @error('notes') is-invalid @enderror"
                                    id="notes" name="notes" rows="3">{{ old('notes') }}</textarea>
                                @error('notes')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <button type="submit" class="btn btn-primary btn-block">
                                    <i class="fas fa-save"></i> Add Salary Record
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Add Salary Advance Card --}}
            <div class="col-md-6">
                <div class="card card-success">
                    <div class="card-header with-border">
                        <h3 class="card-title">
                            <i class="fas fa-hand-holding-usd"></i> Record Salary Advance
                        </h3>
                    </div>
                    <div class="card-body">
                        @if($employee->salaryHistories->count() > 0)
                            <form action="{{ route('salary-advances.store', $employee) }}" method="POST">
                                @csrf
                                <div class="form-group">
                                    <label for="salary_history_id">Select Salary Record <span class="text-danger">*</span></label>
                                    <select class="form-control @error('salary_history_id') is-invalid @enderror"
                                        id="salary_history_id" name="salary_history_id" required>
                                        <option value="">-- Select Salary --</option>
                                        @foreach($employee->salaryHistories as $salary)
                                            <option value="{{ $salary->id }}"
                                                {{ old('salary_history_id') == $salary->id ? 'selected' : '' }}>
                                                {{ $salary->effective_from->format('F Y') }} - PKR {{ number_format($salary->salary_amount, 2) }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('salary_history_id')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="advance_date">Advance Date <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control @error('advance_date') is-invalid @enderror"
                                        id="advance_date" name="advance_date" required value="{{ old('advance_date', now()->format('Y-m-d')) }}">
                                    @error('advance_date')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="advance_amount">Advance Amount (PKR) <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" class="form-control @error('advance_amount') is-invalid @enderror"
                                        id="advance_amount" name="advance_amount" required value="{{ old('advance_amount') }}" min="0.01">
                                    @error('advance_amount')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="remarks">Remarks</label>
                                    <textarea class="form-control @error('remarks') is-invalid @enderror"
                                        id="remarks" name="remarks" rows="3">{{ old('remarks') }}</textarea>
                                    @error('remarks')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <button type="submit" class="btn btn-success btn-block">
                                        <i class="fas fa-plus-circle"></i> Record Advance
                                    </button>
                                </div>
                            </form>
                        @else
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i>
                                Please add a salary record first before recording advances.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Add Employee Leave Card --}}
        <div class="row mt-3">
            <div class="col-md-6">
                <div class="card card-warning">
                    <div class="card-header with-border">
                        <h3 class="card-title">
                            <i class="fas fa-calendar-times"></i> Record Leave
                        </h3>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('employee-leaves.store', $employee) }}" method="POST">
                            @csrf
                            <div class="form-group">
                                <label for="leave_date">Leave Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control @error('leave_date') is-invalid @enderror"
                                    id="leave_date" name="leave_date" required value="{{ old('leave_date') }}">
                                @error('leave_date')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="reason">Reason</label>
                                <select class="form-control @error('reason') is-invalid @enderror"
                                    id="reason" name="reason">
                                    <option value="">-- Select Reason --</option>
                                    <option value="Sick Leave" {{ old('reason') === 'Sick Leave' ? 'selected' : '' }}>Sick Leave</option>
                                    <option value="Casual Leave" {{ old('reason') === 'Casual Leave' ? 'selected' : '' }}>Casual Leave</option>
                                    <option value="Emergency" {{ old('reason') === 'Emergency' ? 'selected' : '' }}>Emergency</option>
                                    <option value="Personal" {{ old('reason') === 'Personal' ? 'selected' : '' }}>Personal</option>
                                    <option value="Other" {{ old('reason') === 'Other' ? 'selected' : '' }}>Other</option>
                                </select>
                                @error('reason')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="notes">Notes</label>
                                <textarea class="form-control @error('notes') is-invalid @enderror"
                                    id="notes" name="notes" rows="2">{{ old('notes') }}</textarea>
                                @error('notes')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <button type="submit" class="btn btn-warning btn-block">
                                    <i class="fas fa-plus-circle"></i> Record Leave
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Leave History --}}
            <div class="col-md-6">
                <div class="card card-danger">
                    <div class="card-header with-border">
                        <h3 class="card-title">
                            <i class="fas fa-calendar"></i> Leave History
                        </h3>
                    </div>
                    <div class="card-body">
                        @if($employee->leaves->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped table-sm">
                                    <thead class="thead-light">
                                        <tr>
                                            <th style="width: 30%">Date</th>
                                            <th style="width: 35%">Reason</th>
                                            <th style="width: 25%">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($employee->leaves->take(10) as $leave)
                                            <tr>
                                                <td>{{ $leave->leave_date->format('d-M-Y') }}</td>
                                                <td>{{ $leave->reason ?? '-' }}</td>
                                                <td>
                                                    <form action="{{ route('employee-leaves.destroy', [$employee, $leave]) }}"
                                                        method="POST" style="display:inline;" onsubmit="return confirm('Delete this leave record?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-xs btn-danger">
                                                            <i class="fas fa-trash"></i> Delete
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i>
                                No leave records found.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Salary History Table --}}
        <div class="row mt-3">
            <div class="col-md-12">
                <div class="card card-info">
                    <div class="card-header with-border">
                        <h3 class="card-title">
                            <i class="fas fa-history"></i> Complete Salary History
                        </h3>
                    </div>
                    <div class="card-body">
                        @if($employee->salaryHistories->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped table-hover">
                                    <thead class="thead-dark">
                                        <tr>
                                            <th style="width: 15%">Period</th>
                                            <th style="width: 12%">Effective From</th>
                                            <th style="width: 12%">Salary Amount</th>
                                            <th style="width: 10%">Type</th>
                                            <th style="width: 12%">Total Advances</th>
                                            <th style="width: 12%">Remaining Balance</th>
                                            <th style="width: 15%">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($employee->salaryHistories as $salary)
                                            @php
                                                $totalAdvances = $salary->salaryAdvances()->where('status', 'approved')->sum('advance_amount');
                                                $remainingBalance = $salary->salary_amount - $totalAdvances;
                                            @endphp
                                            <tr>
                                                <td>
                                                    <strong>{{ $salary->effective_from->format('F Y') }}</strong>
                                                </td>
                                                <td>{{ $salary->effective_from->format('d-M-Y') }}</td>
                                                <td>
                                                    <strong>PKR {{ number_format($salary->salary_amount, 2) }}</strong>
                                                </td>
                                                <td>
                                                    <span class="badge badge-info">{{ ucfirst($salary->salary_type) }}</span>
                                                </td>
                                                <td>
                                                    <span class="badge badge-warning">
                                                        PKR {{ number_format($totalAdvances, 2) }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge badge-{{ $remainingBalance > 0 ? 'success' : 'danger' }}">
                                                        PKR {{ number_format(max(0, $remainingBalance), 2) }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <button type="button" class="btn btn-xs btn-info" data-toggle="modal"
                                                        data-target="#viewAdvancesModal{{ $salary->id }}"
                                                        title="View Advances">
                                                        <i class="fas fa-eye"></i> Advances
                                                    </button>
                                                    @if($employee->salaryHistories->count() > 1)
                                                        <form action="{{ route('salary-histories.destroy', [$employee, $salary]) }}"
                                                            method="POST" style="display:inline;" onsubmit="return confirm('Delete this salary record?');">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-xs btn-danger" title="Delete">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="alert alert-warning">
                                No salary history found.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal: View Advances for Salary Record --}}
    @foreach($employee->salaryHistories as $salary)
        <div class="modal fade" id="viewAdvancesModal{{ $salary->id }}" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Advances for {{ $salary->effective_from->format('F Y') }}</h5>
                        <button type="button" class="close" data-dismiss="modal">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        @php
                            $salaryAdvances = $salary->salaryAdvances()->orderByDesc('advance_date')->get();
                        @endphp
                        @if($salaryAdvances->count() > 0)
                            <table class="table table-bordered table-sm">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Remarks</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($salaryAdvances as $advance)
                                        <tr>
                                            <td>{{ $advance->advance_date->format('d-M-Y') }}</td>
                                            <td>PKR {{ number_format($advance->advance_amount, 2) }}</td>
                                            <td>
                                                <span class="badge badge-{{ $advance->status === 'approved' ? 'success' : ($advance->status === 'rejected' ? 'danger' : 'warning') }}">
                                                    {{ ucfirst($advance->status) }}
                                                </span>
                                            </td>
                                            <td>{{ $advance->remarks ?? '-' }}</td>
                                            <td>
                                                <form action="{{ route('salary-advances.destroy', [$employee, $advance]) }}"
                                                    method="POST" style="display:inline;" onsubmit="return confirm('Delete this advance?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-xs btn-danger" title="Delete">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @else
                            <p class="text-muted">No advances recorded for this salary period.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endforeach

    {{-- Modal: View Log Details --}}
    @foreach($employee->salaryLogs as $log)
        <div class="modal fade" id="viewLogModal{{ $log->id }}" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Salary Log - {{ $log->log_date->format('F Y') }}</h5>
                        <button type="button" class="close" data-dismiss="modal">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Salary Amount:</strong> PKR {{ number_format($log->salary_amount, 2) }}</p>
                                <p><strong>Total Advances:</strong> PKR {{ number_format($log->total_advances, 2) }}</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Net Payable:</strong> PKR {{ number_format($log->net_payable, 2) }}</p>
                                <p><strong>Number of Advances:</strong> {{ $log->advance_count }}</p>
                            </div>
                        </div>
                        <hr>
                        @php
                            $monthAdvances = \App\Models\SalaryAdvance::where('employee_id', $employee->id)
                                ->whereBetween('advance_date', [
                                    $log->log_date,
                                    $log->log_date->endOfMonth()
                                ])
                                ->where('status', 'approved')
                                ->orderByDesc('advance_date')
                                ->get();
                        @endphp
                        <h6>Advances Details:</h6>
                        @if($monthAdvances->count() > 0)
                            <table class="table table-sm">
                                <tr>
                                    <th>Date</th>
                                    <th>Amount</th>
                                </tr>
                                @foreach($monthAdvances as $advance)
                                    <tr>
                                        <td>{{ $advance->advance_date->format('d-M-Y') }}</td>
                                        <td>PKR {{ number_format($advance->advance_amount, 2) }}</td>
                                    </tr>
                                @endforeach
                            </table>
                        @else
                            <p class="text-muted">No advances in this month.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endforeach
@stop

@section('css')
    <style>
        .info-box {
            margin-bottom: 10px;
        }
        .card {
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
    </style>
@stop

@section('js')
    <script>
        $(document).ready(function() {
            // You can add any additional JavaScript here
        });
    </script>
@stop
