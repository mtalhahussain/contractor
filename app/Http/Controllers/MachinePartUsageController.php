<?php

namespace App\Http\Controllers;

use App\Models\Machine;
use App\Models\MachinePartUsage;
use App\Models\SparePart;
use App\Services\InventoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MachinePartUsageController extends Controller
{
    public function __construct(private readonly InventoryService $inventoryService)
    {
    }

    public function index(Request $request): View
    {
        $machines = Machine::query()->orderBy('name')->get(['id', 'name']);
        $entries = MachinePartUsage::query()
            ->with(['machine', 'sparePart'])
            ->when($request->filled('machine_id'), fn ($q) => $q->where('machine_id', $request->machine_id))
            ->when($request->filled('month') && preg_match('/^\d{4}-\d{2}$/', (string) $request->month), function ($q) use ($request) {
                [$y, $m] = explode('-', $request->month);
                $q->whereYear('date', $y)->whereMonth('date', $m);
            })
            ->latest('date')->latest('id')->paginate(30)->withQueryString();

        return view('machine-part-usages.index', compact('entries', 'machines'));
    }

    public function create(): View
    {
        return view('machine-part-usages.create', [
            'machines' => Machine::query()->orderBy('name')->get(),
            'parts' => SparePart::query()->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'date' => ['required', 'date'],
            'machine_id' => ['required', 'exists:machines,id'],
            'spare_part_id' => ['required', 'exists:spare_parts,id'],
            'quantity' => ['required', 'numeric', 'gt:0'],
            'usage_type' => ['required', 'in:maintenance,repair,replacement,other'],
            'reference' => ['nullable', 'string', 'max:255'],
            'remarks' => ['nullable', 'string'],
        ]);

        $validated['created_by'] = $request->user()?->id;
        $this->inventoryService->createUsage($validated);

        return redirect()->route('machine-part-usages.index')->with('success', 'Part usage saved successfully.');
    }

    public function show(string $id): RedirectResponse
    {
        return redirect()->route('machine-part-usages.index');
    }

    public function edit(MachinePartUsage $machine_part_usage): View
    {
        return view('machine-part-usages.edit', [
            'entry' => $machine_part_usage,
            'machines' => Machine::query()->orderBy('name')->get(),
            'parts' => SparePart::query()->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, MachinePartUsage $machine_part_usage): RedirectResponse
    {
        $validated = $request->validate([
            'date' => ['required', 'date'],
            'machine_id' => ['required', 'exists:machines,id'],
            'spare_part_id' => ['required', 'exists:spare_parts,id'],
            'quantity' => ['required', 'numeric', 'gt:0'],
            'usage_type' => ['required', 'in:maintenance,repair,replacement,other'],
            'reference' => ['nullable', 'string', 'max:255'],
            'remarks' => ['nullable', 'string'],
        ]);

        $this->inventoryService->updateUsage($machine_part_usage, $validated);

        return redirect()->route('machine-part-usages.index')->with('success', 'Part usage updated successfully.');
    }

    public function destroy(MachinePartUsage $machine_part_usage): RedirectResponse
    {
        $this->inventoryService->deleteUsage($machine_part_usage);

        return redirect()->route('machine-part-usages.index')->with('success', 'Part usage deleted successfully.');
    }
}