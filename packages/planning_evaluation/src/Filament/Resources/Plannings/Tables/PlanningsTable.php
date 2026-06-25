<?php

namespace Quochao56\PlanningEvaluation\Filament\Resources\Plannings\Tables;

use App\Enum\BaseStatusEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Quochao56\PlanningEvaluation\Filament\Resources\Evaluations\EvaluationResource;
use Quochao56\PlanningEvaluation\Models\Evaluation;
use Quochao56\PlanningEvaluation\Models\Planning;

class PlanningsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label(trans('packages.planning_evaluation::planning.fields.name'))->searchable(),
                TextColumn::make('employee.name')
                    ->label(trans('packages.planning_evaluation::planning.fields.employee'))
                    ->searchable()
                    ->formatStateUsing(fn ($state) => $state ?: '-'),
                TextColumn::make('student.name')
                    ->label(trans('packages.planning_evaluation::planning.fields.student'))
                    ->searchable()
                    ->formatStateUsing(fn ($state) => $state ?: '-'),
                TextColumn::make('start_date')->label(trans('packages.planning_evaluation::planning.fields.start_date'))->date('d/m/Y'),
                TextColumn::make('end_date')->label(trans('packages.planning_evaluation::planning.fields.end_date'))->date('d/m/Y'),
                TextColumn::make('status')->label(trans('packages.planning_evaluation::planning.fields.status'))
                ->badge(),
                TextColumn::make('created_at')->label(trans('packages.planning_evaluation::planning.fields.created_at'))->dateTime(),
                TextColumn::make('updated_at')->label(trans('packages.planning_evaluation::planning.fields.updated_at'))->dateTime(),
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('managing_teacher')
                    ->label(trans('packages.planning_evaluation::planning.tracker.managing_teacher'))
                    ->options(\Quochao56\Employee\Models\Employee::query()->where('status', BaseStatusEnum::Active->value ?? BaseStatusEnum::Active)->pluck('name', 'id'))
                    ->query(function ($query, array $data) {
                        if (empty($data['value'])) {
                            return;
                        }
                        $query->whereHas('student.currentAssignment', function ($q) use ($data) {
                            $q->where('employee_id', $data['value']);
                        });
                    }),
            ])
            ->actions([
                Action::make('evaluate')
                    ->label(trans('packages.planning_evaluation::planning.tracker.evaluation'))
                    ->color('warning')
                    ->visible(fn (Planning $record): bool => (($record->status?->value ?? $record->status) === BaseStatusEnum::Published->value))
                    ->action(function (Planning $record) {
                        $evaluation = Evaluation::upsertFromPlanning($record);

                        return redirect(EvaluationResource::getUrl('edit', [
                            'planning' => $record,
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
                    ->action(function (Planning $record, array $data): void {
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
                    }),
                EditAction::make(),
                ViewAction::make()
                    ->modalWidth('90%'),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                ])
            ]);
    }
}
