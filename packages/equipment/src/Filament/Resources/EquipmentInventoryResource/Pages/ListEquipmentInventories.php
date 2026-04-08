<?php

namespace Quochao56\Equipment\Filament\Resources\EquipmentInventoryResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Quochao56\Equipment\Filament\Resources\EquipmentInventoryResource;

class ListEquipmentInventories extends ListRecords
{
    protected static string $resource = EquipmentInventoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

