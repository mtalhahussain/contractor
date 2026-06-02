<?php

namespace App\Http\Controllers;

use App\Models\FuelStock;
use App\Models\FuelStockMovement;
use App\Services\FuelInventoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FuelStockMovementController extends Controller
{
    public function __construct(private readonly FuelInventoryService $fuelInventoryService)
    {
    }

    public function index(): View
    {
        $movements = FuelStockMovement::query()
            ->with(['fuelStock', 'machine', 'issue'])
            ->latest('date')
            ->latest('id')
            ->paginate(30);

        return view('fuel-stock-movements.index', compact('movements'));
    }

    public function create(): View
    {
        $selectedStockId = request()->integer('fuel_stock_id');

        return view('fuel-stock-movements.create', [
            'stocks' => FuelStock::query()->orderBy('name')->get(),
            'selectedStockId' => $selectedStockId > 0 ? $selectedStockId : null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'date' => ['required', 'date'],
            'fuel_stock_id' => ['required', 'exists:fuel_stocks,id'],
            'movement_type' => ['required', 'in:stock_in,stock_out'],
            'quantity' => ['required', 'numeric', 'gt:0'],
            'reference' => ['nullable', 'string', 'max:255'],
            'remarks' => ['nullable', 'string'],
        ]);

        $validated['created_by'] = $request->user()?->id;
        $this->fuelInventoryService->createMovement($validated);

        return redirect()->route('fuel-stock-movements.index')->with('success', 'Fuel stock movement saved successfully.');
    }

    public function show(string $id): RedirectResponse
    {
        return redirect()->route('fuel-stock-movements.index');
    }

    public function edit(FuelStockMovement $fuel_stock_movement): View|RedirectResponse
    {
        if ($fuel_stock_movement->fuel_issue_id) {
            return redirect()->route('fuel-stock-movements.index')->with('error', 'Fuel issue movement is managed from fuel issue screen.');
        }

        return view('fuel-stock-movements.edit', [
            'movement' => $fuel_stock_movement,
            'stocks' => FuelStock::query()->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, FuelStockMovement $fuel_stock_movement): RedirectResponse
    {
        if ($fuel_stock_movement->fuel_issue_id) {
            return redirect()->route('fuel-stock-movements.index')->with('error', 'Fuel issue movement is managed from fuel issue screen.');
        }

        $validated = $request->validate([
            'date' => ['required', 'date'],
            'fuel_stock_id' => ['required', 'exists:fuel_stocks,id'],
            'movement_type' => ['required', 'in:stock_in,stock_out'],
            'quantity' => ['required', 'numeric', 'gt:0'],
            'reference' => ['nullable', 'string', 'max:255'],
            'remarks' => ['nullable', 'string'],
        ]);

        $validated['created_by'] = $fuel_stock_movement->created_by;
        $this->fuelInventoryService->updateMovement($fuel_stock_movement, $validated);

        return redirect()->route('fuel-stock-movements.index')->with('success', 'Fuel stock movement updated successfully.');
    }

    public function destroy(FuelStockMovement $fuel_stock_movement): RedirectResponse
    {
        if ($fuel_stock_movement->fuel_issue_id) {
            return redirect()->route('fuel-stock-movements.index')->with('error', 'Fuel issue movement is managed from fuel issue screen.');
        }

        $this->fuelInventoryService->deleteMovement($fuel_stock_movement);

        return redirect()->route('fuel-stock-movements.index')->with('success', 'Fuel stock movement deleted successfully.');
    }
}