<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\SalaryAdvance;
use App\Models\SalaryLog;
use App\Services\PayrollService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SalaryReportController extends Controller
{
    protected $payrollService;

    public function __construct(PayrollService $payrollService)
    {
        $this->payrollService = $payrollService;
    }

    /**
     * Display salary report for an employee
     */
    public function index(Request $request)
    {
        $employees = Employee::where('status', 'active')->orderBy('name')->get();
        $selectedEmployee = null;
        $monthYear = $request->input('month_year', now()->format('Y-m'));
        $reportData = null;

        if ($request->has('employee_id') && $request->input('employee_id')) {
            $selectedEmployee = Employee::findOrFail($request->input('employee_id'));
            
            // Parse month_year (format: YYYY-MM)
            [$year, $month] = explode('-', $monthYear);
            
            $reportData = $this->generateMonthlyReport($selectedEmployee, (int)$year, (int)$month);
        }

        return view('salary-reports.index', compact('employees', 'selectedEmployee', 'monthYear', 'reportData'));
    }

    /**
     * Generate monthly salary report for an employee
     */
    private function generateMonthlyReport($employee, $year, $month)
    {
        $startDate = Carbon::createFromDate($year, $month, 1);
        $endDate = $startDate->copy()->endOfMonth();

        // Get salary for this month
        $salaryRecord = $this->payrollService->getSalaryForMonth($employee, $year, $month);
        $salaryAmount = $salaryRecord?->salary_amount ?? 0;

        // Get all advances for this month
        $advances = SalaryAdvance::where('employee_id', $employee->id)
            ->whereBetween('advance_date', [$startDate, $endDate])
            ->orderBy('advance_date')
            ->get();

        $approvedAdvances = $advances->where('status', 'approved');
        $pendingAdvances = $advances->where('status', 'pending');
        $rejectedAdvances = $advances->where('status', 'rejected');

        $totalApprovedAdvances = $approvedAdvances->sum('advance_amount');
        $totalPendingAdvances = $pendingAdvances->sum('advance_amount');
        $totalRejectedAdvances = $rejectedAdvances->sum('advance_amount');

        // Calculate leave deduction
        $leaveDeduction = $this->payrollService->calculateLeaveDeduction($employee, $year, $month);
        $leaveDays = $this->payrollService->getLeaveDaysForMonth($employee, $year, $month);

        $remainingBalance = $salaryAmount - $totalApprovedAdvances - $leaveDeduction;

        // Get salary log for this month
        $salaryLog = $this->payrollService->getSalaryLog($employee, $year, $month);

        // Get last 12 months history
        $monthsHistory = collect();
        for ($i = 11; $i >= 0; $i--) {
            $historyDate = now()->subMonths($i);
            $historyLog = $this->payrollService->getSalaryLog($employee, $historyDate->year, $historyDate->month);
            
            if ($historyLog) {
                $monthsHistory->push([
                    'month' => $historyDate->format('F Y'),
                    'month_short' => $historyDate->format('M Y'),
                    'year' => $historyDate->year,
                    'month_num' => $historyDate->month,
                    'salary_amount' => $historyLog->salary_amount,
                    'total_advances' => $historyLog->total_advances,
                    'leave_deduction' => $historyLog->leave_deduction ?? 0,
                    'net_payable' => $historyLog->net_payable,
                    'advance_count' => $historyLog->advance_count,
                ]);
            }
        }

        return [
            'employee' => $employee,
            'year' => $year,
            'month' => $month,
            'month_year' => $startDate->format('F Y'),
            'salary_record' => $salaryRecord,
            'salary_amount' => $salaryAmount,
            'leave_days' => $leaveDays,
            'leave_deduction' => $leaveDeduction,
            'approved_advances' => $approvedAdvances,
            'pending_advances' => $pendingAdvances,
            'rejected_advances' => $rejectedAdvances,
            'total_approved_advances' => $totalApprovedAdvances,
            'total_pending_advances' => $totalPendingAdvances,
            'total_rejected_advances' => $totalRejectedAdvances,
            'remaining_balance' => max(0, $remainingBalance),
            'salary_log' => $salaryLog,
            'months_history' => $monthsHistory,
        ];
    }

    /**
     * Generate salary report for all employees (summary)
     */
    public function summary(Request $request)
    {
        $monthYear = $request->input('month_year', now()->format('Y-m'));
        [$year, $month] = explode('-', $monthYear);

        $employees = Employee::where('status', 'active')->orderBy('name')->get();

        $reportData = $employees->map(function ($employee) use ($year, $month) {
            $salaryRecord = $this->payrollService->getSalaryForMonth($employee, $year, $month);
            $salaryAmount = $salaryRecord?->salary_amount ?? 0;

            $advances = $this->payrollService->getAdvancesForMonth($employee, $year, $month, 'approved');
            $totalAdvances = $advances->sum('advance_amount');

            $leaveDeduction = $this->payrollService->calculateLeaveDeduction($employee, $year, $month);
            $leaveDays = $this->payrollService->getLeaveDaysForMonth($employee, $year, $month);

            $salaryLog = $this->payrollService->getSalaryLog($employee, $year, $month);

            return [
                'employee_code' => $employee->employee_code,
                'employee_name' => $employee->name,
                'designation' => $employee->designation ?? '-',
                'salary_amount' => $salaryAmount,
                'total_advances' => $totalAdvances,
                'leave_days' => $leaveDays,
                'leave_deduction' => $leaveDeduction,
                'net_payable' => $salaryLog?->net_payable ?? 0,
                'advance_count' => $salaryLog?->advance_count ?? 0,
            ];
        })->filter(function ($item) {
            return $item['salary_amount'] > 0; // Only show employees with salary
        });

        $month_year = Carbon::createFromDate($year, $month, 1)->format('F Y');
        $totalSalary = $reportData->sum('salary_amount');
        $totalAdvances = $reportData->sum('total_advances');
        $totalNetPayable = $reportData->sum('net_payable');

        return view('salary-reports.summary', compact('reportData', 'monthYear', 'month_year', 'totalSalary', 'totalAdvances', 'totalNetPayable', 'employees'));
    }
}
