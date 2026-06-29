<?php

namespace Quochao56\Equipment\Filament\Resources\EquipmentResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Quochao56\Equipment\Filament\Resources\EquipmentResource;
use Quochao56\Core\Traits\HasAutoSave;

class EditEquipment extends EditRecord
{
    use HasAutoSave;

    protected static string $resource = EquipmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
