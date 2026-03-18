<?php

namespace Quochao56\PlanningEvaluation\Filament\Resources\Plannings\Pages;

use Quochao56\PlanningEvaluation\Filament\Resources\Plannings\PlanningResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPlannings extends ListRecords
{
    protected static string $resource = PlanningResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
