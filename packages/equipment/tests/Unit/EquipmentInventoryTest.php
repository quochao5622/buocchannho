<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Quochao56\Core\Models\User;
use Quochao56\Equipment\Models\Equipment;
use Quochao56\Equipment\Models\EquipmentCategory;
use Quochao56\Equipment\Models\EquipmentInventory;
use Quochao56\Equipment\Models\EquipmentInventoryDetail;
use Quochao56\Equipment\Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('prevents approval of incomplete inventory', function () {
    $inspector = clone User::factory()->create();
    $inventory = EquipmentInventory::create([
        'inventory_code' => 'INV_TEST_01',
        'inspector_id' => $inspector->id,
        'inventory_date' => now(),
        'status' => 'draft',
    ]);

    expect(fn () => $inventory->approve())
        ->toThrow(RuntimeException::class, 'Only completed inventories can be approved.');
});

it('approves completed inventory and updates equipment quantities and status atomically', function () {
    $category = EquipmentCategory::create(['name' => 'Bàn ghế']);
    $equipment1 = Equipment::create([
        'equipment_code' => 'TB_01',
        'name' => 'Bàn giáo viên',
        'quantity' => 10,
        'category_id' => $category->id,
        'status' => 'good',
    ]);

    $equipment2 = Equipment::create([
        'equipment_code' => 'TB_02',
        'name' => 'Ghế học sinh',
        'quantity' => 20,
        'category_id' => $category->id,
        'status' => 'broken',
    ]);

    $inspector = clone User::factory()->create();

    $inventory = EquipmentInventory::create([
        'inventory_code' => 'INV_TEST_02',
        'inspector_id' => $inspector->id,
        'inventory_date' => now(),
        'status' => 'completed',
    ]);

    EquipmentInventoryDetail::create([
        'equipment_inventory_id' => $inventory->id,
        'equipment_id' => $equipment1->id,
        'quantity_expected' => 10,
        'quantity_actual' => 9,
        'status' => 'broken',
    ]);

    EquipmentInventoryDetail::create([
        'equipment_inventory_id' => $inventory->id,
        'equipment_id' => $equipment2->id,
        'quantity_expected' => 20,
        'quantity_actual' => 25,
        'status' => 'good',
    ]);

    // Approve the inventory
    $inventory->approve();

    // Verify inventory status changed
    expect($inventory->refresh()->status)->toBe('approved');

    // Verify equipment quantities and status updated
    expect($equipment1->refresh()->quantity)->toBe(9);
    expect($equipment1->status)->toBe('broken');

    expect($equipment2->refresh()->quantity)->toBe(25);
    expect($equipment2->status)->toBe('good');
});
