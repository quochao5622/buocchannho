<?php

namespace Quochao56\Scheduler\Filament\Resources\ClassroomResource\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ClassroomTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(trans('packages.scheduler::scheduler.classrooms.fields.name'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('status')
                    ->label(trans('packages.scheduler::scheduler.classrooms.fields.status'))
                    ->badge()
                    ->sortable(),

                TextColumn::make('description')
                    ->label(trans('packages.scheduler::scheduler.classrooms.fields.description'))
                    ->placeholder(trans('packages.scheduler::scheduler.classrooms.placeholders.empty'))
                    ->limit(50),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
