<?php

namespace Quochao56\PlanningEvaluation\Filament\Resources\Evaluations\RelationManagers;

use Filament\Actions\ViewAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class EvaluationHistoryRelationManager extends RelationManager
{
    protected static string $relationship = 'histories';

    protected static ?string $title = null;

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return trans('packages.planning_evaluation::planning.history.title');
    }

    public function form(Schema $schema): Schema
    {
        return $schema;
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('created_at')
            ->columns([
                TextColumn::make('created_at')
                    ->label(trans('packages.planning_evaluation::planning.history.saved_at'))
                    ->dateTime('d/m/Y H:i:s')
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label(trans('packages.planning_evaluation::planning.history.saved_by'))
                    ->placeholder(trans('packages.planning_evaluation::planning.history.system'))
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                //
            ])
            ->actions([
                ViewAction::make()
                    ->label(trans('packages.planning_evaluation::planning.history.view_detail'))
                    ->modalHeading(trans('packages.planning_evaluation::planning.history.view_evaluation'))
                    ->modalContent(fn ($record) => view('planning-evaluation::filament.resources.evaluation-history-view', ['snapshot' => $record->snapshot]))
                    ->modalWidth('7xl')
                    ->slideOver(),
            ])
            ->bulkActions([
                //
            ])
            ->defaultSort('created_at', 'desc');
    }
}
