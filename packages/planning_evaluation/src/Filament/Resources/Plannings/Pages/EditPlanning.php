<?php

namespace Quochao56\PlanningEvaluation\Filament\Resources\Plannings\Pages;

use App\Enum\BaseStatusEnum;
use Filament\Actions\Action;
use Quochao56\PlanningEvaluation\Filament\Actions\ExportPlanningWordAction;
use Quochao56\PlanningEvaluation\Filament\Resources\Evaluations\EvaluationResource;
use Quochao56\PlanningEvaluation\Filament\Resources\Plannings\PlanningResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Quochao56\PlanningEvaluation\Models\Evaluation;

class EditPlanning extends EditRecord
{
    protected static string $resource = PlanningResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('evaluate')
                ->label('Đánh giá')
                ->color('warning')
                ->visible(fn (): bool => (($this->getRecord()->status?->value ?? $this->getRecord()->status) === BaseStatusEnum::Published->value))
                ->action(function (): void {
                    $evaluation = Evaluation::upsertFromPlanning($this->getRecord());

                    $this->redirect(EvaluationResource::getUrl('edit', [
                        'planning' => $this->getRecord(),
                        'record' => $evaluation,
                    ]));
                }),
            ExportPlanningWordAction::make(),
            DeleteAction::make(),
            $this->getSaveFormAction()
                ->submit(null)
                ->action(fn() => $this->save())
                ->keyBindings(['mod+s']),
            $this->getCancelFormAction(),
        ];
    }
}
