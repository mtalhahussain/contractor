<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class MachineRateHistory extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'machine_id',
        'hourly_rate',
        'effective_from_date',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'effective_from_date' => 'date',
            'hourly_rate' => 'decimal:2',
        ];
    }

    public function machine(): BelongsTo
    {
        return $this->belongsTo(Machine::class);
    }
}
