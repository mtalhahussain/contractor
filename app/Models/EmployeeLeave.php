<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeLeave extends Model
{
    protected $table = 'employee_leaves';

    protected $fillable = [
        'employee_id',
        'leave_date',
        'reason',
        'notes',
    ];

    protected $casts = [
        'leave_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the employee that has this leave
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
