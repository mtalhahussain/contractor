<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\EmployeeLeave;
use App\Services\PayrollService;
use Illuminate\Http\Request;

class EmployeeLeaveController extends Controller
{
    protected $payrollService;

    public function __construct(PayrollService $payrollService)
    {
        $this->payrollService = $payrollService;
    }

    /**
     * Store a new leave record
     */
    public function store(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            'leave_date' => 'required|date|unique:employee_leaves,leave_date,NULL,id,employee_id,' . $employee->id,
            'reason' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:500',
        ]);

        $leave = EmployeeLeave::create([
            'employee_id' => $employee->id,
            'leave_date' => $validated['leave_date'],
            'reason' => $validated['reason'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        // Regenerate salary logs for the affected month
        $leaveDate = $leave->leave_date;
        $this->payrollService->generateSalaryLog($employee, $leaveDate->year, $leaveDate->month);

        return redirect()->route('employees.salary', $employee)
            ->with('success', 'Leave record added successfully.');
    }

    /**
     * Delete a leave record
     */
    public function destroy(Request $request, Employee $employee, EmployeeLeave $leave)
    {
        $leaveDate = $leave->leave_date;
        
        $leave->delete();

        // Regenerate salary logs for the affected month
        $this->payrollService->generateSalaryLog($employee, $leaveDate->year, $leaveDate->month);

        return redirect()->route('employees.salary', $employee)
            ->with('success', 'Leave record deleted successfully.');
    }
}
