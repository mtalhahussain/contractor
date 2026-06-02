<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class FuelStockMovement extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'date',
        'fuel_stock_id',
        'fuel_issue_id',
        'machine_id',
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

    public function fuelStock(): BelongsTo
    {
        return $this->belongsTo(FuelStock::class);
    }

    public function issue(): BelongsTo
    {
        return $this->belongsTo(FuelIssue::class, 'fuel_issue_id');
    }

    public function machine(): BelongsTo
    {
        return $this->belongsTo(Machine::class);
    }
}