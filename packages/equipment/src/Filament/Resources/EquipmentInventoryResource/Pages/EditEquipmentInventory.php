<?php

namespace Quochao56\Equipment\Filament\Resources\EquipmentInventoryResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Quochao56\Equipment\Filament\Actions\ApproveAction;
use Quochao56\Equipment\Filament\Resources\EquipmentInventoryResource;

class EditEquipmentInventory extends EditRecord
{
    protected static string $resource = EquipmentInventoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ApproveAction::make(),
            $this->getSaveFormAction()
                ->submit(null)
                ->action(fn() => $this->save())
                ->keyBindings(['mod+s']),
            DeleteAction::make(),
        ];
    }
}

