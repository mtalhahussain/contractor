<?php

namespace App\Http\Controllers;

use App\Models\Machine;
use App\Models\PartStockMovement;
use App\Models\SparePart;
use App\Services\InventoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PartStockMovementController extends Controller
{
    public function __construct(private readonly InventoryService $inventoryService)
    {
    }

    public function index(): View
    {
        $movements = PartStockMovement::query()
            ->with(['sparePart', 'machine', 'usage'])
            ->latest('date')
            ->latest('id')
            ->paginate(30);

        return view('part-stock-movements.index', compact('movements'));
    }

    public function create(): View
    {
        $selectedPartId = request()->integer('spare_part_id');

        return view('part-stock-movements.create', [
            'parts' => SparePart::query()->orderBy('name')->get(),
            'machines' => Machine::query()->orderBy('name')->get(),
            'selectedPartId' => $selectedPartId > 0 ? $selectedPartId : null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'date' => ['required', 'date'],
            'spare_part_id' => ['required', 'exists:spare_parts,id'],
            'machine_id' => ['nullable', 'exists:machines,id'],
            'movement_type' => ['required', 'in:stock_in,stock_out'],
            'quantity' => ['required', 'numeric', 'gt:0'],
            'reference' => ['nullable', 'string', 'max:255'],
            'remarks' => ['nullable', 'string'],
        ]);

        $validated['created_by'] = $request->user()?->id;
        $this->inventoryService->createMovement($validated);

        return redirect()->route('part-stock-movements.index')->with('success', 'Stock movement saved successfully.');
    }

    public function show(string $id): RedirectResponse
    {
        return redirect()->route('part-stock-movements.index');
    }

    public function edit(PartStockMovement $part_stock_movement): View|RedirectResponse
    {
        if ($part_stock_movement->machine_part_usage_id) {
            return redirect()->route('part-stock-movements.index')->with('error', 'Machine usage stock movement is managed from part usage screen.');
        }

        return view('part-stock-movements.edit', [
            'movement' => $part_stock_movement,
            'parts' => SparePart::query()->orderBy('name')->get(),
            'machines' => Machine::query()->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, PartStockMovement $part_stock_movement): RedirectResponse
    {
        if ($part_stock_movement->machine_part_usage_id) {
            return redirect()->route('part-stock-movements.index')->with('error', 'Machine usage stock movement is managed from part usage screen.');
        }

        $validated = $request->validate([
            'date' => ['required', 'date'],
            'spare_part_id' => ['required', 'exists:spare_parts,id'],
            'machine_id' => ['nullable', 'exists:machines,id'],
            'movement_type' => ['required', 'in:stock_in,stock_out'],
            'quantity' => ['required', 'numeric', 'gt:0'],
            'reference' => ['nullable', 'string', 'max:255'],
            'remarks' => ['nullable', 'string'],
        ]);

        $validated['created_by'] = $part_stock_movement->created_by;
        $this->inventoryService->updateMovement($part_stock_movement, $validated);

        return redirect()->route('part-stock-movements.index')->with('success', 'Stock movement updated successfully.');
    }

    public function destroy(PartStockMovement $part_stock_movement): RedirectResponse
    {
        if ($part_stock_movement->machine_part_usage_id) {
            return redirect()->route('part-stock-movements.index')->with('error', 'Machine usage stock movement is managed from part usage screen.');
        }

        $this->inventoryService->deleteMovement($part_stock_movement);

        return redirect()->route('part-stock-movements.index')->with('success', 'Stock movement deleted successfully.');
    }
}