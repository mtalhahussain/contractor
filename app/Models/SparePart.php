<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SparePart extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'part_number',
        'category',
        'unit',
        'current_stock',
        'minimum_stock',
        'location',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'current_stock' => 'decimal:2',
            'minimum_stock' => 'decimal:2',
        ];
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(PartStockMovement::class);
    }

    public function usages(): HasMany
    {
        return $this->hasMany(MachinePartUsage::class);
    }
}