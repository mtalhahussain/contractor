<?php

namespace Tests\Feature;

use App\Models\Machine;
use App\Models\MachinePartUsage;
use App\Models\SparePart;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_stock_in_and_part_usage_update_inventory_and_delete_restores_stock(): void
    {
        $user = User::factory()->create();
        $machine = Machine::create([
            'name' => 'CAT 320',
            'type' => 'Excavator',
            'owner_category' => 'Company',
            'machine_code' => 'EX-001',
            'status' => 'active',
        ]);
        $part = SparePart::create([
            'name' => 'Hydraulic Filter',
            'part_number' => 'HF-100',
            'unit' => 'pcs',
            'minimum_stock' => 2,
        ]);

        $this->actingAs($user)
            ->post(route('part-stock-movements.store'), [
                'date' => '2026-06-02',
                'spare_part_id' => $part->id,
                'movement_type' => 'stock_in',
                'quantity' => 10,
            ])
            ->assertRedirect(route('part-stock-movements.index'));

        $part->refresh();
        $this->assertSame('10.00', $part->current_stock);

        $this->actingAs($user)
            ->post(route('machine-part-usages.store'), [
                'date' => '2026-06-02',
                'machine_id' => $machine->id,
                'spare_part_id' => $part->id,
                'quantity' => 3,
                'usage_type' => 'maintenance',
                'reference' => 'JOB-1',
            ])
            ->assertRedirect(route('machine-part-usages.index'));

        $part->refresh();
        $this->assertSame('7.00', $part->current_stock);

        $usage = MachinePartUsage::query()->firstOrFail();

        $this->assertDatabaseHas('part_stock_movements', [
            'spare_part_id' => $part->id,
            'machine_id' => $machine->id,
            'machine_part_usage_id' => $usage->id,
            'movement_type' => 'usage',
            'quantity' => 3,
            'balance_after' => 7,
        ]);

        $this->actingAs($user)
            ->delete(route('machine-part-usages.destroy', $usage))
            ->assertRedirect(route('machine-part-usages.index'));

        $part->refresh();
        $this->assertSame('10.00', $part->current_stock);
    }

    public function test_part_usage_cannot_exceed_available_stock(): void
    {
        $user = User::factory()->create();
        $machine = Machine::create([
            'name' => 'Komatsu D65',
            'type' => 'Dozer',
            'owner_category' => 'Company',
            'machine_code' => 'DZ-001',
            'status' => 'active',
        ]);
        $part = SparePart::create([
            'name' => 'Track Bolt',
            'part_number' => 'TB-200',
            'unit' => 'pcs',
            'current_stock' => 1,
            'minimum_stock' => 1,
        ]);

        $this->actingAs($user)
            ->from(route('machine-part-usages.create'))
            ->post(route('machine-part-usages.store'), [
                'date' => '2026-06-02',
                'machine_id' => $machine->id,
                'spare_part_id' => $part->id,
                'quantity' => 2,
                'usage_type' => 'repair',
            ])
            ->assertRedirect(route('machine-part-usages.create'))
            ->assertSessionHasErrors('quantity');

        $part->refresh();
        $this->assertSame('1.00', $part->current_stock);
        $this->assertDatabaseCount('machine_part_usages', 0);
    }

    public function test_inventory_stock_report_filters_low_stock_items(): void
    {
        $user = User::factory()->create();

        SparePart::create([
            'name' => 'Bucket Tooth',
            'part_number' => 'BT-10',
            'unit' => 'pcs',
            'current_stock' => 2,
            'minimum_stock' => 2,
        ]);

        SparePart::create([
            'name' => 'Oil Seal',
            'part_number' => 'OS-10',
            'unit' => 'pcs',
            'current_stock' => 10,
            'minimum_stock' => 2,
        ]);

        $this->actingAs($user)
            ->get(route('reports.inventory-stock', ['low_stock_only' => 1]))
            ->assertOk()
            ->assertSee('Bucket Tooth')
            ->assertDontSee('Oil Seal');
    }
}