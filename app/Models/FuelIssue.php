<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class FuelIssue extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'date',
        'fuel_stock_id',
        'machine_id',
        'consumer_type',
        'consumer_name',
        'quantity',
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

    public function fuelStock(): BelongsTo
    {
        return $this->belongsTo(FuelStock::class);
    }

    public function machine(): BelongsTo
    {
        return $this->belongsTo(Machine::class);
    }

    public function movement(): HasOne
    {
        return $this->hasOne(FuelStockMovement::class);
    }
}