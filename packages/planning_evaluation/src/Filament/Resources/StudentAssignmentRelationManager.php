<?php

namespace Quochao56\PlanningEvaluation\Filament\Resources;

use Illuminate\Database\Eloquent\Model;
use Quochao56\Employee\Models\Employee;
use App\Enum\BaseStatusEnum;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Actions\CreateAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class StudentAssignmentRelationManager extends RelationManager
{
    protected static string $relationship = 'assignments';

    protected static ?string $title = null;

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return trans('packages.planning_evaluation::planning.assignment.title');
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('employee_id')
                ->label(trans('packages.planning_evaluation::planning.assignment.teacher'))
                ->options(Employee::query()->pluck('name', 'id'))
                ->required()
                ->searchable(),
            DateTimePicker::make('assigned_at')
                ->label(trans('packages.planning_evaluation::planning.assignment.assigned_at'))
                ->required(),
            DateTimePicker::make('unassigned_at')
                ->label(trans('packages.planning_evaluation::planning.assignment.unassigned_at')),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                TextColumn::make('employee.name')
                    ->label(trans('packages.planning_evaluation::planning.assignment.teacher'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('assigned_at')
                    ->label(trans('packages.planning_evaluation::planning.assignment.assigned_at'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                TextColumn::make('unassigned_at')
                    ->label(trans('packages.planning_evaluation::planning.assignment.unassigned_at'))
                    ->dateTime('d/m/Y H:i')
                    ->formatStateUsing(fn ($state) => $state ? $state->format('d/m/Y H:i') : trans('packages.planning_evaluation::planning.assignment.active'))
                    ->badge()
                    ->color(fn ($state) => $state ? 'gray' : 'success')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make()
                    ->label(trans('packages.planning_evaluation::planning.assignment.assign_new'))
                    ->modalHeading(trans('packages.planning_evaluation::planning.assignment.assign_heading'))
                    ->modalWidth('md')
                    ->form([
                        Select::make('employee_id')
                            ->label(trans('packages.planning_evaluation::planning.assignment.teacher'))
                            ->options(Employee::query()->where('status', BaseStatusEnum::Active->value ?? BaseStatusEnum::Active)->pluck('name', 'id'))
                            ->required()
                            ->searchable(),
                        DateTimePicker::make('assigned_at')
                            ->label(trans('packages.planning_evaluation::planning.assignment.assign_date'))
                            ->default(now())
                            ->native(false)
                            ->displayFormat('d/m/Y H:i')
                            ->required(),
                    ])
                    ->action(function (array $data): void {
                        $student = $this->getOwnerRecord();

                        // 1. Close current active assignment
                        $student->assignments()
                            ->whereNull('unassigned_at')
                            ->update(['unassigned_at' => $data['assigned_at']]);

                        // 2. Create new assignment
                        $student->assignments()->create([
                            'employee_id' => $data['employee_id'],
                            'assigned_at' => $data['assigned_at'],
                        ]);
                    }),
            ])
            ->actions([
                // Read-only logs
            ])
            ->bulkActions([
                //
            ])
            ->defaultSort('assigned_at', 'desc');
    }
}
