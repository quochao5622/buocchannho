<?php

namespace Quochao56\Scheduler\Filament\Resources\ClassroomResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Quochao56\Scheduler\Filament\Resources\ClassroomResource;

class ListClassrooms extends ListRecords
{
    protected static string $resource = ClassroomResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
