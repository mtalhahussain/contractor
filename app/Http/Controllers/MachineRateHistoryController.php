<?php

namespace App\Http\Controllers;

use App\Models\Machine;
use App\Models\MachineRateHistory;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class MachineRateHistoryController extends Controller
{
    public function index(): View
    {
        $rates = MachineRateHistory::query()->with('machine')->latest('effective_from_date')->paginate(30);

        return view('machine-rates.index', compact('rates'));
    }

    public function create(): View
    {
        $machines = Machine::query()->orderBy('name')->get();

        return view('machine-rates.create', compact('machines'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'machine_id' => ['required', 'exists:machines,id'],
            'hourly_rate' => ['required', 'numeric', 'min:0'],
            'effective_from_date' => ['required', 'date'],
        ]);

        $validated['created_by'] = $request->user()?->id;
        MachineRateHistory::create($validated);

        return redirect()->route('machine-rates.index')->with('success', 'Machine rate history saved.');
    }

    public function show(string $id): RedirectResponse
    {
        return redirect()->route('machine-rates.index');
    }

    public function edit(string $id): RedirectResponse
    {
        return redirect()->route('machine-rates.index');
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        return redirect()->route('machine-rates.index');
    }

    public function destroy(MachineRateHistory $machine_rate): RedirectResponse
    {
        $machine_rate->delete();

        return redirect()->route('machine-rates.index')->with('success', 'Machine rate record deleted.');
    }
}
