<?php

namespace Quochao56\Employee\Filament\Resources\EmployeeResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Quochao56\Employee\Filament\Resources\EmployeeResource;

class ListEmployees extends ListRecords
{
    protected static string $resource = EmployeeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
