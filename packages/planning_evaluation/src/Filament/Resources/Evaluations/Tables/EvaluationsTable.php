<?php

namespace Quochao56\PlanningEvaluation\Filament\Resources\Evaluations\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class EvaluationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(trans('planning-evaluation::evaluation.fields.name'))
                    ->searchable(),
                TextColumn::make('planning.name')
                    ->label(trans('planning-evaluation::evaluation.fields.planning_id'))
                    ->searchable(),
                TextColumn::make('status')
                    ->label(trans('planning-evaluation::evaluation.fields.status'))
                    ->badge(),
                TextColumn::make('updated_at')
                    ->label(trans('planning-evaluation::planning.fields.updated_at'))
                    ->dateTime(),
            ])
            ->actions([
                EditAction::make(),
                ViewAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
