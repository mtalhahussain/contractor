<?php

namespace App\Http\Controllers;

use App\Exports\ArrayExport;
use App\Models\DieselUsageEntry;
use App\Models\Expense;
use App\Models\Machine;
use App\Models\MachineHourEntry;
use App\Models\Payment;
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

        $entries = MachineHourEntry::query()
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
            'machines' => Machine::query()->orderBy('name')->get(),
            'from' => $from,
            'to' => $to,
        ]);
    }

    public function dieselUsage(Request $request, RateResolverService $rateResolver): View
    {
        [$from, $to] = $this->dateRange($request);

        $entries = DieselUsageEntry::query()
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
            'machines' => Machine::query()->orderBy('name')->get(),
            'from' => $from,
            'to' => $to,
        ]);
    }

    public function completeHisab(Request $request, RateResolverService $rateResolver): View
    {
        [$from, $to] = $this->dateRange($request);

        $machines = Machine::query()
            ->when($request->filled('machine_id'), fn ($query) => $query->where('id', $request->machine_id))
            ->orderBy('name')
            ->get();

        $rows = $machines->map(function ($machine) use ($from, $to, $rateResolver) {
            $hoursEntries = MachineHourEntry::query()
                ->where('machine_id', $machine->id)
                ->whereBetween('date', [$from, $to])
                ->get();

            $dieselEntries = DieselUsageEntry::query()
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

            $totalPayments = (float) Payment::query()
                ->where('machine_id', $machine->id)
                ->whereBetween('date', [$from, $to])
                ->sum('amount_received');

            $totalExpenses = (float) Expense::query()
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
            'machines' => Machine::query()->orderBy('name')->get(),
            'from' => $from,
            'to' => $to,
        ]);
    }

    public function machineLedger(Request $request, RateResolverService $rateResolver): View
    {
        [$from, $to] = $this->dateRange($request);
        $machines = Machine::query()->orderBy('name')->get();
        $machineId = (int) ($request->machine_id ?: ($machines->first()->id ?? 0));

        $rows = collect();
        if ($machineId > 0) {
            $hours = MachineHourEntry::query()->where('machine_id', $machineId)->whereBetween('date', [$from, $to])->get();
            $diesel = DieselUsageEntry::query()->where('machine_id', $machineId)->whereBetween('date', [$from, $to])->get();
            $payments = Payment::query()->where('machine_id', $machineId)->whereBetween('date', [$from, $to])->get();
            $expenses = Expense::query()->where('machine_id', $machineId)->whereBetween('date', [$from, $to])->get();

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

        return view('reports.machine-ledger', compact('rows', 'machines', 'machineId', 'from', 'to'));
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

    public function export(Request $request, string $report, string $format, RateResolverService $rateResolver): Response|BinaryFileResponse
    {
        $data = match ($report) {
            'machine-hours' => $this->machineHoursData($request, $rateResolver),
            'diesel-usage' => $this->dieselUsageData($request, $rateResolver),
            'complete-hisab' => $this->completeHisabData($request, $rateResolver),
            'machine-ledger' => $this->machineLedgerData($request, $rateResolver),
            'daily-summary' => $this->dailySummaryData($request, $rateResolver),
            'monthly-summary' => $this->monthlySummaryData($request, $rateResolver),
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

    private function machineHoursData(Request $request, RateResolverService $rateResolver): Collection
    {
        [$from, $to] = $this->dateRange($request);
        return MachineHourEntry::query()
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
        return DieselUsageEntry::query()
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
        $machines = Machine::query()
            ->when($request->filled('machine_id'), fn ($query) => $query->where('id', $request->machine_id))
            ->orderBy('name')
            ->get();

        return $machines->map(function ($machine) use ($from, $to, $rateResolver) {
            $hoursEntries = MachineHourEntry::query()->where('machine_id', $machine->id)->whereBetween('date', [$from, $to])->get();
            $dieselEntries = DieselUsageEntry::query()->where('machine_id', $machine->id)->whereBetween('date', [$from, $to])->get();
            $grossAmount = $hoursEntries->sum(fn ($entry) => (float) $entry->working_hours * $rateResolver->getMachineRate($entry->machine_id, $entry->date));
            $dieselCost = $dieselEntries->sum(fn ($entry) => (float) $entry->diesel_liters * $rateResolver->getDieselRate($entry->date));
            $payments = (float) Payment::query()->where('machine_id', $machine->id)->whereBetween('date', [$from, $to])->sum('amount_received');
            $expenses = (float) Expense::query()->where('machine_id', $machine->id)->whereBetween('date', [$from, $to])->sum('amount');

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
        $machineId = (int) $request->machine_id;
        if ($machineId <= 0) {
            return collect();
        }

        $rows = collect();
        $hours = MachineHourEntry::query()->where('machine_id', $machineId)->whereBetween('date', [$from, $to])->get();
        foreach ($hours as $entry) {
            $rows->push([
                'date' => $entry->date->format('Y-m-d'),
                'description' => 'Working hours income',
                'debit' => 0,
                'credit' => (float) $entry->working_hours * $rateResolver->getMachineRate($entry->machine_id, $entry->date),
            ]);
        }
        $diesel = DieselUsageEntry::query()->where('machine_id', $machineId)->whereBetween('date', [$from, $to])->get();
        foreach ($diesel as $entry) {
            $rows->push([
                'date' => $entry->date->format('Y-m-d'),
                'description' => 'Diesel cost',
                'debit' => (float) $entry->diesel_liters * $rateResolver->getDieselRate($entry->date),
                'credit' => 0,
            ]);
        }
        $payments = Payment::query()->where('machine_id', $machineId)->whereBetween('date', [$from, $to])->get();
        foreach ($payments as $entry) {
            $rows->push([
                'date' => $entry->date->format('Y-m-d'),
                'description' => 'Payment received',
                'debit' => (float) $entry->amount_received,
                'credit' => 0,
            ]);
        }
        $expenses = Expense::query()->where('machine_id', $machineId)->whereBetween('date', [$from, $to])->get();
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
}
