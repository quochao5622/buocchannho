<?php

namespace Quochao56\Equipment\Filament\Resources\EquipmentCategoryResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Quochao56\Equipment\Filament\Resources\EquipmentCategoryResource;
use Quochao56\Core\Traits\HasAutoSave;

class EditEquipmentCategory extends EditRecord
{
    use HasAutoSave;

    protected static string $resource = EquipmentCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
