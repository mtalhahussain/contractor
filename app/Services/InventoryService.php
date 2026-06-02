<?php

namespace App\Services;

use App\Models\MachinePartUsage;
use App\Models\PartStockMovement;
use App\Models\SparePart;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InventoryService
{
    public function createMovement(array $attributes): PartStockMovement
    {
        return DB::transaction(function () use ($attributes) {
            $part = SparePart::query()->lockForUpdate()->findOrFail($attributes['spare_part_id']);
            $quantity = (float) $attributes['quantity'];
            $delta = $this->movementDelta($attributes['movement_type'], $quantity);
            $balance = (float) $part->current_stock + $delta;

            $this->assertStockNotNegative($part, $balance);

            $part->update(['current_stock' => $balance]);

            return PartStockMovement::create([
                'date' => $attributes['date'],
                'spare_part_id' => $part->id,
                'machine_id' => $attributes['machine_id'] ?? null,
                'machine_part_usage_id' => $attributes['machine_part_usage_id'] ?? null,
                'movement_type' => $attributes['movement_type'],
                'quantity' => $quantity,
                'balance_after' => $balance,
                'reference' => $attributes['reference'] ?? null,
                'remarks' => $attributes['remarks'] ?? null,
                'created_by' => $attributes['created_by'] ?? null,
            ]);
        });
    }

    public function updateMovement(PartStockMovement $movement, array $attributes): PartStockMovement
    {
        return DB::transaction(function () use ($movement, $attributes) {
            $movement->loadMissing('sparePart');
            $this->revertMovement($movement);

            $updated = $this->createMovement(array_merge($movement->only([
                'date',
                'spare_part_id',
                'machine_id',
                'machine_part_usage_id',
                'movement_type',
                'reference',
                'remarks',
                'created_by',
            ]), $attributes));

            $movement->delete();

            return $updated;
        });
    }

    public function deleteMovement(PartStockMovement $movement): void
    {
        DB::transaction(function () use ($movement) {
            $this->revertMovement($movement);
            $movement->delete();
        });
    }

    public function createUsage(array $attributes): MachinePartUsage
    {
        return DB::transaction(function () use ($attributes) {
            $usage = MachinePartUsage::create([
                'date' => $attributes['date'],
                'machine_id' => $attributes['machine_id'],
                'spare_part_id' => $attributes['spare_part_id'],
                'quantity' => $attributes['quantity'],
                'usage_type' => $attributes['usage_type'],
                'reference' => $attributes['reference'] ?? null,
                'remarks' => $attributes['remarks'] ?? null,
                'created_by' => $attributes['created_by'] ?? null,
            ]);

            $this->createMovement([
                'date' => $usage->date->format('Y-m-d'),
                'spare_part_id' => $usage->spare_part_id,
                'machine_id' => $usage->machine_id,
                'machine_part_usage_id' => $usage->id,
                'movement_type' => 'usage',
                'quantity' => $usage->quantity,
                'reference' => $usage->reference,
                'remarks' => $usage->remarks,
                'created_by' => $usage->created_by,
            ]);

            return $usage->load(['machine', 'sparePart', 'stockMovement']);
        });
    }

    public function updateUsage(MachinePartUsage $usage, array $attributes): MachinePartUsage
    {
        return DB::transaction(function () use ($usage, $attributes) {
            $usage->loadMissing('stockMovement');

            if ($usage->stockMovement) {
                $this->deleteMovement($usage->stockMovement);
            }

            $usage->update([
                'date' => $attributes['date'],
                'machine_id' => $attributes['machine_id'],
                'spare_part_id' => $attributes['spare_part_id'],
                'quantity' => $attributes['quantity'],
                'usage_type' => $attributes['usage_type'],
                'reference' => $attributes['reference'] ?? null,
                'remarks' => $attributes['remarks'] ?? null,
            ]);

            $this->createMovement([
                'date' => $usage->date->format('Y-m-d'),
                'spare_part_id' => $usage->spare_part_id,
                'machine_id' => $usage->machine_id,
                'machine_part_usage_id' => $usage->id,
                'movement_type' => 'usage',
                'quantity' => $usage->quantity,
                'reference' => $usage->reference,
                'remarks' => $usage->remarks,
                'created_by' => $usage->created_by,
            ]);

            return $usage->load(['machine', 'sparePart', 'stockMovement']);
        });
    }

    public function deleteUsage(MachinePartUsage $usage): void
    {
        DB::transaction(function () use ($usage) {
            $usage->loadMissing('stockMovement');

            if ($usage->stockMovement) {
                $this->deleteMovement($usage->stockMovement);
            }

            $usage->delete();
        });
    }

    private function revertMovement(PartStockMovement $movement): void
    {
        $part = SparePart::query()->lockForUpdate()->findOrFail($movement->spare_part_id);
        $delta = $this->movementDelta($movement->movement_type, (float) $movement->quantity);
        $balance = (float) $part->current_stock - $delta;

        $this->assertStockNotNegative($part, $balance);

        $part->update(['current_stock' => $balance]);
    }

    private function movementDelta(string $movementType, float $quantity): float
    {
        return match ($movementType) {
            'stock_in' => $quantity,
            'stock_out', 'usage' => -$quantity,
            default => throw ValidationException::withMessages([
                'movement_type' => 'Invalid stock movement type.',
            ]),
        };
    }

    private function assertStockNotNegative(SparePart $part, float $balance): void
    {
        if ($balance < 0) {
            throw ValidationException::withMessages([
                'quantity' => 'Insufficient stock for '.$part->name.'. Available quantity is '.number_format((float) $part->current_stock, 2).'.',
            ]);
        }
    }
}