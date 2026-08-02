<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\SalaryBonus;
use App\Services\PayrollService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SalaryBonusController extends Controller
{
    public function __construct(private readonly PayrollService $payrollService)
    {
    }

    /**
     * Store or update monthly bonus for an employee.
     */
    public function store(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            'bonus_month' => 'required|date_format:Y-m',
            'bonus_amount' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $monthDate = Carbon::createFromFormat('Y-m', $validated['bonus_month'])->startOfMonth()->toDateString();

        SalaryBonus::updateOrCreate(
            [
                'employee_id' => $employee->id,
                'bonus_month' => $monthDate,
            ],
            [
                'bonus_amount' => $validated['bonus_amount'],
                'notes' => $validated['notes'] ?? null,
                'created_by' => auth()->id(),
            ]
        );

        $month = Carbon::parse($monthDate);
        $this->payrollService->generateSalaryLog($employee, $month->year, $month->month);

        return redirect()->route('employees.salary', $employee)
            ->with('success', 'Monthly bonus saved successfully!');
    }

    /**
     * Delete monthly bonus.
     */
    public function destroy(Employee $employee, SalaryBonus $salaryBonus)
    {
        if ($salaryBonus->employee_id !== $employee->id) {
            abort(403, 'Unauthorized');
        }

        $month = Carbon::parse($salaryBonus->bonus_month);
        $salaryBonus->delete();

        $this->payrollService->generateSalaryLog($employee, $month->year, $month->month);

        return redirect()->route('employees.salary', $employee)
            ->with('success', 'Monthly bonus deleted successfully!');
    }
}
