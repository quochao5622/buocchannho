<?php

namespace Quochao56\Equipment\Filament\Resources\EquipmentInventoryResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Quochao56\Equipment\Filament\Resources\EquipmentInventoryResource;
use Quochao56\Equipment\Models\Equipment;

class CreateEquipmentInventory extends CreateRecord
{
    protected static string $resource = EquipmentInventoryResource::class;

    protected function afterCreate(): void
    {
        $inventory = $this->record;

        // Lấy tất cả học cụ đang hoạt động/có trong hệ thống
        $equipments = Equipment::all();

        $details = $equipments->map(fn ($equipment) => [
            'equipment_id' => $equipment->id,
            'quantity_expected_good' => (int) $equipment->quantity_good,
            'quantity_actual_good' => (int) $equipment->quantity_good,
            'quantity_expected_broken' => (int) $equipment->quantity_broken,
            'quantity_actual_broken' => (int) $equipment->quantity_broken,
            'quantity_expected_missing' => (int) $equipment->quantity_missing,
            'quantity_actual_missing' => (int) $equipment->quantity_missing,
            'notes' => null,
        ])->toArray();

        $inventory->details()->createMany($details);
    }
}
