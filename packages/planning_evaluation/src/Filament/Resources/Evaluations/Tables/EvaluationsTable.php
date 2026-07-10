<?php

namespace Quochao56\PlanningEvaluation\Filament\Resources\Evaluations\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Quochao56\PlanningEvaluation\Filament\Actions\ApproveAction;

class EvaluationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(trans('packages.planning_evaluation::evaluation.fields.name'))
                    ->searchable(),
                TextColumn::make('planning.name')
                    ->label(trans('packages.planning_evaluation::evaluation.fields.planning_id'))
                    ->searchable(),
                TextColumn::make('status')
                    ->label(trans('packages.planning_evaluation::evaluation.fields.status'))
                    ->badge(),
                TextColumn::make('updated_at')
                    ->label(trans('packages.planning_evaluation::planning.fields.updated_at'))
                    ->dateTime(),
            ])
            ->actions([
                ApproveAction::make(),
                EditAction::make(),
                ViewAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
