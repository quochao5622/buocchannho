<?php

namespace Quochao56\Student\Filament\Resources\StudentResource\Pages;

use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Quochao56\Core\Traits\HasAutoSave;
use Quochao56\PlanningEvaluation\Filament\Resources\Plannings\PlanningResource;
use Quochao56\Student\Filament\Resources\StudentResource;

class EditStudent extends EditRecord
{
    use HasAutoSave;

    protected static string $resource = StudentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('create_planning')
                ->label(trans('packages.planning_evaluation::planning.actions.create_plan'))
                ->icon('heroicon-o-document-plus')
                ->color('success')
                ->url(fn (): string => PlanningResource::getUrl('create', [
                    'student_id' => $this->getRecord()->id,
                ])),
            DeleteAction::make(),
        ];
    }
}
