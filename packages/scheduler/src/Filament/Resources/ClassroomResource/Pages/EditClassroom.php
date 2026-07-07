<?php

namespace Quochao56\Scheduler\Filament\Resources\ClassroomResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Quochao56\Scheduler\Filament\Resources\ClassroomResource;

class EditClassroom extends EditRecord
{
    protected static string $resource = ClassroomResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
