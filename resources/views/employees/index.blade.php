@extends('adminlte::page')

@section('title', 'Employees')

@section('content_header')
    <div class="row">
        <div class="col-md-6">
            <h1>Employees</h1>
        </div>
        <div class="col-md-6 text-right">
            <a href="{{ route('employees.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add New Employee
            </a>
        </div>
    </div>
@stop

@section('content')
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <h4><i class="icon fa fa-ban"></i> Error!</h4>
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <i class="icon fa fa-check"></i> {{ session('success') }}
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Employee List</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover" id="employeesTable">
                    <thead class="thead-dark">
                        <tr>
                            <th style="width: 5%">#</th>
                            <th style="width: 15%">Name</th>
                            <th style="width: 12%">Code</th>
                            <th style="width: 15%">Designation</th>
                            <th style="width: 10%">Status</th>
                            <th style="width: 15%">Current Salary</th>
                            <th style="width: 15%">Contact</th>
                            <th style="width: 15%">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($employees as $employee)
                            @php
                                $currentSalary = $employee->salaryHistories->first();
                            @endphp
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td><strong>{{ $employee->name }}</strong></td>
                                <td>{{ $employee->employee_code }}</td>
                                <td>{{ $employee->designation ?? '-' }}</td>
                                <td>
                                    <span class="badge badge-{{ $employee->status === 'active' ? 'success' : 'danger' }}">
                                        {{ ucfirst($employee->status) }}
                                    </span>
                                </td>
                                <td>
                                    @if($currentSalary)
                                        <strong>PKR {{ number_format($currentSalary->salary_amount, 2) }}</strong>
                                        <br>
                                        <small class="text-muted">from {{ $currentSalary->effective_from->format('M Y') }}</small>
                                    @else
                                        <span class="text-danger">No Salary</span>
                                    @endif
                                </td>
                                <td>
                                    @if($employee->phone)
                                        <i class="fas fa-phone"></i> {{ $employee->phone }}
                                    @endif
                                    @if($employee->email)
                                        <br><i class="fas fa-envelope"></i> {{ $employee->email }}
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('employees.salary', $employee) }}" class="btn btn-xs btn-primary" title="Salary">
                                        <i class="fas fa-money-bill-wave"></i> Salary
                                    </a>
                                    <a href="{{ route('employees.edit', $employee) }}" class="btn btn-xs btn-warning" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('employees.destroy', $employee) }}" method="POST" style="display:inline;"
                                        onsubmit="return confirm('Delete this employee?');">
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
            </div>

            {{-- Pagination --}}
            <div class="mt-3">
                {{ $employees->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.25/css/dataTables.bootstrap4.min.css">
@stop

@section('js')
    <script src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.25/js/dataTables.bootstrap4.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#employeesTable').DataTable({
                "paging": false,
                "searching": true,
                "ordering": true,
            });
        });
    </script>
@stop
