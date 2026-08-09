<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Machine;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ExpenseController extends Controller
{
    private const EXPENSE_TYPES = [
        'Fuel',
        'Repair & Maintenance',
        'Spare Parts',
        'Tyre & Tube',
        'Labour',
        'Transport',
        'Miscellaneous',
    ];

    public function index(Request $request): View
    {
        $machines = Machine::query()->orderBy('name')->get(['id', 'name']);
        $expenses = Expense::query()->with('machine')
            ->when($request->filled('machine_id'), fn ($q) => $q->where('machine_id', $request->machine_id))
            ->when($request->filled('expense_type'), fn ($q) => $q->where('expense_type', $request->expense_type))
            ->when($request->filled('month') && preg_match('/^\d{4}-\d{2}$/', (string) $request->month), function ($q) use ($request) {
                [$y, $m] = explode('-', $request->month);
                $q->whereYear('date', $y)->whereMonth('date', $m);
            })
            ->latest('date')->paginate(30)->withQueryString();

        return view('expenses.index', compact('expenses', 'machines'));
    }

    public function create(): View
    {
        $machines = Machine::query()->orderBy('name')->get();

        return view('expenses.create', [
            'machines' => $machines,
            'expenseTypes' => self::EXPENSE_TYPES,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'date' => ['required', 'date'],
            'machine_id' => ['nullable', 'exists:machines,id'],
            'expense_type' => ['required', Rule::in(self::EXPENSE_TYPES)],
            'amount' => ['required', 'numeric', 'min:0'],
            'remarks' => ['nullable', 'string'],
        ]);

        $validated['created_by'] = $request->user()?->id;
        Expense::create($validated);

        return redirect()->route('expenses.index')->with('success', 'Expense saved.');
    }

    public function show(string $id): RedirectResponse
    {
        return redirect()->route('expenses.index');
    }

    public function edit(Expense $expense): View
    {
        $machines = Machine::query()->orderBy('name')->get();

        return view('expenses.edit', [
            'expense' => $expense,
            'machines' => $machines,
            'expenseTypes' => self::EXPENSE_TYPES,
        ]);
    }

    public function update(Request $request, Expense $expense): RedirectResponse
    {
        $validated = $request->validate([
            'date' => ['required', 'date'],
            'machine_id' => ['nullable', 'exists:machines,id'],
            'expense_type' => ['required', Rule::in(self::EXPENSE_TYPES)],
            'amount' => ['required', 'numeric', 'min:0'],
            'remarks' => ['nullable', 'string'],
        ]);

        $expense->update($validated);

        return redirect()->route('expenses.index')->with('success', 'Expense updated.');
    }

    public function destroy(Expense $expense): RedirectResponse
    {
        $expense->delete();

        return redirect()->route('expenses.index')->with('success', 'Expense deleted.');
    }
}
