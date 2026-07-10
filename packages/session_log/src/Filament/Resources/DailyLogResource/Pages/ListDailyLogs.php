<?php

namespace Quochao56\SessionLog\Filament\Resources\DailyLogResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Quochao56\SessionLog\Filament\Resources\DailyLogResource;

class ListDailyLogs extends ListRecords
{
    protected static string $resource = DailyLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
