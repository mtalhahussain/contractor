<?php

namespace App\Http\Controllers;

use App\Models\SparePart;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SparePartController extends Controller
{
    public function index(): View
    {
        $parts = SparePart::query()->latest()->paginate(25);

        return view('spare-parts.index', compact('parts'));
    }

    public function create(): View
    {
        return view('spare-parts.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'part_number' => ['nullable', 'string', 'max:255', 'unique:spare_parts,part_number'],
            'category' => ['nullable', 'string', 'max:255'],
            'unit' => ['required', 'string', 'max:30'],
            'current_stock' => ['required', 'numeric', 'min:0'],
            'minimum_stock' => ['required', 'numeric', 'min:0'],
            'location' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        $validated['created_by'] = $request->user()?->id;
        SparePart::create($validated);

        return redirect()->route('spare-parts.index')->with('success', 'Spare part created successfully.');
    }

    public function show(string $id): RedirectResponse
    {
        return redirect()->route('spare-parts.index');
    }

    public function edit(SparePart $spare_part): View
    {
        return view('spare-parts.edit', ['part' => $spare_part]);
    }

    public function update(Request $request, SparePart $spare_part): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'part_number' => ['nullable', 'string', 'max:255', Rule::unique('spare_parts', 'part_number')->ignore($spare_part->id)],
            'category' => ['nullable', 'string', 'max:255'],
            'unit' => ['required', 'string', 'max:30'],
            'minimum_stock' => ['required', 'numeric', 'min:0'],
            'location' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        $spare_part->update($validated);

        return redirect()->route('spare-parts.index')->with('success', 'Spare part updated successfully.');
    }

    public function destroy(SparePart $spare_part): RedirectResponse
    {
        $spare_part->delete();

        return redirect()->route('spare-parts.index')->with('success', 'Spare part deleted successfully.');
    }
}