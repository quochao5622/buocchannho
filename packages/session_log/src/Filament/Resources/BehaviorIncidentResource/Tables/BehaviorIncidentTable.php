<?php

namespace Quochao56\SessionLog\Filament\Resources\BehaviorIncidentResource\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

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
