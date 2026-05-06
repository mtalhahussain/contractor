<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Machine extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'type',
        'owner_category',
        'machine_code',
        'status',
        'notes',
        'created_by',
    ];

    public function rateHistories(): HasMany
    {
        return $this->hasMany(MachineRateHistory::class);
    }

    public function hourEntries(): HasMany
    {
        return $this->hasMany(MachineHourEntry::class);
    }

    public function dieselEntries(): HasMany
    {
        return $this->hasMany(DieselUsageEntry::class);
    }
}
