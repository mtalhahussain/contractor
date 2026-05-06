<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Machine;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ExpenseController extends Controller
{
    public function index(): View
    {
        $expenses = Expense::query()->with('machine')->latest('date')->paginate(30);

        return view('expenses.index', compact('expenses'));
    }

    public function create(): View
    {
        $machines = Machine::query()->orderBy('name')->get();

        return view('expenses.create', compact('machines'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'date' => ['required', 'date'],
            'machine_id' => ['nullable', 'exists:machines,id'],
            'expense_type' => ['required', 'string', 'max:255'],
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

        return view('expenses.edit', compact('expense', 'machines'));
    }

    public function update(Request $request, Expense $expense): RedirectResponse
    {
        $validated = $request->validate([
            'date' => ['required', 'date'],
            'machine_id' => ['nullable', 'exists:machines,id'],
            'expense_type' => ['required', 'string', 'max:255'],
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
