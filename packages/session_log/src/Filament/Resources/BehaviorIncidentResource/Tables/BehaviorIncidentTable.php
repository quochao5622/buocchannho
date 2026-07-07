<?php

namespace Quochao56\SessionLog\Filament\Resources\BehaviorIncidentResource\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Quochao56\Employee\Models\Employee;
use Quochao56\SessionLog\Filament\Enums\BehaviorIntensityEnum;
use Quochao56\Student\Models\Student;

class BehaviorIncidentTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('student.name')
                    ->label(trans('packages.session_log::behavior_incident.fields.student_id'))
                    ->sortable()
                    ->searchable(),

                TextColumn::make('employee.name')
                    ->label(trans('packages.session_log::behavior_incident.fields.employee'))
                    ->sortable(),

                TextColumn::make('incident_date')
                    ->label(trans('packages.session_log::behavior_incident.fields.incident_date'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                TextColumn::make('intensity')
                    ->label(trans('packages.session_log::behavior_incident.fields.intensity'))
                    ->badge(),

                TextColumn::make('duration_minutes')
                    ->label(trans('packages.session_log::behavior_incident.fields.duration_minutes'))
                    ->suffix(' '.trans('packages.session_log::behavior_incident.units.minutes'))
                    ->sortable(),

                TextColumn::make('behavior')
                    ->label(trans('packages.session_log::behavior_incident.fields.behavior'))
                    ->limit(50),
            ])
            ->defaultSort('incident_date', 'desc')
            ->filters([
                SelectFilter::make('employee_id')
                    ->label(trans('packages.session_log::behavior_incident.fields.employee'))
                    ->searchable()
                    ->options(fn () => Employee::query()->pluck('name', 'id')->toArray()),
                SelectFilter::make('student_id')
                    ->label(trans('packages.session_log::behavior_incident.fields.student_id'))
                    ->searchable()
                    ->options(fn () => Student::query()->pluck('name', 'id')->toArray()),
                SelectFilter::make('intensity')
                    ->label(trans('packages.session_log::behavior_incident.fields.intensity'))
                    ->options(fn (): array => collect(BehaviorIntensityEnum::cases())
                        ->mapWithKeys(fn (BehaviorIntensityEnum $intensity): array => [
                            $intensity->value => $intensity->getLabel(),
                        ])
                        ->toArray()),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make()
                    ->modalHeading(trans('packages.session_log::behavior_incident.actions.delete.heading'))
                    ->modalDescription(trans('packages.session_log::behavior_incident.actions.delete.description'))
                    ->modalSubmitActionLabel(trans('packages.session_log::behavior_incident.actions.delete.submit'))
                    ->modalCancelActionLabel(trans('packages.session_log::behavior_incident.actions.delete.cancel')),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->modalHeading(trans('packages.session_log::behavior_incident.actions.bulk_delete.heading'))
                        ->modalDescription(trans('packages.session_log::behavior_incident.actions.bulk_delete.description')),
                ]),
            ]);
    }
}
