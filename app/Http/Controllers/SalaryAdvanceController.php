<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\SalaryHistory;
use App\Models\SalaryAdvance;
use App\Services\PayrollService;
use Illuminate\Http\Request;

class SalaryAdvanceController extends Controller
{
    protected $payrollService;

    public function __construct(PayrollService $payrollService)
    {
        $this->payrollService = $payrollService;
    }

    /**
     * Store a new salary advance
     */
    public function store(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            'advance_date' => 'required|date',
            'advance_amount' => 'required|numeric|min:0.01',
            'salary_history_id' => 'required|exists:salary_histories,id',
            'remarks' => 'nullable|string',
        ]);

        // Verify salary history belongs to this employee
        $salaryHistory = SalaryHistory::where('id', $validated['salary_history_id'])
            ->where('employee_id', $employee->id)
            ->firstOrFail();

        $advance = $this->payrollService->createSalaryAdvance(
            $employee,
            $salaryHistory,
            $validated['advance_date'],
            $validated['advance_amount'],
            $validated['remarks'],
            auth()->id()
        );

        // Auto-approve advances (adjust as needed)
        $advance->approve(auth()->id());

        // Regenerate salary log
        $date = \Carbon\Carbon::parse($validated['advance_date']);
        $this->payrollService->generateSalaryLog($employee, $date->year, $date->month);

        return redirect()->route('employees.salary', $employee)
            ->with('success', 'Salary advance recorded successfully!');
    }

    /**
     * Approve a salary advance
     */
    public function approve(Employee $employee, SalaryAdvance $advance)
    {
        if ($advance->employee_id !== $employee->id) {
            abort(403, 'Unauthorized');
        }

        $advance->approve(auth()->id());

        // Regenerate salary log
        $date = \Carbon\Carbon::parse($advance->advance_date);
        $this->payrollService->generateSalaryLog($employee, $date->year, $date->month);

        return redirect()->route('employees.salary', $employee)
            ->with('success', 'Salary advance approved!');
    }

    /**
     * Reject a salary advance
     */
    public function reject(Employee $employee, SalaryAdvance $advance)
    {
        if ($advance->employee_id !== $employee->id) {
            abort(403, 'Unauthorized');
        }

        $advance->reject();

        // Regenerate salary log
        $date = \Carbon\Carbon::parse($advance->advance_date);
        $this->payrollService->generateSalaryLog($employee, $date->year, $date->month);

        return redirect()->route('employees.salary', $employee)
            ->with('success', 'Salary advance rejected!');
    }

    /**
     * Delete a salary advance
     */
    public function destroy(Employee $employee, SalaryAdvance $advance)
    {
        if ($advance->employee_id !== $employee->id) {
            abort(403, 'Unauthorized');
        }

        $date = \Carbon\Carbon::parse($advance->advance_date);
        $advance->delete();

        // Regenerate salary log
        $this->payrollService->generateSalaryLog($employee, $date->year, $date->month);

        return redirect()->route('employees.salary', $employee)
            ->with('success', 'Salary advance deleted!');
    }

    /**
     * Get advances for an employee (JSON response for AJAX)
     */
    public function getAdvances(Employee $employee, $year = null, $month = null)
    {
        if (!$year || !$month) {
            $year = now()->year;
            $month = now()->month;
        }

        $advances = $this->payrollService->getAdvancesForMonth($employee, $year, $month);

        return response()->json([
            'success' => true,
            'data' => $advances->map(function ($advance) {
                return [
                    'id' => $advance->id,
                    'advance_date' => $advance->advance_date->format('Y-m-d'),
                    'advance_amount' => $advance->advance_amount,
                    'status' => $advance->status,
                    'remarks' => $advance->remarks,
                ];
            }),
        ]);
    }
}
