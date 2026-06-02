<?php

namespace Tests\Feature;

use App\Models\FuelIssue;
use App\Models\FuelStock;
use App\Models\Machine;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FuelInventoryManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_fuel_stock_in_and_issue_update_inventory_and_delete_restores_stock(): void
    {
        $user = User::factory()->create();
        $machine = Machine::create([
            'name' => 'Generator 1',
            'type' => 'Excavator',
            'owner_category' => 'Company',
            'machine_code' => 'GEN-001',
            'status' => 'active',
        ]);
        $stock = FuelStock::create([
            'name' => 'Main Diesel Tank',
            'unit' => 'liters',
            'minimum_stock' => 100,
            'current_stock' => 0,
        ]);

        $this->actingAs($user)
            ->post(route('fuel-stock-movements.store'), [
                'date' => '2026-06-03',
                'fuel_stock_id' => $stock->id,
                'movement_type' => 'stock_in',
                'quantity' => 500,
            ])
            ->assertRedirect(route('fuel-stock-movements.index'));

        $stock->refresh();
        $this->assertSame('500.00', $stock->current_stock);

        $this->actingAs($user)
            ->post(route('fuel-issues.store'), [
                'date' => '2026-06-03',
                'fuel_stock_id' => $stock->id,
                'consumer_type' => 'machine',
                'machine_id' => $machine->id,
                'quantity' => 80,
                'reference' => 'SHIFT-1',
            ])
            ->assertRedirect(route('fuel-issues.index'));

        $stock->refresh();
        $this->assertSame('420.00', $stock->current_stock);

        $issue = FuelIssue::query()->firstOrFail();

        $this->assertDatabaseHas('fuel_stock_movements', [
            'fuel_stock_id' => $stock->id,
            'machine_id' => $machine->id,
            'fuel_issue_id' => $issue->id,
            'movement_type' => 'issue',
            'quantity' => 80,
            'balance_after' => 420,
        ]);

        $this->actingAs($user)
            ->delete(route('fuel-issues.destroy', $issue))
            ->assertRedirect(route('fuel-issues.index'));

        $stock->refresh();
        $this->assertSame('500.00', $stock->current_stock);
    }

    public function test_fuel_issue_cannot_exceed_available_stock(): void
    {
        $user = User::factory()->create();
        $stock = FuelStock::create([
            'name' => 'Backup Tank',
            'unit' => 'liters',
            'minimum_stock' => 10,
            'current_stock' => 20,
        ]);

        $this->actingAs($user)
            ->from(route('fuel-issues.create'))
            ->post(route('fuel-issues.store'), [
                'date' => '2026-06-03',
                'fuel_stock_id' => $stock->id,
                'consumer_type' => 'generator',
                'consumer_name' => 'DG-2',
                'quantity' => 30,
            ])
            ->assertRedirect(route('fuel-issues.create'))
            ->assertSessionHasErrors('quantity');

        $stock->refresh();
        $this->assertSame('20.00', $stock->current_stock);
        $this->assertDatabaseCount('fuel_issues', 0);
    }
}