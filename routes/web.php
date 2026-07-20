<?php

use App\Http\Controllers\BulkEntryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DieselRateHistoryController;
use App\Http\Controllers\DieselUsageEntryController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\EmployeeLeaveController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\FuelIssueController;
use App\Http\Controllers\FuelStockController;
use App\Http\Controllers\FuelStockMovementController;
use App\Http\Controllers\MachineController;
use App\Http\Controllers\MachineHourEntryController;
use App\Http\Controllers\MachinePartUsageController;
use App\Http\Controllers\MachineRateHistoryController;
use App\Http\Controllers\PartStockMovementController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SalaryAdvanceController;
use App\Http\Controllers\SalaryHistoryController;
use App\Http\Controllers\SalaryReportController;
use App\Http\Controllers\SiteController;
use App\Http\Controllers\SparePartController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Employee Management
    Route::resource('employees', EmployeeController::class);
    Route::resource('sites', SiteController::class);
    Route::get('/employees/{employee}/salary', [EmployeeController::class, 'salary'])->name('employees.salary');
    Route::post('/employees/{employee}/salary-histories', [SalaryHistoryController::class, 'store'])->name('salary-histories.store');
    Route::put('/employees/{employee}/salary-histories/{salaryHistory}', [SalaryHistoryController::class, 'update'])->name('salary-histories.update');
    Route::delete('/employees/{employee}/salary-histories/{salaryHistory}', [SalaryHistoryController::class, 'destroy'])->name('salary-histories.destroy');
    Route::get('/employees/{employee}/salary-histories', [SalaryHistoryController::class, 'getHistory'])->name('salary-histories.get-history');
    Route::post('/employees/{employee}/salary-advances', [SalaryAdvanceController::class, 'store'])->name('salary-advances.store');
    Route::post('/employees/{employee}/salary-advances/{advance}/approve', [SalaryAdvanceController::class, 'approve'])->name('salary-advances.approve');
    Route::post('/employees/{employee}/salary-advances/{advance}/reject', [SalaryAdvanceController::class, 'reject'])->name('salary-advances.reject');
    Route::delete('/employees/{employee}/salary-advances/{advance}', [SalaryAdvanceController::class, 'destroy'])->name('salary-advances.destroy');
    Route::get('/employees/{employee}/salary-advances', [SalaryAdvanceController::class, 'getAdvances'])->name('salary-advances.get-advances');

    // Employee Leave Management
    Route::post('/employees/{employee}/leaves', [EmployeeLeaveController::class, 'store'])->name('employee-leaves.store');
    Route::delete('/employees/{employee}/leaves/{leave}', [EmployeeLeaveController::class, 'destroy'])->name('employee-leaves.destroy');

    // Salary Reports
    Route::get('/reports/salary', [SalaryReportController::class, 'index'])->name('salary-reports.index');
    Route::get('/reports/salary/summary', [SalaryReportController::class, 'summary'])->name('salary-reports.summary');

    Route::resource('machines', MachineController::class);
    Route::resource('machine-rates', MachineRateHistoryController::class);
    Route::resource('diesel-rates', DieselRateHistoryController::class);
    Route::resource('machine-hours', MachineHourEntryController::class);
    Route::resource('diesel-usage', DieselUsageEntryController::class);
    Route::resource('payments', PaymentController::class);
    Route::resource('expenses', ExpenseController::class);
    Route::resource('spare-parts', SparePartController::class);
    Route::resource('part-stock-movements', PartStockMovementController::class);
    Route::resource('machine-part-usages', MachinePartUsageController::class);
    Route::resource('fuel-stocks', FuelStockController::class);
    Route::resource('fuel-stock-movements', FuelStockMovementController::class);
    Route::resource('fuel-issues', FuelIssueController::class);

    Route::get('/bulk/hours', [BulkEntryController::class, 'hoursForm'])->name('bulk.hours.form');
    Route::post('/bulk/hours', [BulkEntryController::class, 'hoursStore'])->name('bulk.hours.store');
    Route::get('/bulk/diesel', [BulkEntryController::class, 'dieselForm'])->name('bulk.diesel.form');
    Route::post('/bulk/diesel', [BulkEntryController::class, 'dieselStore'])->name('bulk.diesel.store');

    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/machine-hours', [ReportController::class, 'machineHours'])->name('reports.machine-hours');
    Route::get('/reports/diesel-usage', [ReportController::class, 'dieselUsage'])->name('reports.diesel-usage');
    Route::get('/reports/complete-hisab', [ReportController::class, 'completeHisab'])->name('reports.complete-hisab');
    Route::get('/reports/machine-ledger', [ReportController::class, 'machineLedger'])->name('reports.machine-ledger');
    Route::get('/reports/daily-summary', [ReportController::class, 'dailySummary'])->name('reports.daily-summary');
    Route::get('/reports/monthly-summary', [ReportController::class, 'monthlySummary'])->name('reports.monthly-summary');
    Route::get('/reports/inventory-stock', [ReportController::class, 'inventoryStock'])->name('reports.inventory-stock');
    Route::get('/reports/part-usage', [ReportController::class, 'partUsage'])->name('reports.part-usage');
    Route::get('/reports/machine-parts', [ReportController::class, 'machineParts'])->name('reports.machine-parts');
    Route::get('/reports/fuel-stock', [ReportController::class, 'fuelStock'])->name('reports.fuel-stock');
    Route::get('/reports/fuel-issues', [ReportController::class, 'fuelIssues'])->name('reports.fuel-issues');
    Route::get('/reports/fuel-consumption', [ReportController::class, 'fuelConsumption'])->name('reports.fuel-consumption');
    Route::get('/reports/{report}/export/{format}', [ReportController::class, 'export'])->name('reports.export');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
