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

    public function index(): View
    {
        $expenses = Expense::query()->with('machine')->latest('date')->paginate(30);

        return view('expenses.index', compact('expenses'));
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
