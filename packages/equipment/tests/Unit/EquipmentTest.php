<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Quochao56\Equipment\Models\Equipment;
use Quochao56\Equipment\Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

use Quochao56\Equipment\Models\EquipmentCategory;

it('provides correct status options', function () {
    $options = Equipment::statusOptions();

    expect($options)->toHaveKey('good');
    expect($options)->toHaveKey('broken');
    expect($options['good'])->toBe('Tốt');
});

it('allows mass assignment of basic attributes', function () {
    $category = EquipmentCategory::create(['name' => 'Bàn ghế']);
    $equipment = Equipment::create([
        'equipment_code' => 'TB02',
        'name' => 'Ghế học sinh',
        'description' => 'Mô tả ghế',
        'quantity_good' => 15,
        'quantity_broken' => 5,
        'quantity_missing' => 0,
        'category_id' => $category->id,
    ]);

    expect($equipment->name)->toBe('Ghế học sinh');
    expect($equipment->quantity)->toBe(20);
    expect($equipment->quantity_good)->toBe(15);
    expect($equipment->quantity_broken)->toBe(5);
});
