<?php

namespace Quochao56\PlanningEvaluation\Filament\Resources\Evaluations\Pages;

use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Quochao56\PlanningEvaluation\Filament\Actions\ApproveAction;
use Quochao56\PlanningEvaluation\Filament\Actions\ExportEvaluationWordAction;
use Quochao56\PlanningEvaluation\Filament\Actions\ReopenAction;
use Quochao56\PlanningEvaluation\Filament\Resources\Evaluations\EvaluationResource;
use Quochao56\PlanningEvaluation\Filament\Resources\Plannings\PlanningResource;

class ViewEvaluation extends ViewRecord
{
    protected static string $resource = EvaluationResource::class;

    public function getBreadcrumbs(): array
    {
        $planning = $this->getRecord()?->planning;

        return [
            PlanningResource::getUrl('index') => PlanningResource::getPluralModelLabel(),
            PlanningResource::getUrl('edit', ['record' => $planning]) => $planning?->name ?? '',
            EvaluationResource::getUrl('index', ['planning' => $planning]) => EvaluationResource::getPluralModelLabel(),
            trans('filament-panels::resources/pages/view-record.breadcrumb'),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            ApproveAction::make(),
            ReopenAction::make(),
            EditAction::make(),
            ActionGroup::make([
                ExportEvaluationWordAction::make(),
                DeleteAction::make(),
            ])
                ->label('Thao tác')
                ->icon('heroicon-m-chevron-down')
                ->color('gray')
                ->button(),
        ];
    }
}
