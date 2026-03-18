<?php

namespace Quochao56\PlanningEvaluation\Filament\Resources\Evaluations;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Quochao56\PlanningEvaluation\Filament\Resources\Evaluations\Pages\CreateEvaluation;
use Quochao56\PlanningEvaluation\Filament\Resources\Evaluations\Pages\EditEvaluation;
use Quochao56\PlanningEvaluation\Filament\Resources\Evaluations\Pages\ListEvaluations;
use Quochao56\PlanningEvaluation\Filament\Resources\Plannings\PlanningResource;
use Quochao56\PlanningEvaluation\Filament\Resources\Evaluations\Schemas\EvaluationForm;
use Quochao56\PlanningEvaluation\Filament\Resources\Evaluations\Tables\EvaluationsTable;
use Quochao56\PlanningEvaluation\Models\Evaluation;

class EvaluationResource extends Resource
{
    protected static ?string $model = Evaluation::class;

    protected static ?string $parentResource = PlanningResource::class;

    protected static ?int $navigationSort = 3;

    protected static string | \BackedEnum | null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    public static function getNavigationLabel(): string
    {
        return trans('planning-evaluation::evaluation.navigation_label');
    }

    public static function getModelLabel(): string
    {
        return trans('planning-evaluation::evaluation.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return trans('planning-evaluation::evaluation.plural_model_label');
    }

    public static function getNavigationGroup(): ?string
    {
        return trans('planning-evaluation::evaluation.navigation_group');
    }

    public static function form(Schema $schema): Schema
    {
        return EvaluationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EvaluationsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEvaluations::route('/'),
            'create' => CreateEvaluation::route('/create'),
            'edit' => EditEvaluation::route('/{record}/edit'),
        ];
    }
}
