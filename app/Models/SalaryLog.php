<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalaryLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'log_date',
        'salary_amount',
        'bonus_amount',
        'total_advances',
        'leave_deduction',
        'net_payable',
        'advance_count',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'log_date' => 'date',
            'salary_amount' => 'decimal:2',
            'bonus_amount' => 'decimal:2',
            'total_advances' => 'decimal:2',
            'leave_deduction' => 'decimal:2',
            'net_payable' => 'decimal:2',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Get month-year as formatted string
     */
    public function getMonthYearAttribute()
    {
        return $this->log_date->format('F Y');
    }
}
