<?php

namespace App\Http\Controllers;

use App\Models\FuelStock;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class FuelStockController extends Controller
{
    public function index(): View
    {
        $stocks = FuelStock::query()->latest()->paginate(25);

        return view('fuel-stocks.index', compact('stocks'));
    }

    public function create(): View
    {
        return view('fuel-stocks.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:255', 'unique:fuel_stocks,code'],
            'unit' => ['required', 'string', 'max:20'],
            'current_stock' => ['required', 'numeric', 'min:0'],
            'minimum_stock' => ['required', 'numeric', 'min:0'],
            'location' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        $validated['created_by'] = $request->user()?->id;
        FuelStock::create($validated);

        return redirect()->route('fuel-stocks.index')->with('success', 'Fuel stock created successfully.');
    }

    public function show(string $id): RedirectResponse
    {
        return redirect()->route('fuel-stocks.index');
    }

    public function edit(FuelStock $fuel_stock): View
    {
        return view('fuel-stocks.edit', ['stock' => $fuel_stock]);
    }

    public function update(Request $request, FuelStock $fuel_stock): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:255', Rule::unique('fuel_stocks', 'code')->ignore($fuel_stock->id)],
            'unit' => ['required', 'string', 'max:20'],
            'minimum_stock' => ['required', 'numeric', 'min:0'],
            'location' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        $fuel_stock->update($validated);

        return redirect()->route('fuel-stocks.index')->with('success', 'Fuel stock updated successfully.');
    }

    public function destroy(FuelStock $fuel_stock): RedirectResponse
    {
        $fuel_stock->delete();

        return redirect()->route('fuel-stocks.index')->with('success', 'Fuel stock deleted successfully.');
    }
}