<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Quochao56\Core\Models\User;
use Quochao56\Equipment\Enum\InventoryStatus;
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
        'status' => InventoryStatus::Draft,
    ]);

    expect(fn () => $inventory->approve())
        ->toThrow(RuntimeException::class, 'Only completed inventories can be approved.');
});

it('approves completed inventory and updates equipment quantities and status atomically', function () {
    $category = EquipmentCategory::create(['name' => 'Bàn ghế']);
    $equipment1 = Equipment::create([
        'equipment_code' => 'TB_01',
        'name' => 'Bàn giáo viên',
        'quantity_good' => 10,
        'quantity_broken' => 0,
        'quantity_missing' => 0,
        'category_id' => $category->id,
    ]);

    $equipment2 = Equipment::create([
        'equipment_code' => 'TB_02',
        'name' => 'Ghế học sinh',
        'quantity_good' => 15,
        'quantity_broken' => 5,
        'quantity_missing' => 0,
        'category_id' => $category->id,
    ]);

    $inspector = clone User::factory()->create();

    $inventory = EquipmentInventory::create([
        'inventory_code' => 'INV_TEST_02',
        'inspector_id' => $inspector->id,
        'inventory_date' => now(),
        'status' => InventoryStatus::Completed,
    ]);

    EquipmentInventoryDetail::create([
        'equipment_inventory_id' => $inventory->id,
        'equipment_id' => $equipment1->id,
        'quantity_expected_good' => 10,
        'quantity_actual_good' => 9,
        'quantity_expected_broken' => 0,
        'quantity_actual_broken' => 1,
        'quantity_expected_missing' => 0,
        'quantity_actual_missing' => 0,
    ]);

    EquipmentInventoryDetail::create([
        'equipment_inventory_id' => $inventory->id,
        'equipment_id' => $equipment2->id,
        'quantity_expected_good' => 15,
        'quantity_actual_good' => 12,
        'quantity_expected_broken' => 5,
        'quantity_actual_broken' => 5,
        'quantity_expected_missing' => 0,
        'quantity_actual_missing' => 3,
    ]);

    // Approve the inventory
    $inventory->approve();

    // Verify inventory status changed
    expect($inventory->refresh()->status)->toBe(InventoryStatus::Approved);

    // Verify equipment quantities updated
    $equipment1->refresh();
    expect($equipment1->quantity_good)->toBe(9);
    expect($equipment1->quantity_broken)->toBe(1);
    expect($equipment1->quantity_missing)->toBe(0);
    expect($equipment1->quantity)->toBe(10);

    $equipment2->refresh();
    expect($equipment2->quantity_good)->toBe(12);
    expect($equipment2->quantity_broken)->toBe(5);
    expect($equipment2->quantity_missing)->toBe(3);
    expect($equipment2->quantity)->toBe(20);
});
