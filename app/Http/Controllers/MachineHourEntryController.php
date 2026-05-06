<?php

namespace App\Http\Controllers;

use App\Models\Machine;
use App\Models\MachineHourEntry;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MachineHourEntryController extends Controller
{
    public function index(): View
    {
        $entries = MachineHourEntry::query()->with('machine')->latest('date')->paginate(30);

        return view('machine-hours.index', compact('entries'));
    }

    public function create(): View
    {
        $machines = Machine::query()->orderBy('name')->get();

        return view('machine-hours.create', compact('machines'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'date' => ['required', 'date'],
            'machine_id' => [
                'required',
                'exists:machines,id',
                Rule::unique('machine_hour_entries')->where(fn ($query) => $query->where('date', $request->date)),
            ],
            'working_hours' => ['required', 'numeric', 'min:0'],
            'remarks' => ['nullable', 'string'],
        ]);

        $validated['created_by'] = $request->user()?->id;
        MachineHourEntry::create($validated);

        return redirect()->route('machine-hours.index')->with('success', 'Machine hours saved.');
    }

    public function show(string $id): RedirectResponse
    {
        return redirect()->route('machine-hours.index');
    }

    public function edit(MachineHourEntry $machine_hour): View
    {
        $machines = Machine::query()->orderBy('name')->get();

        return view('machine-hours.edit', [
            'entry' => $machine_hour,
            'machines' => $machines,
        ]);
    }

    public function update(Request $request, MachineHourEntry $machine_hour): RedirectResponse
    {
        $validated = $request->validate([
            'date' => ['required', 'date'],
            'machine_id' => [
                'required',
                'exists:machines,id',
                Rule::unique('machine_hour_entries')->ignore($machine_hour->id)->where(fn ($query) => $query->where('date', $request->date)),
            ],
            'working_hours' => ['required', 'numeric', 'min:0'],
            'remarks' => ['nullable', 'string'],
        ]);

        $machine_hour->update($validated);

        return redirect()->route('machine-hours.index')->with('success', 'Machine hours updated.');
    }

    public function destroy(MachineHourEntry $machine_hour): RedirectResponse
    {
        $machine_hour->delete();

        return redirect()->route('machine-hours.index')->with('success', 'Machine hours deleted.');
    }
}
