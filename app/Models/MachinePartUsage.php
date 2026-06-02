<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class MachinePartUsage extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'date',
        'machine_id',
        'spare_part_id',
        'quantity',
        'usage_type',
        'reference',
        'remarks',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'quantity' => 'decimal:2',
        ];
    }

    public function machine(): BelongsTo
    {
        return $this->belongsTo(Machine::class);
    }

    public function sparePart(): BelongsTo
    {
        return $this->belongsTo(SparePart::class);
    }

    public function stockMovement(): HasOne
    {
        return $this->hasOne(PartStockMovement::class);
    }
}