<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    /**
     * Display a listing of all employees
     */
    public function index(Request $request)
    {
        $employees = Employee::with('salaryHistories')
            ->when($request->filled('q'), fn ($q) => $q->where(fn ($i) => $i->where('name', 'like', '%'.$request->q.'%')->orWhere('employee_code', 'like', '%'.$request->q.'%')))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('employees.index', compact('employees'));
    }

    /**
     * Show the form for creating a new employee
     */
    public function create()
    {
        return view('employees.create');
    }

    /**
     * Store a newly created employee in database
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'designation' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email',
            'address' => 'nullable|string',
            'cnic' => 'nullable|string|max:20',
            'bank_account' => 'nullable|string|max:50',
            'bank_name' => 'nullable|string|max:255',
            'joining_date' => 'nullable|date',
            'status' => 'required|in:active,inactive',
            'notes' => 'nullable|string',
        ]);

        $validated['created_by'] = auth()->id();
        // Employee code will be auto-generated in Model

        Employee::create($validated);

        return redirect()->route('employees.index')
            ->with('success', 'Employee created successfully!');
    }

    /**
     * Display the specified employee with salary dashboard
     */
    public function show(Employee $employee)
    {
        // Redirect to salary dashboard (main employee page)
        return redirect()->route('employees.salary', $employee);
    }

    /**
     * Show the form for editing the specified employee
     */
    public function edit(Employee $employee)
    {
        return view('employees.edit', compact('employee'));
    }

    /**
     * Update the specified employee in database
     */
    public function update(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'designation' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email',
            'address' => 'nullable|string',
            'cnic' => 'nullable|string|max:20',
            'bank_account' => 'nullable|string|max:50',
            'bank_name' => 'nullable|string|max:255',
            'joining_date' => 'nullable|date',
            'status' => 'required|in:active,inactive',
            'notes' => 'nullable|string',
        ]);

        $employee->update($validated);

        return redirect()->route('employees.show', $employee)
            ->with('success', 'Employee updated successfully!');
    }

    /**
     * Remove the specified employee
     */
    public function destroy(Employee $employee)
    {
        $employee->delete();

        return redirect()->route('employees.index')
            ->with('success', 'Employee deleted successfully!');
    }

    /**
     * Show salary dashboard for employee
     */
    public function salary(Employee $employee)
    {
        $employee->load('salaryHistories', 'salaryAdvances', 'salaryLogs', 'salaryBonuses');
        return view('employees.salary-dashboard', compact('employee'));
    }
}
