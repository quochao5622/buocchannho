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
            'quantity_expected' => (int) $equipment->quantity,
            'quantity_actual' => (int) $equipment->quantity,
            'status' => $equipment->status ?: 'good',
            'notes' => null,
        ])->toArray();

        $inventory->details()->createMany($details);
    }
}

