<?php

namespace Quochao56\PlanningEvaluation\Filament\Resources\Plannings\Pages;

use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Quochao56\Core\Enum\BaseStatusEnum;
use Quochao56\Core\Traits\HasAutoSave;
use Quochao56\Employee\Models\Employee;
use Quochao56\PlanningEvaluation\Filament\Actions\ApproveAction;
use Quochao56\PlanningEvaluation\Filament\Actions\ExportPlanningWordAction;
use Quochao56\PlanningEvaluation\Filament\Resources\Evaluations\EvaluationResource;
use Quochao56\PlanningEvaluation\Filament\Resources\Plannings\PlanningResource;
use Quochao56\PlanningEvaluation\Models\Evaluation;
use Quochao56\Student\Models\Student;

class EditPlanning extends EditRecord
{
    use HasAutoSave;

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
            ApproveAction::make(),
            ActionGroup::make([
                Action::make('clone')
                    ->label(trans('packages.planning_evaluation::planning.clone.label'))
                    ->icon('heroicon-o-document-duplicate')
                    ->form([
                        Select::make('student_id')
                            ->label(trans('packages.planning_evaluation::planning.clone.student'))
                            ->options(Student::query()->pluck('name', 'id'))
                            ->required()
                            ->searchable(),
                        DatePicker::make('start_date')
                            ->label(trans('packages.planning_evaluation::planning.clone.start_date'))
                            ->native(false)
                            ->default(now())
                            ->displayFormat('d/m/Y')
                            ->required(),
                        DatePicker::make('end_date')
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
                        $cloned->name = $record->name.trans('packages.planning_evaluation::planning.clone.suffix');

                        $newStudent = Student::find($data['student_id']);
                        $employeeId = null;
                        if (auth()->check()) {
                            $employeeId = Employee::where('email', auth()->user()->email)->first()?->id;
                        }
                        $cloned->employee_id = $newStudent?->currentAssignment?->employee_id
                            ?? $employeeId
                            ?? $record->employee_id;

                        $cloned->status = BaseStatusEnum::Draft;
                        $cloned->save();

                        Notification::make()
                            ->success()
                            ->title(trans('packages.planning_evaluation::planning.clone.success'))
                            ->send();

                        $this->redirect(PlanningResource::getUrl('edit', ['record' => $cloned]));
                    }),
                ExportPlanningWordAction::make(),
                DeleteAction::make(),
            ])
                ->label('Thao tác')
                ->icon('heroicon-m-chevron-down')
                ->color('gray')
                ->button(),
            $this->getSaveFormAction()
                ->submit(null)
                ->action(fn () => $this->save())
                ->keyBindings(['mod+s']),
            $this->getCancelFormAction(),
        ];
    }
}
