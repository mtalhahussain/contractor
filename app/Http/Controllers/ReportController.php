<?php

namespace App\Http\Controllers;

use App\Exports\ArrayExport;
use App\Models\DieselUsageEntry;
use App\Models\Expense;
use App\Models\FuelIssue;
use App\Models\FuelStock;
use App\Models\FuelStockMovement;
use App\Models\Machine;
use App\Models\MachineHourEntry;
use App\Models\MachinePartUsage;
use App\Models\Payment;
use App\Models\Site;
use App\Models\SparePart;
use App\Services\RateResolverService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

class ReportController extends Controller
{
    public function index(): View
    {
        $machines = Machine::query()->orderBy('name')->get();

        return view('reports.index', compact('machines'));
    }

    public function machineHours(Request $request, RateResolverService $rateResolver): View
    {
        [$from, $to] = $this->dateRange($request);
        $siteId = $this->selectedSiteId($request);

        $entries = $this->applySiteFilterByMachineAssignment(
            MachineHourEntry::query(),
            $siteId,
            'machine_hour_entries.date'
        )
            ->with('machine')
            ->whereBetween('date', [$from, $to])
            ->when($request->filled('machine_id'), fn ($query) => $query->where('machine_id', $request->machine_id))
            ->orderBy('date')
            ->get();

        $rows = $entries->map(function ($entry) use ($rateResolver) {
            $rate = $rateResolver->getMachineRate($entry->machine_id, $entry->date);
            $earning = (float) $entry->working_hours * $rate;

            return [
                'date' => $entry->date->format('Y-m-d'),
                'machine' => $entry->machine?->name,
                'hours' => (float) $entry->working_hours,
                'applied_rate' => $rate,
                'earning' => $earning,
            ];
        });

        return view('reports.machine-hours', [
            'rows' => $rows,
            'machines' => $this->machineOptionsForRange($siteId, $from, $to),
            'sites' => $this->activeSiteOptions(),
            'from' => $from,
            'to' => $to,
        ]);
    }

    public function dieselUsage(Request $request, RateResolverService $rateResolver): View
    {
        [$from, $to] = $this->dateRange($request);
        $siteId = $this->selectedSiteId($request);

        $entries = $this->applySiteFilterByMachineAssignment(
            DieselUsageEntry::query(),
            $siteId,
            'diesel_usage_entries.date'
        )
            ->with('machine')
            ->whereBetween('date', [$from, $to])
            ->when($request->filled('machine_id'), fn ($query) => $query->where('machine_id', $request->machine_id))
            ->orderBy('date')
            ->get();

        $rows = $entries->map(function ($entry) use ($rateResolver) {
            $rate = $rateResolver->getDieselRate($entry->date);
            $cost = (float) $entry->diesel_liters * $rate;

            return [
                'date' => $entry->date->format('Y-m-d'),
                'machine' => $entry->machine?->name,
                'liters' => (float) $entry->diesel_liters,
                'diesel_rate' => $rate,
                'cost' => $cost,
            ];
        });

        return view('reports.diesel-usage', [
            'rows' => $rows,
            'machines' => $this->machineOptionsForRange($siteId, $from, $to),
            'sites' => $this->activeSiteOptions(),
            'from' => $from,
            'to' => $to,
        ]);
    }

    public function completeHisab(Request $request, RateResolverService $rateResolver): View
    {
        [$from, $to] = $this->dateRange($request);
        $siteId = $this->selectedSiteId($request);

        $machines = $this->applySiteOverlapToMachineQuery(Machine::query(), $siteId, $from, $to)
            ->when($request->filled('machine_id'), fn ($query) => $query->where('id', $request->machine_id))
            ->orderBy('name')
            ->get();

        $rows = $machines->map(function ($machine) use ($from, $to, $rateResolver, $siteId) {
            $hoursEntries = $this->applySiteFilterByMachineAssignment(
                MachineHourEntry::query(),
                $siteId,
                'machine_hour_entries.date'
            )
                ->where('machine_id', $machine->id)
                ->whereBetween('date', [$from, $to])
                ->get();

            $dieselEntries = $this->applySiteFilterByMachineAssignment(
                DieselUsageEntry::query(),
                $siteId,
                'diesel_usage_entries.date'
            )
                ->where('machine_id', $machine->id)
                ->whereBetween('date', [$from, $to])
                ->get();

            $totalHours = (float) $hoursEntries->sum('working_hours');
            $grossAmount = $hoursEntries->sum(function ($entry) use ($rateResolver) {
                return (float) $entry->working_hours * $rateResolver->getMachineRate($entry->machine_id, $entry->date);
            });

            $totalDiesel = (float) $dieselEntries->sum('diesel_liters');
            $dieselCost = $dieselEntries->sum(function ($entry) use ($rateResolver) {
                return (float) $entry->diesel_liters * $rateResolver->getDieselRate($entry->date);
            });

            $totalPayments = (float) $this->applySiteFilterByMachineAssignment(
                Payment::query(),
                $siteId,
                'payments.date'
            )
                ->where('machine_id', $machine->id)
                ->whereBetween('date', [$from, $to])
                ->sum('amount_received');

            $totalExpenses = (float) $this->applySiteFilterByMachineAssignment(
                Expense::query(),
                $siteId,
                'expenses.date'
            )
                ->where('machine_id', $machine->id)
                ->whereBetween('date', [$from, $to])
                ->sum('amount');

            return [
                'machine' => $machine->name,
                'total_hours' => $totalHours,
                'gross_amount' => $grossAmount,
                'total_diesel_liters' => $totalDiesel,
                'diesel_cost' => $dieselCost,
                'total_payments' => $totalPayments,
                'total_expenses' => $totalExpenses,
                'net_balance' => $grossAmount - $dieselCost - $totalPayments - $totalExpenses,
            ];
        });

        return view('reports.complete-hisab', [
            'rows' => $rows,
            'machines' => $this->machineOptionsForRange($siteId, $from, $to),
            'sites' => $this->activeSiteOptions(),
            'from' => $from,
            'to' => $to,
        ]);
    }

    public function machineLedger(Request $request, RateResolverService $rateResolver): View
    {
        [$from, $to] = $this->dateRange($request);
        $siteId = $this->selectedSiteId($request);
        $machines = $this->machineOptionsForRange($siteId, $from, $to);
        $machineId = (int) ($request->machine_id ?: ($machines->first()->id ?? 0));

        $rows = collect();
        if ($machineId > 0) {
            $hours = $this->applySiteFilterByMachineAssignment(
                MachineHourEntry::query(),
                $siteId,
                'machine_hour_entries.date'
            )->where('machine_id', $machineId)->whereBetween('date', [$from, $to])->get();
            $diesel = $this->applySiteFilterByMachineAssignment(
                DieselUsageEntry::query(),
                $siteId,
                'diesel_usage_entries.date'
            )->where('machine_id', $machineId)->whereBetween('date', [$from, $to])->get();
            $payments = $this->applySiteFilterByMachineAssignment(
                Payment::query(),
                $siteId,
                'payments.date'
            )->where('machine_id', $machineId)->whereBetween('date', [$from, $to])->get();
            $expenses = $this->applySiteFilterByMachineAssignment(
                Expense::query(),
                $siteId,
                'expenses.date'
            )->where('machine_id', $machineId)->whereBetween('date', [$from, $to])->get();

            foreach ($hours as $entry) {
                $amount = (float) $entry->working_hours * $rateResolver->getMachineRate($entry->machine_id, $entry->date);
                $rows->push([
                    'date' => $entry->date->format('Y-m-d'),
                    'description' => 'Working hours income',
                    'debit' => 0,
                    'credit' => $amount,
                ]);
            }

            foreach ($diesel as $entry) {
                $amount = (float) $entry->diesel_liters * $rateResolver->getDieselRate($entry->date);
                $rows->push([
                    'date' => $entry->date->format('Y-m-d'),
                    'description' => 'Diesel cost',
                    'debit' => $amount,
                    'credit' => 0,
                ]);
            }

            foreach ($payments as $entry) {
                $rows->push([
                    'date' => $entry->date->format('Y-m-d'),
                    'description' => 'Payment received',
                    'debit' => (float) $entry->amount_received,
                    'credit' => 0,
                ]);
            }

            foreach ($expenses as $entry) {
                $rows->push([
                    'date' => $entry->date->format('Y-m-d'),
                    'description' => 'Expense: '.$entry->expense_type,
                    'debit' => (float) $entry->amount,
                    'credit' => 0,
                ]);
            }

            $rows = $rows->sortBy('date')->values();

            $running = 0;
            $rows = $rows->map(function ($row) use (&$running) {
                $running += ((float) $row['credit'] - (float) $row['debit']);
                $row['running_balance'] = $running;

                return $row;
            });
        }

        $sites = $this->activeSiteOptions();

        return view('reports.machine-ledger', compact('rows', 'machines', 'machineId', 'from', 'to', 'sites'));
    }

    public function dailySummary(Request $request, RateResolverService $rateResolver): View
    {
        [$from, $to] = $this->dateRange($request);
        $period = Carbon::parse($from);
        $end = Carbon::parse($to);
        $rows = collect();

        while ($period->lte($end)) {
            $date = $period->toDateString();
            $hourEntries = MachineHourEntry::query()->whereDate('date', $date)->get();
            $dieselEntries = DieselUsageEntry::query()->whereDate('date', $date)->get();
            $payments = (float) Payment::query()->whereDate('date', $date)->sum('amount_received');
            $expenses = (float) Expense::query()->whereDate('date', $date)->sum('amount');

            $earning = $hourEntries->sum(fn ($entry) => (float) $entry->working_hours * $rateResolver->getMachineRate($entry->machine_id, $entry->date));
            $dieselCost = $dieselEntries->sum(fn ($entry) => (float) $entry->diesel_liters * $rateResolver->getDieselRate($entry->date));

            $rows->push([
                'date' => $date,
                'total_hours' => (float) $hourEntries->sum('working_hours'),
                'total_diesel' => (float) $dieselEntries->sum('diesel_liters'),
                'total_earning' => $earning,
                'diesel_cost' => $dieselCost,
                'net' => $earning - $dieselCost - $payments - $expenses,
            ]);

            $period->addDay();
        }

        return view('reports.daily-summary', compact('rows', 'from', 'to'));
    }

    public function monthlySummary(Request $request, RateResolverService $rateResolver): View
    {
        [$from, $to] = $this->dateRange($request);
        $hourEntries = MachineHourEntry::query()->whereBetween('date', [$from, $to])->get();
        $dieselEntries = DieselUsageEntry::query()->whereBetween('date', [$from, $to])->get();

        $months = collect();

        foreach ($hourEntries as $entry) {
            $month = Carbon::parse($entry->date)->format('Y-m');
            $row = $months->get($month, [
                'month' => $month,
                'total_hours' => 0,
                'total_diesel' => 0,
                'total_earning' => 0,
                'cost' => 0,
                'payments' => 0,
                'expenses' => 0,
            ]);

            $row['total_hours'] += (float) $entry->working_hours;
            $row['total_earning'] += (float) $entry->working_hours * $rateResolver->getMachineRate($entry->machine_id, $entry->date);
            $months->put($month, $row);
        }

        foreach ($dieselEntries as $entry) {
            $month = Carbon::parse($entry->date)->format('Y-m');
            $row = $months->get($month, [
                'month' => $month,
                'total_hours' => 0,
                'total_diesel' => 0,
                'total_earning' => 0,
                'cost' => 0,
                'payments' => 0,
                'expenses' => 0,
            ]);

            $row['total_diesel'] += (float) $entry->diesel_liters;
            $row['cost'] += (float) $entry->diesel_liters * $rateResolver->getDieselRate($entry->date);
            $months->put($month, $row);
        }

        $paymentRows = Payment::query()->whereBetween('date', [$from, $to])->get();
        foreach ($paymentRows as $entry) {
            $month = Carbon::parse($entry->date)->format('Y-m');
            $row = $months->get($month, [
                'month' => $month,
                'total_hours' => 0,
                'total_diesel' => 0,
                'total_earning' => 0,
                'cost' => 0,
                'payments' => 0,
                'expenses' => 0,
            ]);
            $row['payments'] += (float) $entry->amount_received;
            $months->put($month, $row);
        }

        $expenseRows = Expense::query()->whereBetween('date', [$from, $to])->get();
        foreach ($expenseRows as $entry) {
            $month = Carbon::parse($entry->date)->format('Y-m');
            $row = $months->get($month, [
                'month' => $month,
                'total_hours' => 0,
                'total_diesel' => 0,
                'total_earning' => 0,
                'cost' => 0,
                'payments' => 0,
                'expenses' => 0,
            ]);
            $row['expenses'] += (float) $entry->amount;
            $months->put($month, $row);
        }

        $rows = $months->values()->sortBy('month')->map(function ($row) {
            $row['balance'] = $row['total_earning'] - $row['cost'] - $row['payments'] - $row['expenses'];

            return $row;
        })->values();

        return view('reports.monthly-summary', compact('rows', 'from', 'to'));
    }

    public function inventoryStock(Request $request): View
    {
        $parts = SparePart::query()
            ->when($request->boolean('low_stock_only'), fn ($query) => $query->whereColumn('current_stock', '<=', 'minimum_stock'))
            ->orderBy('name')
            ->get();

        $rows = $parts->map(fn ($part) => [
            'part' => $part->name,
            'part_number' => $part->part_number,
            'category' => $part->category,
            'current_stock' => (float) $part->current_stock,
            'minimum_stock' => (float) $part->minimum_stock,
            'unit' => $part->unit,
            'location' => $part->location,
            'low_stock' => (float) $part->current_stock <= (float) $part->minimum_stock,
        ]);

        return view('reports.inventory-stock', [
            'rows' => $rows,
            'lowStockOnly' => $request->boolean('low_stock_only'),
        ]);
    }

    public function partUsage(Request $request): View
    {
        [$from, $to] = $this->dateRange($request);

        $entries = MachinePartUsage::query()
            ->with(['machine', 'sparePart'])
            ->whereBetween('date', [$from, $to])
            ->when($request->filled('machine_id'), fn ($query) => $query->where('machine_id', $request->machine_id))
            ->when($request->filled('spare_part_id'), fn ($query) => $query->where('spare_part_id', $request->spare_part_id))
            ->when($request->filled('usage_type'), fn ($query) => $query->where('usage_type', $request->usage_type))
            ->orderBy('date')
            ->get();

        $rows = $entries->map(fn ($entry) => [
            'date' => $entry->date->format('Y-m-d'),
            'machine' => $entry->machine?->name,
            'part' => $entry->sparePart?->name,
            'usage_type' => $entry->usage_type,
            'quantity' => (float) $entry->quantity,
            'reference' => $entry->reference,
            'remarks' => $entry->remarks,
        ]);

        return view('reports.part-usage', [
            'rows' => $rows,
            'machines' => Machine::query()->orderBy('name')->get(),
            'parts' => SparePart::query()->orderBy('name')->get(),
            'from' => $from,
            'to' => $to,
        ]);
    }

    public function machineParts(Request $request): View
    {
        [$from, $to] = $this->dateRange($request);

        $entries = MachinePartUsage::query()
            ->with(['machine', 'sparePart'])
            ->whereBetween('date', [$from, $to])
            ->when($request->filled('machine_id'), fn ($query) => $query->where('machine_id', $request->machine_id))
            ->when($request->filled('spare_part_id'), fn ($query) => $query->where('spare_part_id', $request->spare_part_id))
            ->get()
            ->groupBy(fn ($entry) => $entry->machine?->name.'|'.$entry->sparePart?->name)
            ->map(function ($group) {
                $first = $group->first();

                return [
                    'machine' => $first->machine?->name,
                    'part' => $first->sparePart?->name,
                    'usage_count' => $group->count(),
                    'total_quantity' => (float) $group->sum('quantity'),
                ];
            })
            ->values()
            ->sortBy(['machine', 'part'])
            ->values();

        return view('reports.machine-parts', [
            'rows' => $entries,
            'machines' => Machine::query()->orderBy('name')->get(),
            'parts' => SparePart::query()->orderBy('name')->get(),
            'from' => $from,
            'to' => $to,
        ]);
    }

    public function fuelStock(Request $request): View
    {
        $stocks = FuelStock::query()
            ->when($request->boolean('low_stock_only'), fn ($query) => $query->whereColumn('current_stock', '<=', 'minimum_stock'))
            ->orderBy('name')
            ->get();

        $rows = $stocks->map(fn ($stock) => [
            'stock' => $stock->name,
            'code' => $stock->code,
            'current_stock' => (float) $stock->current_stock,
            'minimum_stock' => (float) $stock->minimum_stock,
            'unit' => $stock->unit,
            'location' => $stock->location,
            'low_stock' => (float) $stock->current_stock <= (float) $stock->minimum_stock,
        ]);

        return view('reports.fuel-stock', [
            'rows' => $rows,
            'lowStockOnly' => $request->boolean('low_stock_only'),
        ]);
    }

    public function fuelIssues(Request $request): View
    {
        [$from, $to] = $this->dateRange($request);

        $entries = FuelIssue::query()
            ->with(['fuelStock', 'machine'])
            ->whereBetween('date', [$from, $to])
            ->when($request->filled('fuel_stock_id'), fn ($query) => $query->where('fuel_stock_id', $request->fuel_stock_id))
            ->when($request->filled('consumer_type'), fn ($query) => $query->where('consumer_type', $request->consumer_type))
            ->when($request->filled('machine_id'), fn ($query) => $query->where('machine_id', $request->machine_id))
            ->orderBy('date')
            ->get();

        $rows = $entries->map(fn ($entry) => [
            'date' => $entry->date->format('Y-m-d'),
            'stock' => $entry->fuelStock?->name,
            'consumer_type' => $entry->consumer_type,
            'consumer' => $entry->machine?->name ?: $entry->consumer_name,
            'quantity' => (float) $entry->quantity,
            'reference' => $entry->reference,
            'remarks' => $entry->remarks,
        ]);

        return view('reports.fuel-issues', [
            'rows' => $rows,
            'stocks' => FuelStock::query()->orderBy('name')->get(),
            'machines' => Machine::query()->orderBy('name')->get(),
            'from' => $from,
            'to' => $to,
        ]);
    }

    public function fuelConsumption(Request $request): View
    {
        [$from, $to] = $this->dateRange($request);

        $rows = FuelIssue::query()
            ->with('machine')
            ->whereBetween('date', [$from, $to])
            ->when($request->filled('consumer_type'), fn ($query) => $query->where('consumer_type', $request->consumer_type))
            ->when($request->filled('machine_id'), fn ($query) => $query->where('machine_id', $request->machine_id))
            ->get()
            ->groupBy(function ($issue) {
                if ($issue->consumer_type === 'machine') {
                    return 'machine|'.($issue->machine?->name ?: 'Unknown Machine');
                }

                return $issue->consumer_type.'|'.($issue->consumer_name ?: 'Unknown');
            })
            ->map(function ($group, $key) {
                [$consumerType, $consumer] = explode('|', $key, 2);

                return [
                    'consumer_type' => $consumerType,
                    'consumer' => $consumer,
                    'issues_count' => $group->count(),
                    'total_quantity' => (float) $group->sum('quantity'),
                ];
            })
            ->values()
            ->sortBy(['consumer_type', 'consumer'])
            ->values();

        return view('reports.fuel-consumption', [
            'rows' => $rows,
            'machines' => Machine::query()->orderBy('name')->get(),
            'from' => $from,
            'to' => $to,
        ]);
    }

    public function export(Request $request, string $report, string $format, RateResolverService $rateResolver): Response|BinaryFileResponse
    {
        $data = match ($report) {
            'machine-hours' => $this->machineHoursData($request, $rateResolver),
            'diesel-usage' => $this->dieselUsageData($request, $rateResolver),
            'complete-hisab' => $this->completeHisabData($request, $rateResolver),
            'machine-ledger' => $this->machineLedgerData($request, $rateResolver),
            'daily-summary' => $this->dailySummaryData($request, $rateResolver),
            'monthly-summary' => $this->monthlySummaryData($request, $rateResolver),
            'inventory-stock' => $this->inventoryStockData($request),
            'part-usage' => $this->partUsageData($request),
            'machine-parts' => $this->machinePartsData($request),
            'fuel-stock' => $this->fuelStockData($request),
            'fuel-issues' => $this->fuelIssuesData($request),
            'fuel-consumption' => $this->fuelConsumptionData($request),
            default => collect(),
        };

        if ($format === 'excel') {
            return Excel::download(new ArrayExport($data), $report.'-report.xlsx');
        }

        $pdf = Pdf::loadView('reports.pdf-table', [
            'title' => ucwords(str_replace('-', ' ', $report)).' Report',
            'rows' => $data,
        ]);

        return $pdf->download($report.'-report.pdf');
    }

    private function dateRange(Request $request): array
    {
        $from = $request->input('from', Carbon::now()->startOfMonth()->toDateString());
        $to = $request->input('to', Carbon::now()->toDateString());

        return [$from, $to];
    }

    private function selectedSiteId(Request $request): ?int
    {
        return $request->filled('site_id') ? (int) $request->site_id : null;
    }

    private function machineOptionsForRange(?int $siteId, string $from, string $to): Collection
    {
        return $this->applySiteOverlapToMachineQuery(Machine::query(), $siteId, $from, $to)
            ->orderBy('name')
            ->get();
    }

    private function activeSiteOptions(): Collection
    {
        return Site::query()
            ->where('status', 'active')
            ->orderBy('name')
            ->get();
    }

    private function applySiteOverlapToMachineQuery($query, ?int $siteId, string $from, string $to)
    {
        return $query->when($siteId, function ($machineQuery) use ($siteId, $from, $to) {
            $machineQuery->whereHas('siteAssignments', function ($assignmentQuery) use ($siteId, $from, $to) {
                $assignmentQuery->where('site_id', $siteId)
                    ->where('assigned_from', '<=', $to)
                    ->where(function ($inner) use ($from) {
                        $inner->whereNull('assigned_to')
                            ->orWhere('assigned_to', '>=', $from);
                    });
            });
        });
    }

    private function applySiteFilterByMachineAssignment($query, ?int $siteId, string $dateColumn)
    {
        return $query->when($siteId, function ($entryQuery) use ($siteId, $dateColumn) {
            $entryQuery->whereHas('machine.siteAssignments', function ($assignmentQuery) use ($siteId, $dateColumn) {
                $assignmentQuery->where('site_id', $siteId)
                    ->whereColumn('machine_site_assignments.assigned_from', '<=', $dateColumn)
                    ->where(function ($inner) use ($dateColumn) {
                        $inner->whereNull('machine_site_assignments.assigned_to')
                            ->orWhereColumn('machine_site_assignments.assigned_to', '>=', $dateColumn);
                    });
            });
        });
    }

    private function machineHoursData(Request $request, RateResolverService $rateResolver): Collection
    {
        [$from, $to] = $this->dateRange($request);
        $siteId = $this->selectedSiteId($request);

        return $this->applySiteFilterByMachineAssignment(
            MachineHourEntry::query(),
            $siteId,
            'machine_hour_entries.date'
        )
            ->with('machine')
            ->whereBetween('date', [$from, $to])
            ->when($request->filled('machine_id'), fn ($query) => $query->where('machine_id', $request->machine_id))
            ->orderBy('date')
            ->get()
            ->map(fn ($entry) => [
                'date' => $entry->date->format('Y-m-d'),
                'machine' => $entry->machine?->name,
                'hours' => (float) $entry->working_hours,
                'applied_rate' => $rateResolver->getMachineRate($entry->machine_id, $entry->date),
                'earning' => (float) $entry->working_hours * $rateResolver->getMachineRate($entry->machine_id, $entry->date),
            ]);
    }

    private function dieselUsageData(Request $request, RateResolverService $rateResolver): Collection
    {
        [$from, $to] = $this->dateRange($request);
        $siteId = $this->selectedSiteId($request);

        return $this->applySiteFilterByMachineAssignment(
            DieselUsageEntry::query(),
            $siteId,
            'diesel_usage_entries.date'
        )
            ->with('machine')
            ->whereBetween('date', [$from, $to])
            ->when($request->filled('machine_id'), fn ($query) => $query->where('machine_id', $request->machine_id))
            ->orderBy('date')
            ->get()
            ->map(fn ($entry) => [
                'date' => $entry->date->format('Y-m-d'),
                'machine' => $entry->machine?->name,
                'liters' => (float) $entry->diesel_liters,
                'diesel_rate' => $rateResolver->getDieselRate($entry->date),
                'cost' => (float) $entry->diesel_liters * $rateResolver->getDieselRate($entry->date),
            ]);
    }

    private function completeHisabData(Request $request, RateResolverService $rateResolver): Collection
    {
        [$from, $to] = $this->dateRange($request);
        $siteId = $this->selectedSiteId($request);

        $machines = $this->applySiteOverlapToMachineQuery(Machine::query(), $siteId, $from, $to)
            ->when($request->filled('machine_id'), fn ($query) => $query->where('id', $request->machine_id))
            ->orderBy('name')
            ->get();

        return $machines->map(function ($machine) use ($from, $to, $rateResolver, $siteId) {
            $hoursEntries = $this->applySiteFilterByMachineAssignment(
                MachineHourEntry::query(),
                $siteId,
                'machine_hour_entries.date'
            )->where('machine_id', $machine->id)->whereBetween('date', [$from, $to])->get();
            $dieselEntries = $this->applySiteFilterByMachineAssignment(
                DieselUsageEntry::query(),
                $siteId,
                'diesel_usage_entries.date'
            )->where('machine_id', $machine->id)->whereBetween('date', [$from, $to])->get();
            $grossAmount = $hoursEntries->sum(fn ($entry) => (float) $entry->working_hours * $rateResolver->getMachineRate($entry->machine_id, $entry->date));
            $dieselCost = $dieselEntries->sum(fn ($entry) => (float) $entry->diesel_liters * $rateResolver->getDieselRate($entry->date));
            $payments = (float) $this->applySiteFilterByMachineAssignment(
                Payment::query(),
                $siteId,
                'payments.date'
            )->where('machine_id', $machine->id)->whereBetween('date', [$from, $to])->sum('amount_received');
            $expenses = (float) $this->applySiteFilterByMachineAssignment(
                Expense::query(),
                $siteId,
                'expenses.date'
            )->where('machine_id', $machine->id)->whereBetween('date', [$from, $to])->sum('amount');

            return [
                'machine' => $machine->name,
                'total_hours' => (float) $hoursEntries->sum('working_hours'),
                'gross_amount' => $grossAmount,
                'total_diesel_liters' => (float) $dieselEntries->sum('diesel_liters'),
                'diesel_cost' => $dieselCost,
                'total_payments' => $payments,
                'total_expenses' => $expenses,
                'net_balance' => $grossAmount - $dieselCost - $payments - $expenses,
            ];
        });
    }

    private function machineLedgerData(Request $request, RateResolverService $rateResolver): Collection
    {
        [$from, $to] = $this->dateRange($request);
        $siteId = $this->selectedSiteId($request);
        $machineId = (int) $request->machine_id;
        if ($machineId <= 0) {
            return collect();
        }

        $rows = collect();
        $hours = $this->applySiteFilterByMachineAssignment(
            MachineHourEntry::query(),
            $siteId,
            'machine_hour_entries.date'
        )->where('machine_id', $machineId)->whereBetween('date', [$from, $to])->get();
        foreach ($hours as $entry) {
            $rows->push([
                'date' => $entry->date->format('Y-m-d'),
                'description' => 'Working hours income',
                'debit' => 0,
                'credit' => (float) $entry->working_hours * $rateResolver->getMachineRate($entry->machine_id, $entry->date),
            ]);
        }
        $diesel = $this->applySiteFilterByMachineAssignment(
            DieselUsageEntry::query(),
            $siteId,
            'diesel_usage_entries.date'
        )->where('machine_id', $machineId)->whereBetween('date', [$from, $to])->get();
        foreach ($diesel as $entry) {
            $rows->push([
                'date' => $entry->date->format('Y-m-d'),
                'description' => 'Diesel cost',
                'debit' => (float) $entry->diesel_liters * $rateResolver->getDieselRate($entry->date),
                'credit' => 0,
            ]);
        }
        $payments = $this->applySiteFilterByMachineAssignment(
            Payment::query(),
            $siteId,
            'payments.date'
        )->where('machine_id', $machineId)->whereBetween('date', [$from, $to])->get();
        foreach ($payments as $entry) {
            $rows->push([
                'date' => $entry->date->format('Y-m-d'),
                'description' => 'Payment received',
                'debit' => (float) $entry->amount_received,
                'credit' => 0,
            ]);
        }
        $expenses = $this->applySiteFilterByMachineAssignment(
            Expense::query(),
            $siteId,
            'expenses.date'
        )->where('machine_id', $machineId)->whereBetween('date', [$from, $to])->get();
        foreach ($expenses as $entry) {
            $rows->push([
                'date' => $entry->date->format('Y-m-d'),
                'description' => 'Expense: '.$entry->expense_type,
                'debit' => (float) $entry->amount,
                'credit' => 0,
            ]);
        }

        $rows = $rows->sortBy('date')->values();
        $running = 0;

        return $rows->map(function ($row) use (&$running) {
            $running += (float) $row['credit'] - (float) $row['debit'];
            $row['running_balance'] = $running;

            return $row;
        });
    }

    private function dailySummaryData(Request $request, RateResolverService $rateResolver): Collection
    {
        [$from, $to] = $this->dateRange($request);
        $period = Carbon::parse($from);
        $end = Carbon::parse($to);
        $rows = collect();

        while ($period->lte($end)) {
            $date = $period->toDateString();
            $hourEntries = MachineHourEntry::query()->whereDate('date', $date)->get();
            $dieselEntries = DieselUsageEntry::query()->whereDate('date', $date)->get();
            $payments = (float) Payment::query()->whereDate('date', $date)->sum('amount_received');
            $expenses = (float) Expense::query()->whereDate('date', $date)->sum('amount');
            $earning = $hourEntries->sum(fn ($entry) => (float) $entry->working_hours * $rateResolver->getMachineRate($entry->machine_id, $entry->date));
            $dieselCost = $dieselEntries->sum(fn ($entry) => (float) $entry->diesel_liters * $rateResolver->getDieselRate($entry->date));

            $rows->push([
                'date' => $date,
                'total_hours' => (float) $hourEntries->sum('working_hours'),
                'total_diesel' => (float) $dieselEntries->sum('diesel_liters'),
                'total_earning' => $earning,
                'diesel_cost' => $dieselCost,
                'net' => $earning - $dieselCost - $payments - $expenses,
            ]);

            $period->addDay();
        }

        return $rows;
    }

    private function monthlySummaryData(Request $request, RateResolverService $rateResolver): Collection
    {
        [$from, $to] = $this->dateRange($request);
        $rows = collect();

        foreach ($this->dailySummaryData($request, $rateResolver) as $dailyRow) {
            $month = Carbon::parse($dailyRow['date'])->format('Y-m');
            $row = $rows->get($month, [
                'month' => $month,
                'total_hours' => 0,
                'total_diesel' => 0,
                'total_earning' => 0,
                'cost' => 0,
                'balance' => 0,
            ]);

            $row['total_hours'] += $dailyRow['total_hours'];
            $row['total_diesel'] += $dailyRow['total_diesel'];
            $row['total_earning'] += $dailyRow['total_earning'];
            $row['cost'] += $dailyRow['diesel_cost'];
            $row['balance'] += $dailyRow['net'];
            $rows->put($month, $row);
        }

        return $rows->values()->sortBy('month')->values();
    }

    private function inventoryStockData(Request $request): Collection
    {
        return SparePart::query()
            ->when($request->boolean('low_stock_only'), fn ($query) => $query->whereColumn('current_stock', '<=', 'minimum_stock'))
            ->orderBy('name')
            ->get()
            ->map(fn ($part) => [
                'part' => $part->name,
                'part_number' => $part->part_number,
                'category' => $part->category,
                'current_stock' => (float) $part->current_stock,
                'minimum_stock' => (float) $part->minimum_stock,
                'unit' => $part->unit,
                'location' => $part->location,
            ]);
    }

    private function partUsageData(Request $request): Collection
    {
        [$from, $to] = $this->dateRange($request);

        return MachinePartUsage::query()
            ->with(['machine', 'sparePart'])
            ->whereBetween('date', [$from, $to])
            ->when($request->filled('machine_id'), fn ($query) => $query->where('machine_id', $request->machine_id))
            ->when($request->filled('spare_part_id'), fn ($query) => $query->where('spare_part_id', $request->spare_part_id))
            ->when($request->filled('usage_type'), fn ($query) => $query->where('usage_type', $request->usage_type))
            ->orderBy('date')
            ->get()
            ->map(fn ($entry) => [
                'date' => $entry->date->format('Y-m-d'),
                'machine' => $entry->machine?->name,
                'part' => $entry->sparePart?->name,
                'usage_type' => $entry->usage_type,
                'quantity' => (float) $entry->quantity,
                'reference' => $entry->reference,
                'remarks' => $entry->remarks,
            ]);
    }

    private function machinePartsData(Request $request): Collection
    {
        [$from, $to] = $this->dateRange($request);

        return MachinePartUsage::query()
            ->with(['machine', 'sparePart'])
            ->whereBetween('date', [$from, $to])
            ->when($request->filled('machine_id'), fn ($query) => $query->where('machine_id', $request->machine_id))
            ->when($request->filled('spare_part_id'), fn ($query) => $query->where('spare_part_id', $request->spare_part_id))
            ->get()
            ->groupBy(fn ($entry) => $entry->machine?->name.'|'.$entry->sparePart?->name)
            ->map(function ($group) {
                $first = $group->first();

                return [
                    'machine' => $first->machine?->name,
                    'part' => $first->sparePart?->name,
                    'usage_count' => $group->count(),
                    'total_quantity' => (float) $group->sum('quantity'),
                ];
            })
            ->values()
            ->sortBy(['machine', 'part'])
            ->values();
    }

    private function fuelStockData(Request $request): Collection
    {
        return FuelStock::query()
            ->when($request->boolean('low_stock_only'), fn ($query) => $query->whereColumn('current_stock', '<=', 'minimum_stock'))
            ->orderBy('name')
            ->get()
            ->map(fn ($stock) => [
                'stock' => $stock->name,
                'code' => $stock->code,
                'current_stock' => (float) $stock->current_stock,
                'minimum_stock' => (float) $stock->minimum_stock,
                'unit' => $stock->unit,
                'location' => $stock->location,
            ]);
    }

    private function fuelIssuesData(Request $request): Collection
    {
        [$from, $to] = $this->dateRange($request);

        return FuelIssue::query()
            ->with(['fuelStock', 'machine'])
            ->whereBetween('date', [$from, $to])
            ->when($request->filled('fuel_stock_id'), fn ($query) => $query->where('fuel_stock_id', $request->fuel_stock_id))
            ->when($request->filled('consumer_type'), fn ($query) => $query->where('consumer_type', $request->consumer_type))
            ->when($request->filled('machine_id'), fn ($query) => $query->where('machine_id', $request->machine_id))
            ->orderBy('date')
            ->get()
            ->map(fn ($entry) => [
                'date' => $entry->date->format('Y-m-d'),
                'stock' => $entry->fuelStock?->name,
                'consumer_type' => $entry->consumer_type,
                'consumer' => $entry->machine?->name ?: $entry->consumer_name,
                'quantity' => (float) $entry->quantity,
                'reference' => $entry->reference,
                'remarks' => $entry->remarks,
            ]);
    }

    private function fuelConsumptionData(Request $request): Collection
    {
        [$from, $to] = $this->dateRange($request);

        return FuelIssue::query()
            ->with('machine')
            ->whereBetween('date', [$from, $to])
            ->when($request->filled('consumer_type'), fn ($query) => $query->where('consumer_type', $request->consumer_type))
            ->when($request->filled('machine_id'), fn ($query) => $query->where('machine_id', $request->machine_id))
            ->get()
            ->groupBy(function ($issue) {
                if ($issue->consumer_type === 'machine') {
                    return 'machine|'.($issue->machine?->name ?: 'Unknown Machine');
                }

                return $issue->consumer_type.'|'.($issue->consumer_name ?: 'Unknown');
            })
            ->map(function ($group, $key) {
                [$consumerType, $consumer] = explode('|', $key, 2);

                return [
                    'consumer_type' => $consumerType,
                    'consumer' => $consumer,
                    'issues_count' => $group->count(),
                    'total_quantity' => (float) $group->sum('quantity'),
                ];
            })
            ->values()
            ->sortBy(['consumer_type', 'consumer'])
            ->values();
    }
}
