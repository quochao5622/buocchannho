<?php

namespace Quochao56\Student\Filament\Resources\StudentResource\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup as ActionsBulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use Quochao56\Core\Enum\BaseStatusEnum;
use Quochao56\Employee\Models\Employee;
use Quochao56\PlanningEvaluation\Filament\Resources\Plannings\PlanningResource;
use Quochao56\Student\Models\Student;

class StudentTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('avatar')
                    ->label(trans('packages.student::student.fields.avatar'))
                    ->circular(),

                TextColumn::make('name')
                    ->label(trans('packages.student::student.fields.name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('gender')
                    ->label(trans('packages.student::student.fields.gender'))
                    ->formatStateUsing(fn (?string $state): string => trans('packages.core::core.gender.'.$state ?? 'male')),

                TextColumn::make('dob')
                    ->label(trans('packages.student::student.fields.dob'))
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('nickname')
                    ->label(trans('packages.student::student.fields.nickname'))
                    ->searchable(),

                TextColumn::make('currentTeacher.name')
                    ->label('Giáo viên phụ trách')
                    ->placeholder('Chưa gán'),

                TextColumn::make('father_name')
                    ->label(trans('packages.student::student.fields.father_name'))
                    ->searchable(),

                TextColumn::make('father_phone')
                    ->label(trans('packages.student::student.fields.father_phone')),

                TextColumn::make('mother_name')
                    ->label(trans('packages.student::student.fields.mother_name'))
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('mother_phone')
                    ->label(trans('packages.student::student.fields.mother_phone'))
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('status')
                    ->label(trans('packages.student::student.fields.status'))
                    ->badge(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(trans('packages.student::student.fields.status'))
                    ->options([
                        BaseStatusEnum::Active->value => BaseStatusEnum::Active->getLabel(),
                        BaseStatusEnum::Inactive->value => BaseStatusEnum::Inactive->getLabel(),
                    ]),
            ])
            ->actions([
                Action::make('create_planning')
                    ->label(trans('packages.planning_evaluation::planning.actions.create_plan'))
                    ->icon('heroicon-o-document-plus')
                    ->color('success')
                    ->url(fn (Student $record) => PlanningResource::getUrl('create', [
                        'student_id' => $record->id,
                    ])),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                ActionsBulkActionGroup::make([
                    BulkAction::make('assign_teacher')
                        ->label(trans('packages.planning_evaluation::planning.assignment.assign_teacher_bulk'))
                        ->icon('heroicon-o-user-plus')
                        ->visible(fn () => auth()->check() && auth()->user()->can('students.assign'))
                        ->form([
                            Select::make('employee_id')
                                ->label(trans('packages.planning_evaluation::planning.assignment.teacher'))
                                ->options(Employee::query()->where('status', BaseStatusEnum::Active->value ?? BaseStatusEnum::Active)->pluck('name', 'id'))
                                ->required()
                                ->searchable(),
                            DatePicker::make('assigned_at')
                                ->label(trans('packages.planning_evaluation::planning.assignment.assign_date'))
                                ->default(now())
                                ->native(false)
                                ->displayFormat('d/m/Y')
                                ->required(),
                        ])
                        ->action(function (Collection $records, array $data): void {
                            foreach ($records as $student) {
                                // 1. Close current active assignment
                                $student->assignments()
                                    ->whereNull('unassigned_at')
                                    ->update(['unassigned_at' => $data['assigned_at']]);

                                // 2. Create new assignment
                                $student->assignments()->create([
                                    'employee_id' => $data['employee_id'],
                                    'assigned_at' => $data['assigned_at'],
                                ]);
                            }
                        })
                        ->deselectRecordsAfterCompletion(),
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
