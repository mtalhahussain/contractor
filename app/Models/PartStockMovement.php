<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PartStockMovement extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'date',
        'spare_part_id',
        'machine_id',
        'machine_part_usage_id',
        'movement_type',
        'quantity',
        'balance_after',
        'reference',
        'remarks',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'quantity' => 'decimal:2',
            'balance_after' => 'decimal:2',
        ];
    }

    public function sparePart(): BelongsTo
    {
        return $this->belongsTo(SparePart::class);
    }

    public function machine(): BelongsTo
    {
        return $this->belongsTo(Machine::class);
    }

    public function usage(): BelongsTo
    {
        return $this->belongsTo(MachinePartUsage::class, 'machine_part_usage_id');
    }
}