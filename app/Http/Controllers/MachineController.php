<?php

namespace App\Http\Controllers;

use App\Models\Machine;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class MachineController extends Controller
{
    public function index(): View
    {
        $machines = Machine::query()->latest()->paginate(20);

        return view('machines.index', compact('machines'));
    }

    public function create(): View
    {
        return view('machines.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:Excavator,Dozer,Roller,Grader,Water Tanker,Hilux,Dumper'],
            'owner_category' => ['required', 'in:Company,Haji,Foji'],
            'machine_code' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'string', 'max:100'],
            'notes' => ['nullable', 'string'],
        ]);

        $validated['created_by'] = $request->user()?->id;
        Machine::create($validated);

        return redirect()->route('machines.index')->with('success', 'Machine created successfully.');
    }

    public function show(string $id): RedirectResponse
    {
        return redirect()->route('machines.index');
    }

    public function edit(Machine $machine): View
    {
        return view('machines.edit', compact('machine'));
    }

    public function update(Request $request, Machine $machine): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:Excavator,Dozer,Roller,Grader,Water Tanker,Hilux,Dumper'],
            'owner_category' => ['required', 'in:Company,Haji,Foji'],
            'machine_code' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'string', 'max:100'],
            'notes' => ['nullable', 'string'],
        ]);

        $machine->update($validated);

        return redirect()->route('machines.index')->with('success', 'Machine updated successfully.');
    }

    public function destroy(Machine $machine): RedirectResponse
    {
        $machine->delete();

        return redirect()->route('machines.index')->with('success', 'Machine deleted successfully.');
    }
}
