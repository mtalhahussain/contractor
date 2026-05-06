<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DieselRateHistory extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'rate_per_liter',
        'effective_from_date',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'effective_from_date' => 'date',
            'rate_per_liter' => 'decimal:2',
        ];
    }
}
