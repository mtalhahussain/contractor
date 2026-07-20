<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SalaryHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'effective_from',
        'salary_amount',
        'salary_type',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'effective_from' => 'date',
            'salary_amount' => 'decimal:2',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function salaryAdvances(): HasMany
    {
        return $this->hasMany(SalaryAdvance::class)->orderByDesc('advance_date');
    }

    /**
     * Get total approved advances for this salary record
     */
    public function getTotalAdvancesAttribute()
    {
        return $this->salaryAdvances()
            ->where('status', 'approved')
            ->sum('advance_amount') ?? 0;
    }

    /**
     * Get month-year as formatted string
     */
    public function getMonthYearAttribute()
    {
        return $this->effective_from->format('F Y');
    }
}
