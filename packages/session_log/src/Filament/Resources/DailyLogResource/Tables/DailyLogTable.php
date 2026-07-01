<?php

namespace Quochao56\SessionLog\Filament\Resources\DailyLogResource\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class DailyLogTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('student.name')
                    ->label(trans('packages.session_log::daily_log.fields.student_id'))
                    ->sortable()
                    ->searchable(),

                TextColumn::make('employee.name')
                    ->label(trans('packages.session_log::daily_log.fields.employee'))
                    ->sortable(),

                TextColumn::make('log_date')
                    ->label(trans('packages.session_log::daily_log.fields.log_date'))
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('emotion')
                    ->label(trans('packages.session_log::daily_log.fields.emotion'))
                    ->badge(),

                TextColumn::make('focus_level')
                    ->label(trans('packages.session_log::daily_log.fields.focus_level_short'))
                    ->badge(),

                TextColumn::make('cooperation_level')
                    ->label(trans('packages.session_log::daily_log.fields.cooperation_level_short'))
                    ->badge(),

                TextColumn::make('status')
                    ->label(trans('packages.session_log::daily_log.fields.status'))
                    ->badge(),
            ])
            ->defaultSort('log_date', 'desc')
            ->actions([
                EditAction::make(),
                DeleteAction::make()
                    ->modalHeading(trans('packages.session_log::daily_log.actions.delete.heading'))
                    ->modalDescription(trans('packages.session_log::daily_log.actions.delete.description'))
                    ->modalSubmitActionLabel(trans('packages.session_log::daily_log.actions.delete.submit'))
                    ->modalCancelActionLabel(trans('packages.session_log::daily_log.actions.delete.cancel')),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->modalHeading(trans('packages.session_log::daily_log.actions.bulk_delete.heading'))
                        ->modalDescription(trans('packages.session_log::daily_log.actions.bulk_delete.description')),
                ]),
            ]);
    }
}
