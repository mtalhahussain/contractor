<?php

use App\Http\Controllers\BulkEntryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DieselRateHistoryController;
use App\Http\Controllers\DieselUsageEntryController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\MachineController;
use App\Http\Controllers\MachineHourEntryController;
use App\Http\Controllers\MachineRateHistoryController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('machines', MachineController::class);
    Route::resource('machine-rates', MachineRateHistoryController::class);
    Route::resource('diesel-rates', DieselRateHistoryController::class);
    Route::resource('machine-hours', MachineHourEntryController::class);
    Route::resource('diesel-usage', DieselUsageEntryController::class);
    Route::resource('payments', PaymentController::class);
    Route::resource('expenses', ExpenseController::class);

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
    Route::get('/reports/{report}/export/{format}', [ReportController::class, 'export'])->name('reports.export');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
