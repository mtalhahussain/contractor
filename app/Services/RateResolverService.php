<?php

namespace App\Services;

use App\Models\DieselRateHistory;
use App\Models\MachineRateHistory;
use Carbon\CarbonInterface;

class RateResolverService
{
    public function getDieselRate(string|CarbonInterface $date): float
    {
        $rate = DieselRateHistory::query()
            ->whereDate('effective_from_date', '<=', $date)
            ->orderByDesc('effective_from_date')
            ->value('rate_per_liter');

        return (float) ($rate ?? 0);
    }

    public function getMachineRate(int $machineId, string|CarbonInterface $date): float
    {
        $rate = MachineRateHistory::query()
            ->where('machine_id', $machineId)
            ->whereDate('effective_from_date', '<=', $date)
            ->orderByDesc('effective_from_date')
            ->value('hourly_rate');

        return (float) ($rate ?? 0);
    }
}
