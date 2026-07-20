<?php

namespace App\Http\Controllers;

use App\Models\Machine;
use App\Models\Site;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class MachineController extends Controller
{
    public function index(): View
    {
        $machines = Machine::query()
            ->with(['currentSiteAssignment.site'])
            ->latest()
            ->paginate(20);

        return view('machines.index', compact('machines'));
    }

    public function create(): View
    {
        $sites = Site::query()
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        return view('machines.create', compact('sites'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:Excavator,Dozer,Roller,Grader,Water Tanker,Hilux,Dumper'],
            'owner_category' => ['required', 'in:Company,Haji,Foji'],
            'machine_code' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'in:active,inactive'],
            'notes' => ['nullable', 'string'],
            'site_id' => ['nullable', Rule::exists('sites', 'id')->where('status', 'active')],
            'site_assigned_from' => ['nullable', 'date', 'required_with:site_id'],
        ]);

        $machineData = Arr::except($validated, ['site_id', 'site_assigned_from']);
        $machineData['created_by'] = $request->user()?->id;
        $machine = Machine::create($machineData);

        $this->syncSiteAssignment($request, $machine, $validated, true);

        return redirect()->route('machines.index')->with('success', 'Machine created successfully.');
    }

    public function show(string $id): RedirectResponse
    {
        return redirect()->route('machines.index');
    }

    public function edit(Machine $machine): View
    {
        $machine->load('siteAssignments.site');
        $sites = Site::query()
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        return view('machines.edit', compact('machine', 'sites'));
    }

    public function update(Request $request, Machine $machine): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:Excavator,Dozer,Roller,Grader,Water Tanker,Hilux,Dumper'],
            'owner_category' => ['required', 'in:Company,Haji,Foji'],
            'machine_code' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'in:active,inactive'],
            'notes' => ['nullable', 'string'],
            'site_id' => ['nullable', Rule::exists('sites', 'id')->where('status', 'active')],
            'site_assigned_from' => ['nullable', 'date', 'required_with:site_id'],
        ]);

        $machineData = Arr::except($validated, ['site_id', 'site_assigned_from']);
        $machine->update($machineData);
        $this->syncSiteAssignment($request, $machine, $validated, false);

        return redirect()->route('machines.index')->with('success', 'Machine updated successfully.');
    }

    public function destroy(Machine $machine): RedirectResponse
    {
        $machine->delete();

        return redirect()->route('machines.index')->with('success', 'Machine deleted successfully.');
    }

    private function syncSiteAssignment(Request $request, Machine $machine, array $validated, bool $isCreate): void
    {
        $siteId = $validated['site_id'] ?? null;
        if (empty($siteId)) {
            return;
        }

        $assignedFrom = Carbon::parse($validated['site_assigned_from'])->toDateString();

        $currentAssignment = $machine->siteAssignments()
            ->whereNull('assigned_to')
            ->latest('assigned_from')
            ->first();

        if (! $currentAssignment) {
            $machine->siteAssignments()->create([
                'site_id' => $siteId,
                'assigned_from' => $assignedFrom,
                'created_by' => $request->user()?->id,
            ]);

            return;
        }

        if ((int) $currentAssignment->site_id === (int) $siteId) {
            return;
        }

        if (Carbon::parse($assignedFrom)->lte(Carbon::parse($currentAssignment->assigned_from))) {
            $message = $isCreate
                ? 'Site assignment date must be after the current active assignment start date.'
                : 'New site assignment date must be after the current site assignment start date.';

            throw ValidationException::withMessages([
                'site_assigned_from' => $message,
            ]);
        }

        $currentAssignment->update([
            'assigned_to' => Carbon::parse($assignedFrom)->subDay()->toDateString(),
        ]);

        $machine->siteAssignments()->create([
            'site_id' => $siteId,
            'assigned_from' => $assignedFrom,
            'created_by' => $request->user()?->id,
        ]);
    }
}
