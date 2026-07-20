<?php

namespace App\Http\Controllers;

use App\Models\SparePart;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SparePartController extends Controller
{
    private const PART_CATEGORIES = [
        'Filters',
        'Engine Parts',
        'Hydraulic Parts',
        'Undercarriage',
        'Electrical',
        'Body & Cabin',
        'Tyres & Wheels',
        'Lubricants',
        'General',
    ];

    private const PART_UNITS = [
        'pcs',
        'set',
        'box',
        'kg',
        'liters',
        'meter',
        'roll',
    ];

    public function index(): View
    {
        $parts = SparePart::query()
            ->when(request()->filled('q'), function ($query) {
                $search = trim((string) request('q'));

                $query->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', '%'.$search.'%')
                        ->orWhere('part_number', 'like', '%'.$search.'%')
                        ->orWhere('category', 'like', '%'.$search.'%')
                        ->orWhere('location', 'like', '%'.$search.'%');
                });
            })
            ->latest()
            ->paginate(25)
            ->withQueryString();

        return view('spare-parts.index', compact('parts'));
    }

    public function create(): View
    {
        return view('spare-parts.create', [
            'categories' => self::PART_CATEGORIES,
            'units' => self::PART_UNITS,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'part_number' => ['nullable', 'string', 'max:255', 'unique:spare_parts,part_number'],
            'category' => ['nullable', Rule::in(self::PART_CATEGORIES)],
            'unit' => ['required', Rule::in(self::PART_UNITS)],
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
        return view('spare-parts.edit', [
            'part' => $spare_part,
            'categories' => self::PART_CATEGORIES,
            'units' => self::PART_UNITS,
        ]);
    }

    public function update(Request $request, SparePart $spare_part): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'part_number' => ['nullable', 'string', 'max:255', Rule::unique('spare_parts', 'part_number')->ignore($spare_part->id)],
            'category' => ['nullable', Rule::in(self::PART_CATEGORIES)],
            'unit' => ['required', Rule::in(self::PART_UNITS)],
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