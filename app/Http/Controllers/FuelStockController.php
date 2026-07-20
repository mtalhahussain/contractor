<?php

namespace App\Http\Controllers;

use App\Models\FuelStock;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class FuelStockController extends Controller
{
    private const FUEL_UNITS = [
        'liters',
        'gallons',
        'barrels',
    ];

    public function index(): View
    {
        $stocks = FuelStock::query()
            ->when(request()->filled('q'), function ($query) {
                $search = trim((string) request('q'));

                $query->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', '%'.$search.'%')
                        ->orWhere('code', 'like', '%'.$search.'%')
                        ->orWhere('location', 'like', '%'.$search.'%');
                });
            })
            ->latest()
            ->paginate(25)
            ->withQueryString();

        return view('fuel-stocks.index', compact('stocks'));
    }

    public function create(): View
    {
        return view('fuel-stocks.create', [
            'units' => self::FUEL_UNITS,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:255', 'unique:fuel_stocks,code'],
            'unit' => ['required', Rule::in(self::FUEL_UNITS)],
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
        return view('fuel-stocks.edit', [
            'stock' => $fuel_stock,
            'units' => self::FUEL_UNITS,
        ]);
    }

    public function update(Request $request, FuelStock $fuel_stock): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:255', Rule::unique('fuel_stocks', 'code')->ignore($fuel_stock->id)],
            'unit' => ['required', Rule::in(self::FUEL_UNITS)],
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