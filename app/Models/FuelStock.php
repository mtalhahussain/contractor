<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class FuelStock extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'code',
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

    public function movements(): HasMany
    {
        return $this->hasMany(FuelStockMovement::class);
    }

    public function issues(): HasMany
    {
        return $this->hasMany(FuelIssue::class);
    }
}