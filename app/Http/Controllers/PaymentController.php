<?php

namespace App\Http\Controllers;

use App\Models\Machine;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function index(): View
    {
        $payments = Payment::query()->with('machine')->latest('date')->paginate(30);

        return view('payments.index', compact('payments'));
    }

    public function create(): View
    {
        $machines = Machine::query()->orderBy('name')->get();

        return view('payments.create', compact('machines'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'date' => ['required', 'date'],
            'machine_id' => ['nullable', 'exists:machines,id'],
            'party_name' => ['required', 'string', 'max:255'],
            'amount_received' => ['required', 'numeric', 'min:0'],
            'payment_method' => ['nullable', 'string', 'max:255'],
            'remarks' => ['nullable', 'string'],
        ]);

        $validated['created_by'] = $request->user()?->id;
        Payment::create($validated);

        return redirect()->route('payments.index')->with('success', 'Payment saved.');
    }

    public function show(string $id): RedirectResponse
    {
        return redirect()->route('payments.index');
    }

    public function edit(Payment $payment): View
    {
        $machines = Machine::query()->orderBy('name')->get();

        return view('payments.edit', compact('payment', 'machines'));
    }

    public function update(Request $request, Payment $payment): RedirectResponse
    {
        $validated = $request->validate([
            'date' => ['required', 'date'],
            'machine_id' => ['nullable', 'exists:machines,id'],
            'party_name' => ['required', 'string', 'max:255'],
            'amount_received' => ['required', 'numeric', 'min:0'],
            'payment_method' => ['nullable', 'string', 'max:255'],
            'remarks' => ['nullable', 'string'],
        ]);

        $payment->update($validated);

        return redirect()->route('payments.index')->with('success', 'Payment updated.');
    }

    public function destroy(Payment $payment): RedirectResponse
    {
        $payment->delete();

        return redirect()->route('payments.index')->with('success', 'Payment deleted.');
    }
}
