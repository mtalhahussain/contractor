<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
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

    public function partUsages(): HasMany
    {
        return $this->hasMany(MachinePartUsage::class);
    }

    public function partStockMovements(): HasMany
    {
        return $this->hasMany(PartStockMovement::class);
    }

    public function fuelIssues(): HasMany
    {
        return $this->hasMany(FuelIssue::class);
    }

    public function fuelStockMovements(): HasMany
    {
        return $this->hasMany(FuelStockMovement::class);
    }

    public function siteAssignments(): HasMany
    {
        return $this->hasMany(MachineSiteAssignment::class);
    }

    public function currentSiteAssignment(): HasOne
    {
        return $this->hasOne(MachineSiteAssignment::class)
            ->whereNull('assigned_to')
            ->latestOfMany('assigned_from');
    }
}
