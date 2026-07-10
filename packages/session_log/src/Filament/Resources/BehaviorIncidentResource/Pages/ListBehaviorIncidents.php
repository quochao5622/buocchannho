<?php

namespace Quochao56\SessionLog\Filament\Resources\BehaviorIncidentResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Quochao56\SessionLog\Filament\Resources\BehaviorIncidentResource;

class ListBehaviorIncidents extends ListRecords
{
    protected static string $resource = BehaviorIncidentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
