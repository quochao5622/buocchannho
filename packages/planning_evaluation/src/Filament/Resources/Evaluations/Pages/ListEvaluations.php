<?php

namespace Quochao56\PlanningEvaluation\Filament\Resources\Evaluations\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Quochao56\PlanningEvaluation\Filament\Resources\Evaluations\EvaluationResource;

class ListEvaluations extends ListRecords
{
    protected static string $resource = EvaluationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
