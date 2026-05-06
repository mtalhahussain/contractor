<?php

namespace App\Http\Controllers;

use App\Models\DieselRateHistory;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DieselRateHistoryController extends Controller
{
    public function index(): View
    {
        $rates = DieselRateHistory::query()->latest('effective_from_date')->paginate(30);

        return view('diesel-rates.index', compact('rates'));
    }

    public function create(): View
    {
        return view('diesel-rates.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'rate_per_liter' => ['required', 'numeric', 'min:0'],
            'effective_from_date' => ['required', 'date'],
        ]);

        $validated['created_by'] = $request->user()?->id;
        DieselRateHistory::create($validated);

        return redirect()->route('diesel-rates.index')->with('success', 'Diesel rate history saved.');
    }

    public function show(string $id): RedirectResponse
    {
        return redirect()->route('diesel-rates.index');
    }

    public function edit(string $id): RedirectResponse
    {
        return redirect()->route('diesel-rates.index');
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        return redirect()->route('diesel-rates.index');
    }

    public function destroy(DieselRateHistory $diesel_rate): RedirectResponse
    {
        $diesel_rate->delete();

        return redirect()->route('diesel-rates.index')->with('success', 'Diesel rate record deleted.');
    }
}
