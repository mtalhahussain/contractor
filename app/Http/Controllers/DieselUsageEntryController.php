<?php

namespace App\Http\Controllers;

use App\Models\DieselUsageEntry;
use App\Models\Machine;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DieselUsageEntryController extends Controller
{
    public function index(Request $request): View
    {
        $selectedMachineId = $request->integer('machine_id') ?: null;
        $selectedMonth = $request->input('month');

        $entries = DieselUsageEntry::query()
            ->with('machine')
            ->when($selectedMachineId, fn ($query) => $query->where('machine_id', $selectedMachineId))
            ->when(
                is_string($selectedMonth) && preg_match('/^\d{4}-\d{2}$/', $selectedMonth),
                function ($query) use ($selectedMonth) {
                    [$year, $month] = explode('-', $selectedMonth);

                    $query->whereYear('date', (int) $year)
                        ->whereMonth('date', (int) $month);
                }
            )
            ->latest('date')
            ->paginate(30)
            ->withQueryString();

        $machines = Machine::query()->orderBy('name')->get(['id', 'name']);

        return view('diesel-usage.index', compact('entries', 'machines', 'selectedMachineId', 'selectedMonth'));
    }

    public function create(): View
    {
        $machines = Machine::query()->orderBy('name')->get();

        return view('diesel-usage.create', compact('machines'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'date' => ['required', 'date'],
            'machine_id' => ['required', 'exists:machines,id'],
            'diesel_liters' => ['required', 'numeric', 'min:0'],
            'remarks' => ['nullable', 'string'],
        ]);

        $validated['created_by'] = $request->user()?->id;
        DieselUsageEntry::create($validated);

        return redirect()->route('diesel-usage.index')->with('success', 'Diesel usage saved.');
    }

    public function show(string $id): RedirectResponse
    {
        return redirect()->route('diesel-usage.index');
    }

    public function edit(DieselUsageEntry $diesel_usage): View
    {
        $machines = Machine::query()->orderBy('name')->get();

        return view('diesel-usage.edit', [
            'entry' => $diesel_usage,
            'machines' => $machines,
        ]);
    }

    public function update(Request $request, DieselUsageEntry $diesel_usage): RedirectResponse
    {
        $validated = $request->validate([
            'date' => ['required', 'date'],
            'machine_id' => ['required', 'exists:machines,id'],
            'diesel_liters' => ['required', 'numeric', 'min:0'],
            'remarks' => ['nullable', 'string'],
        ]);

        $diesel_usage->update($validated);

        return redirect()->route('diesel-usage.index')->with('success', 'Diesel usage updated.');
    }

    public function destroy(DieselUsageEntry $diesel_usage): RedirectResponse
    {
        $diesel_usage->delete();

        return redirect()->route('diesel-usage.index')->with('success', 'Diesel usage deleted.');
    }
}
