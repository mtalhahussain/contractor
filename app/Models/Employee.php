<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'designation',
        'employee_code',
        'phone',
        'email',
        'address',
        'cnic',
        'bank_account',
        'bank_name',
        'status',
        'joining_date',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'joining_date' => 'date',
        ];
    }

    /**
     * Boot method to auto-generate employee code
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($employee) {
            if (empty($employee->employee_code)) {
                $employee->employee_code = self::generateEmployeeCode();
            }
        });
    }

    /**
     * Generate unique employee code
     * Format: EMP + 5-digit number (e.g., EMP00001)
     */
    public static function generateEmployeeCode(): string
    {
        // Include soft-deleted records so codes are never re-used.
        $nextNumber = (self::withTrashed()->max('id') ?? 0) + 1;

        do {
            $code = 'EMP' . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
            $exists = self::withTrashed()->where('employee_code', $code)->exists();
            $nextNumber++;
        } while ($exists);

        return $code;
    }

    public function salaryHistories(): HasMany
    {
        return $this->hasMany(SalaryHistory::class)->orderByDesc('effective_from');
    }

    public function salaryAdvances(): HasMany
    {
        return $this->hasMany(SalaryAdvance::class)->orderByDesc('advance_date');
    }

    public function salaryLogs(): HasMany
    {
        return $this->hasMany(SalaryLog::class)->orderByDesc('log_date');
    }

    public function leaves(): HasMany
    {
        return $this->hasMany(EmployeeLeave::class)->orderByDesc('leave_date');
    }

    /**
     * Get the current active salary (latest effective salary)
     */
    public function currentSalary()
    {
        return $this->salaryHistories()->first();
    }

    /**
     * Get salary effective at a specific date
     */
    public function getSalaryAt($date)
    {
        return $this->salaryHistories()
            ->where('effective_from', '<=', $date)
            ->orderByDesc('effective_from')
            ->first();
    }

    /**
     * Get salary for a specific month (e.g., July 2026)
     */
    public function getSalaryForMonth($year, $month)
    {
        $targetDate = date('Y-m-d', mktime(0, 0, 0, $month, 1, $year));
        return $this->getSalaryAt($targetDate);
    }
}
