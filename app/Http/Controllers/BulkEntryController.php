<?php

namespace App\Http\Controllers;

use App\Models\DieselUsageEntry;
use App\Models\Machine;
use App\Models\MachineHourEntry;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class BulkEntryController extends Controller
{
    public function hoursForm(): View
    {
        $machines = Machine::query()->orderBy('name')->get();

        return view('bulk.hours', compact('machines'));
    }

    public function hoursStore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'date' => ['required', 'date'],
            'hours' => ['nullable', 'array'],
            'hours.*' => ['nullable', 'numeric', 'min:0'],
            'remarks' => ['nullable', 'array'],
            'remarks.*' => ['nullable', 'string'],
        ]);

        $savedCount = 0;
        foreach ($validated['hours'] ?? [] as $machineId => $hours) {
            if ($hours === null || (float) $hours <= 0) {
                continue;
            }

            $attributes = [
                'working_hours' => $hours,
                'remarks' => $validated['remarks'][$machineId] ?? null,
                'created_by' => $request->user()?->id,
            ];

            try {
                MachineHourEntry::updateOrCreate(
                    ['date' => $validated['date'], 'machine_id' => $machineId],
                    $attributes
                );
            } catch (QueryException $exception) {
                if (! $this->isMachineHoursUniqueViolation($exception)) {
                    throw $exception;
                }

                $existingEntry = MachineHourEntry::withTrashed()
                    ->whereDate('date', $validated['date'])
                    ->where('machine_id', $machineId)
                    ->first();

                if (! $existingEntry) {
                    throw $exception;
                }

                if ($existingEntry->trashed()) {
                    $existingEntry->restore();
                }

                $existingEntry->update($attributes);
            }

            $savedCount++;
        }

        return redirect()->route('bulk.hours.form')->with('success', "Saved {$savedCount} hour entries.");
    }

    public function dieselForm(): View
    {
        $machines = Machine::query()->orderBy('name')->get();

        return view('bulk.diesel', compact('machines'));
    }

    public function dieselStore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'date' => ['required', 'date'],
            'liters' => ['nullable', 'array'],
            'liters.*' => ['nullable', 'numeric', 'min:0'],
            'remarks' => ['nullable', 'array'],
            'remarks.*' => ['nullable', 'string'],
        ]);

        $savedCount = 0;
        foreach ($validated['liters'] ?? [] as $machineId => $liters) {
            if ($liters === null || (float) $liters <= 0) {
                continue;
            }

            DieselUsageEntry::updateOrCreate(
                ['date' => $validated['date'], 'machine_id' => $machineId],
                [
                    'diesel_liters' => $liters,
                    'remarks' => $validated['remarks'][$machineId] ?? null,
                    'created_by' => $request->user()?->id,
                ]
            );
            $savedCount++;
        }

        return redirect()->route('bulk.diesel.form')->with('success', "Saved {$savedCount} diesel entries.");
    }

    private function isMachineHoursUniqueViolation(QueryException $exception): bool
    {
        return (string) $exception->getCode() === '23000'
            && str_contains(
                strtolower($exception->getMessage()),
                'machine_hour_entries_date_machine_id_unique'
            );
    }
}
