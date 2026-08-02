<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\SalaryHistory;
use App\Models\SalaryAdvance;
use App\Models\SalaryBonus;
use App\Models\SalaryLog;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class PayrollService
{
    /**
     * Create a new salary record for an employee
     * Does NOT overwrite existing records - creates new history entry
     */
    public function createSalaryRecord(Employee $employee, $effectiveDate, $salaryAmount, $salaryType = 'monthly', $notes = null, $createdBy = null): SalaryHistory
    {
        return SalaryHistory::create([
            'employee_id' => $employee->id,
            'effective_from' => Carbon::parse($effectiveDate),
            'salary_amount' => $salaryAmount,
            'salary_type' => $salaryType,
            'notes' => $notes,
            'created_by' => $createdBy,
        ]);
    }

    /**
     * Get the salary effective at a specific date
     */
    public function getSalaryAt(Employee $employee, $date): ?SalaryHistory
    {
        return SalaryHistory::where('employee_id', $employee->id)
            ->where('effective_from', '<=', Carbon::parse($date))
            ->orderByDesc('effective_from')
            ->first();
    }

    /**
     * Get salary for a specific month (uses first day of month to find effective salary)
     */
    public function getSalaryForMonth(Employee $employee, $year, $month): ?SalaryHistory
    {
        $targetDate = Carbon::createFromDate($year, $month, 1);
        return $this->getSalaryAt($employee, $targetDate);
    }

    /**
     * Create a salary advance for an employee
     */
    public function createSalaryAdvance(Employee $employee, SalaryHistory $salaryHistory, $advanceDate, $advanceAmount, $remarks = null, $createdBy = null): SalaryAdvance
    {
        return SalaryAdvance::create([
            'employee_id' => $employee->id,
            'salary_history_id' => $salaryHistory->id,
            'advance_date' => Carbon::parse($advanceDate),
            'advance_amount' => $advanceAmount,
            'status' => 'pending',
            'remarks' => $remarks,
            'created_by' => $createdBy,
        ]);
    }

    /**
     * Get all advances for a salary record
     */
    public function getAdvancesForSalaryRecord(SalaryHistory $salaryHistory, $status = 'approved')
    {
        $query = $salaryHistory->salaryAdvances();

        if ($status) {
            $query->where('status', $status);
        }

        return $query->get();
    }

    /**
     * Get total approved advances for a salary record
     */
    public function getTotalAdvancesForSalaryRecord(SalaryHistory $salaryHistory): float
    {
        return (float) $salaryHistory->salaryAdvances()
            ->where('status', 'approved')
            ->sum('advance_amount') ?? 0;
    }

    /**
     * Get advances for a specific month
     */
    public function getAdvancesForMonth(Employee $employee, $year, $month, $status = 'approved')
    {
        $startDate = Carbon::createFromDate($year, $month, 1)->startOfDay();
        $endDate = $startDate->copy()->endOfMonth();

        $query = SalaryAdvance::where('employee_id', $employee->id)
            ->whereBetween('advance_date', [$startDate, $endDate]);

        if ($status) {
            $query->where('status', $status);
        }

        return $query->orderByDesc('advance_date')->get();
    }

    /**
     * Get total leave days for a specific month
     */
    public function getLeaveDaysForMonth(Employee $employee, $year, $month): int
    {
        $startDate = Carbon::createFromDate($year, $month, 1)->startOfDay();
        $endDate = $startDate->copy()->endOfMonth();

        return $employee->leaves()
            ->whereBetween('leave_date', [$startDate, $endDate])
            ->count();
    }

    /**
     * Calculate leave deduction for a month
     * Formula: (salary / days_in_month) × leave_days
     */
    public function calculateLeaveDeduction(Employee $employee, $year, $month): float
    {
        $salaryRecord = $this->getSalaryForMonth($employee, $year, $month);
        
        if (!$salaryRecord) {
            return 0;
        }

        $leaveDays = $this->getLeaveDaysForMonth($employee, $year, $month);
        
        if ($leaveDays === 0) {
            return 0;
        }

        $daysInMonth = Carbon::createFromDate($year, $month, 1)->daysInMonth;
        $perDayAmount = $salaryRecord->salary_amount / $daysInMonth;

        return round($perDayAmount * $leaveDays, 2);
    }

    /**
     * Generate or update monthly salary log with leave deduction
     */
    public function generateSalaryLog(Employee $employee, $year, $month): SalaryLog
    {
        $logDate = Carbon::createFromDate($year, $month, 1)->format('Y-m-d');

        $salaryRecord = $this->getSalaryForMonth($employee, $year, $month);
        $salaryAmount = $salaryRecord?->salary_amount ?? 0;
        $bonusAmount = $this->getBonusForMonth($employee, $year, $month);

        // Get all approved advances for this month
        $advances = $this->getAdvancesForMonth($employee, $year, $month, 'approved');
        $totalAdvances = $advances->sum('advance_amount');

        // Calculate leave deduction
        $leaveDeduction = $this->calculateLeaveDeduction($employee, $year, $month);

        // Net payable = salary + bonus - advances - leave_deduction
        $netPayable = $salaryAmount + $bonusAmount - $totalAdvances - $leaveDeduction;

        return SalaryLog::updateOrCreate(
            [
                'employee_id' => $employee->id,
                'log_date' => $logDate,
            ],
            [
                'salary_amount' => $salaryAmount,
                'bonus_amount' => $bonusAmount,
                'total_advances' => $totalAdvances,
                'leave_deduction' => $leaveDeduction,
                'net_payable' => max(0, $netPayable),
                'advance_count' => $advances->count(),
            ]
        );
    }

    /**
     * Get monthly salary log for an employee
     */
    public function getSalaryLog(Employee $employee, $year, $month): ?SalaryLog
    {
        $logDate = Carbon::createFromDate($year, $month, 1)->format('Y-m-d');

        return SalaryLog::where('employee_id', $employee->id)
            ->where('log_date', $logDate)
            ->first();
    }

    /**
     * Get total bonus for a specific month.
     */
    public function getBonusForMonth(Employee $employee, $year, $month): float
    {
        $bonusMonth = Carbon::createFromDate($year, $month, 1)->format('Y-m-d');

        return (float) SalaryBonus::where('employee_id', $employee->id)
            ->whereDate('bonus_month', $bonusMonth)
            ->sum('bonus_amount');
    }

    /**
     * Get salary logs for an employee for a date range
     */
    public function getSalaryLogsForRange(Employee $employee, $fromDate, $toDate)
    {
        return SalaryLog::where('employee_id', $employee->id)
            ->whereBetween('log_date', [$fromDate, $toDate])
            ->orderByDesc('log_date')
            ->get();
    }

    /**
     * Get all employees with their current salaries
     */
    public function getAllEmployeesWithSalaries($status = 'active'): Collection
    {
        $query = Employee::query();

        if ($status) {
            $query->where('status', $status);
        }

        return $query->with('salaryHistories')
            ->orderBy('name')
            ->get();
    }

    /**
     * Calculate remaining balance (salary - advances) for a salary record
     */
    public function calculateRemainingBalance(SalaryHistory $salaryHistory): float
    {
        $totalAdvances = $this->getTotalAdvancesForSalaryRecord($salaryHistory);
        return max(0, $salaryHistory->salary_amount - $totalAdvances);
    }

    /**
     * Get salary change history for an employee (shows all changes over time)
     */
    public function getSalaryChangeHistory(Employee $employee)
    {
        return $employee->salaryHistories()
            ->with(['salaryAdvances' => function ($query) {
                $query->where('status', 'approved')->orderByDesc('advance_date');
            }])
            ->orderByDesc('effective_from')
            ->get()
            ->map(function ($salary) {
                $year = $salary->effective_from->year;
                $month = $salary->effective_from->month;

                return [
                    'id' => $salary->id,
                    'employee_id' => $salary->employee_id,
                    'effective_from' => $salary->effective_from,
                    'month_year' => $salary->effective_from->format('F Y'),
                    'salary_amount' => $salary->salary_amount,
                    'bonus_amount' => $this->getBonusForMonth($salary->employee, $year, $month),
                    'salary_type' => $salary->salary_type,
                    'total_advances' => $salary->salaryAdvances->sum('advance_amount'),
                    'advances_count' => $salary->salaryAdvances->count(),
                    'remaining_balance' => $this->calculateRemainingBalance($salary),
                    'notes' => $salary->notes,
                    'created_at' => $salary->created_at,
                ];
            });
    }
}
