<?php

namespace App\Http\Controllers;

use App\Models\DieselUsageEntry;
use App\Models\Expense;
use App\Models\MachineHourEntry;
use App\Models\Payment;
use App\Services\RateResolverService;
use Carbon\Carbon;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(RateResolverService $rateResolver): View
    {
        $today = Carbon::today();
        $monthStart = Carbon::now()->startOfMonth();
        $monthEnd = Carbon::now()->endOfMonth();

        $todayHours = (float) MachineHourEntry::query()
            ->whereDate('date', $today)
            ->sum('working_hours');

        $todayDiesel = (float) DieselUsageEntry::query()
            ->whereDate('date', $today)
            ->sum('diesel_liters');

        $monthlyHourEntries = MachineHourEntry::query()
            ->whereBetween('date', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->get(['date', 'machine_id', 'working_hours']);

        $monthlyEarning = $monthlyHourEntries->sum(function ($entry) use ($rateResolver) {
            $rate = $rateResolver->getMachineRate($entry->machine_id, $entry->date);

            return (float) $entry->working_hours * $rate;
        });

        $monthlyDieselEntries = DieselUsageEntry::query()
            ->whereBetween('date', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->get(['date', 'diesel_liters']);

        $monthlyDieselCost = $monthlyDieselEntries->sum(function ($entry) use ($rateResolver) {
            $rate = $rateResolver->getDieselRate($entry->date);

            return (float) $entry->diesel_liters * $rate;
        });

        $monthlyPayments = (float) Payment::query()
            ->whereBetween('date', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->sum('amount_received');

        $monthlyExpenses = (float) Expense::query()
            ->whereBetween('date', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->sum('amount');

        $monthlyBalance = $monthlyEarning - $monthlyDieselCost - $monthlyPayments - $monthlyExpenses;

        return view('dashboard', compact(
            'todayHours',
            'todayDiesel',
            'monthlyEarning',
            'monthlyDieselCost',
            'monthlyBalance'
        ));
    }
}
