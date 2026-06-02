<?php

namespace App\Services;

use App\Models\FuelIssue;
use App\Models\FuelStock;
use App\Models\FuelStockMovement;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class FuelInventoryService
{
    public function createMovement(array $attributes): FuelStockMovement
    {
        return DB::transaction(function () use ($attributes) {
            $stock = FuelStock::query()->lockForUpdate()->findOrFail($attributes['fuel_stock_id']);
            $quantity = (float) $attributes['quantity'];
            $delta = $this->movementDelta($attributes['movement_type'], $quantity);
            $balance = (float) $stock->current_stock + $delta;

            $this->assertStockNotNegative($stock, $balance);

            $stock->update(['current_stock' => $balance]);

            return FuelStockMovement::create([
                'date' => $attributes['date'],
                'fuel_stock_id' => $stock->id,
                'fuel_issue_id' => $attributes['fuel_issue_id'] ?? null,
                'machine_id' => $attributes['machine_id'] ?? null,
                'movement_type' => $attributes['movement_type'],
                'quantity' => $quantity,
                'balance_after' => $balance,
                'reference' => $attributes['reference'] ?? null,
                'remarks' => $attributes['remarks'] ?? null,
                'created_by' => $attributes['created_by'] ?? null,
            ]);
        });
    }

    public function updateMovement(FuelStockMovement $movement, array $attributes): FuelStockMovement
    {
        return DB::transaction(function () use ($movement, $attributes) {
            $this->revertMovement($movement);

            $updated = $this->createMovement(array_merge($movement->only([
                'date',
                'fuel_stock_id',
                'fuel_issue_id',
                'machine_id',
                'movement_type',
                'reference',
                'remarks',
                'created_by',
            ]), $attributes));

            $movement->delete();

            return $updated;
        });
    }

    public function deleteMovement(FuelStockMovement $movement): void
    {
        DB::transaction(function () use ($movement) {
            $this->revertMovement($movement);
            $movement->delete();
        });
    }

    public function createIssue(array $attributes): FuelIssue
    {
        return DB::transaction(function () use ($attributes) {
            $issue = FuelIssue::create([
                'date' => $attributes['date'],
                'fuel_stock_id' => $attributes['fuel_stock_id'],
                'machine_id' => $attributes['machine_id'] ?? null,
                'consumer_type' => $attributes['consumer_type'],
                'consumer_name' => $attributes['consumer_name'] ?? null,
                'quantity' => $attributes['quantity'],
                'reference' => $attributes['reference'] ?? null,
                'remarks' => $attributes['remarks'] ?? null,
                'created_by' => $attributes['created_by'] ?? null,
            ]);

            $this->createMovement([
                'date' => $issue->date->format('Y-m-d'),
                'fuel_stock_id' => $issue->fuel_stock_id,
                'fuel_issue_id' => $issue->id,
                'machine_id' => $issue->machine_id,
                'movement_type' => 'issue',
                'quantity' => $issue->quantity,
                'reference' => $issue->reference,
                'remarks' => $issue->remarks,
                'created_by' => $issue->created_by,
            ]);

            return $issue->load(['fuelStock', 'machine', 'movement']);
        });
    }

    public function updateIssue(FuelIssue $issue, array $attributes): FuelIssue
    {
        return DB::transaction(function () use ($issue, $attributes) {
            $issue->loadMissing('movement');

            if ($issue->movement) {
                $this->deleteMovement($issue->movement);
            }

            $issue->update([
                'date' => $attributes['date'],
                'fuel_stock_id' => $attributes['fuel_stock_id'],
                'machine_id' => $attributes['machine_id'] ?? null,
                'consumer_type' => $attributes['consumer_type'],
                'consumer_name' => $attributes['consumer_name'] ?? null,
                'quantity' => $attributes['quantity'],
                'reference' => $attributes['reference'] ?? null,
                'remarks' => $attributes['remarks'] ?? null,
            ]);

            $this->createMovement([
                'date' => $issue->date->format('Y-m-d'),
                'fuel_stock_id' => $issue->fuel_stock_id,
                'fuel_issue_id' => $issue->id,
                'machine_id' => $issue->machine_id,
                'movement_type' => 'issue',
                'quantity' => $issue->quantity,
                'reference' => $issue->reference,
                'remarks' => $issue->remarks,
                'created_by' => $issue->created_by,
            ]);

            return $issue->load(['fuelStock', 'machine', 'movement']);
        });
    }

    public function deleteIssue(FuelIssue $issue): void
    {
        DB::transaction(function () use ($issue) {
            $issue->loadMissing('movement');

            if ($issue->movement) {
                $this->deleteMovement($issue->movement);
            }

            $issue->delete();
        });
    }

    private function revertMovement(FuelStockMovement $movement): void
    {
        $stock = FuelStock::query()->lockForUpdate()->findOrFail($movement->fuel_stock_id);
        $delta = $this->movementDelta($movement->movement_type, (float) $movement->quantity);
        $balance = (float) $stock->current_stock - $delta;

        $this->assertStockNotNegative($stock, $balance);

        $stock->update(['current_stock' => $balance]);
    }

    private function movementDelta(string $movementType, float $quantity): float
    {
        return match ($movementType) {
            'stock_in' => $quantity,
            'stock_out', 'issue' => -$quantity,
            default => throw ValidationException::withMessages([
                'movement_type' => 'Invalid fuel movement type.',
            ]),
        };
    }

    private function assertStockNotNegative(FuelStock $stock, float $balance): void
    {
        if ($balance < 0) {
            throw ValidationException::withMessages([
                'quantity' => 'Insufficient fuel stock for '.$stock->name.'. Available quantity is '.number_format((float) $stock->current_stock, 2).'.',
            ]);
        }
    }
}