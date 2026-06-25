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
                ->label(trans('packages.planning_evaluation::planning.tracker.evaluation'))
                ->color('warning')
                ->visible(fn (): bool => (($this->getRecord()->status?->value ?? $this->getRecord()->status) === BaseStatusEnum::Published->value))
                ->action(function (): void {
                    $evaluation = Evaluation::upsertFromPlanning($this->getRecord());

                    $this->redirect(EvaluationResource::getUrl('edit', [
                        'planning' => $this->getRecord(),
                        'record' => $evaluation,
                    ]));
                }),
            Action::make('clone')
                ->label(trans('packages.planning_evaluation::planning.clone.label'))
                ->icon('heroicon-o-document-duplicate')
                ->color('info')
                ->form([
                    \Filament\Forms\Components\Select::make('student_id')
                        ->label(trans('packages.planning_evaluation::planning.clone.student'))
                        ->options(\Quochao56\Student\Models\Student::query()->pluck('name', 'id'))
                        ->required()
                        ->searchable(),
                    \Filament\Forms\Components\DatePicker::make('start_date')
                        ->label(trans('packages.planning_evaluation::planning.clone.start_date'))
                        ->native(false)
                        ->default(now())
                        ->displayFormat('d/m/Y')
                        ->required(),
                    \Filament\Forms\Components\DatePicker::make('end_date')
                        ->label(trans('packages.planning_evaluation::planning.clone.end_date'))
                        ->native(false)
                        ->default(now()->addMonths(3))
                        ->displayFormat('d/m/Y')
                        ->required(),
                ])
                ->action(function (array $data): void {
                    $record = $this->getRecord();
                    $cloned = $record->replicate();
                    $cloned->student_id = $data['student_id'];
                    $cloned->start_date = $data['start_date'];
                    $cloned->end_date = $data['end_date'];
                    $cloned->name = $record->name . trans('packages.planning_evaluation::planning.clone.suffix');
                    
                    $newStudent = \Quochao56\Student\Models\Student::find($data['student_id']);
                    $employeeId = null;
                    if (auth()->check()) {
                        $employeeId = \Quochao56\Employee\Models\Employee::where('email', auth()->user()->email)->first()?->id;
                    }
                    $cloned->employee_id = $newStudent?->currentAssignment?->employee_id 
                        ?? $employeeId 
                        ?? $record->employee_id;

                    $cloned->status = BaseStatusEnum::Draft;
                    $cloned->save();

                    \Filament\Notifications\Notification::make()
                        ->success()
                        ->title(trans('packages.planning_evaluation::planning.clone.success'))
                        ->send();

                    $this->redirect(PlanningResource::getUrl('edit', ['record' => $cloned]));
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
