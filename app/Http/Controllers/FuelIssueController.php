<?php

namespace App\Http\Controllers;

use App\Models\FuelIssue;
use App\Models\FuelStock;
use App\Models\Machine;
use App\Services\FuelInventoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FuelIssueController extends Controller
{
    public function __construct(private readonly FuelInventoryService $fuelInventoryService)
    {
    }

    public function index(): View
    {
        $issues = FuelIssue::query()
            ->with(['fuelStock', 'machine'])
            ->latest('date')
            ->latest('id')
            ->paginate(30);

        return view('fuel-issues.index', compact('issues'));
    }

    public function create(): View
    {
        return view('fuel-issues.create', [
            'stocks' => FuelStock::query()->orderBy('name')->get(),
            'machines' => Machine::query()->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'date' => ['required', 'date'],
            'fuel_stock_id' => ['required', 'exists:fuel_stocks,id'],
            'consumer_type' => ['required', 'in:machine,generator,vehicle,equipment,other'],
            'machine_id' => ['nullable', 'exists:machines,id'],
            'consumer_name' => ['nullable', 'string', 'max:255'],
            'quantity' => ['required', 'numeric', 'gt:0'],
            'reference' => ['nullable', 'string', 'max:255'],
            'remarks' => ['nullable', 'string'],
        ]);

        if (($validated['consumer_type'] ?? null) === 'machine' && empty($validated['machine_id'])) {
            return back()->withErrors(['machine_id' => 'Machine is required when consumer type is machine.'])->withInput();
        }

        if (($validated['consumer_type'] ?? null) !== 'machine' && empty($validated['consumer_name'])) {
            return back()->withErrors(['consumer_name' => 'Consumer name is required for non-machine issues.'])->withInput();
        }

        $validated['created_by'] = $request->user()?->id;
        $this->fuelInventoryService->createIssue($validated);

        return redirect()->route('fuel-issues.index')->with('success', 'Fuel issue saved successfully.');
    }

    public function show(string $id): RedirectResponse
    {
        return redirect()->route('fuel-issues.index');
    }

    public function edit(FuelIssue $fuel_issue): View
    {
        return view('fuel-issues.edit', [
            'issue' => $fuel_issue,
            'stocks' => FuelStock::query()->orderBy('name')->get(),
            'machines' => Machine::query()->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, FuelIssue $fuel_issue): RedirectResponse
    {
        $validated = $request->validate([
            'date' => ['required', 'date'],
            'fuel_stock_id' => ['required', 'exists:fuel_stocks,id'],
            'consumer_type' => ['required', 'in:machine,generator,vehicle,equipment,other'],
            'machine_id' => ['nullable', 'exists:machines,id'],
            'consumer_name' => ['nullable', 'string', 'max:255'],
            'quantity' => ['required', 'numeric', 'gt:0'],
            'reference' => ['nullable', 'string', 'max:255'],
            'remarks' => ['nullable', 'string'],
        ]);

        if (($validated['consumer_type'] ?? null) === 'machine' && empty($validated['machine_id'])) {
            return back()->withErrors(['machine_id' => 'Machine is required when consumer type is machine.'])->withInput();
        }

        if (($validated['consumer_type'] ?? null) !== 'machine' && empty($validated['consumer_name'])) {
            return back()->withErrors(['consumer_name' => 'Consumer name is required for non-machine issues.'])->withInput();
        }

        $this->fuelInventoryService->updateIssue($fuel_issue, $validated);

        return redirect()->route('fuel-issues.index')->with('success', 'Fuel issue updated successfully.');
    }

    public function destroy(FuelIssue $fuel_issue): RedirectResponse
    {
        $this->fuelInventoryService->deleteIssue($fuel_issue);

        return redirect()->route('fuel-issues.index')->with('success', 'Fuel issue deleted successfully.');
    }
}