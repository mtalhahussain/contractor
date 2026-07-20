<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\SalaryHistory;
use App\Services\PayrollService;
use Illuminate\Http\Request;

class SalaryHistoryController extends Controller
{
    protected $payrollService;

    public function __construct(PayrollService $payrollService)
    {
        $this->payrollService = $payrollService;
    }

    /**
     * Store a new salary record (does NOT overwrite previous records)
     */
    public function store(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            'effective_from' => 'required|date',
            'salary_amount' => 'required|numeric|min:0',
            'salary_type' => 'required|in:monthly,hourly',
            'notes' => 'nullable|string',
        ]);

        // Check if salary already exists for this date
        $existingSalary = SalaryHistory::where('employee_id', $employee->id)
            ->where('effective_from', $validated['effective_from'])
            ->first();

        if ($existingSalary) {
            return redirect()->route('employees.salary', $employee)
                ->with('error', 'A salary record already exists for this date. Please choose a different date.');
        }

        $this->payrollService->createSalaryRecord(
            $employee,
            $validated['effective_from'],
            $validated['salary_amount'],
            $validated['salary_type'],
            $validated['notes'],
            auth()->id()
        );

        // Generate/update salary log for the month
        $date = \Carbon\Carbon::parse($validated['effective_from']);
        $this->payrollService->generateSalaryLog($employee, $date->year, $date->month);

        return redirect()->route('employees.salary', $employee)
            ->with('success', 'Salary record created successfully! Previous records are preserved.');
    }

    /**
     * Update a salary record
     */
    public function update(Request $request, Employee $employee, SalaryHistory $salaryHistory)
    {
        $validated = $request->validate([
            'salary_amount' => 'required|numeric|min:0',
            'salary_type' => 'required|in:monthly,hourly',
            'notes' => 'nullable|string',
        ]);

        $salaryHistory->update($validated);

        // Regenerate salary log
        $this->payrollService->generateSalaryLog($employee, $salaryHistory->effective_from->year, $salaryHistory->effective_from->month);

        return redirect()->route('employees.salary', $employee)
            ->with('success', 'Salary record updated successfully!');
    }

    /**
     * Delete a salary record
     */
    public function destroy(Employee $employee, SalaryHistory $salaryHistory)
    {
        // Only allow deletion if it's not the only salary record
        if ($employee->salaryHistories()->count() <= 1) {
            return redirect()->route('employees.salary', $employee)
                ->with('error', 'Cannot delete the only salary record. Employee must have at least one salary record.');
        }

        $year = $salaryHistory->effective_from->year;
        $month = $salaryHistory->effective_from->month;

        $salaryHistory->delete();

        // Regenerate salary log after deletion
        $this->payrollService->generateSalaryLog($employee, $year, $month);

        return redirect()->route('employees.salary', $employee)
            ->with('success', 'Salary record deleted successfully!');
    }

    /**
     * Get salary history for an employee (JSON response for AJAX)
     */
    public function getHistory(Employee $employee)
    {
        $history = $this->payrollService->getSalaryChangeHistory($employee);

        return response()->json([
            'success' => true,
            'data' => $history,
        ]);
    }
}
