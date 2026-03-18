<?php

namespace Quochao56\PlanningEvaluation\Filament\Resources\Plannings;

use Quochao56\PlanningEvaluation\Filament\Resources\Plannings\Pages\CreatePlanning;
use Quochao56\PlanningEvaluation\Filament\Resources\Plannings\Pages\EditPlanning;
use Quochao56\PlanningEvaluation\Filament\Resources\Plannings\Pages\ListPlannings;
use Quochao56\PlanningEvaluation\Filament\Resources\Plannings\RelationManagers\EvaluationRelationManager;
use Quochao56\PlanningEvaluation\Filament\Resources\Plannings\Schemas\PlanningForm;
use Quochao56\PlanningEvaluation\Filament\Resources\Plannings\Tables\PlanningsTable;
use Quochao56\PlanningEvaluation\Models\Planning;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PlanningResource extends Resource
{
    protected static ?string $model = Planning::class;
    protected static ?int $navigationSort = 2;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'Planning';

    public static function getNavigationIcon(): string|\BackedEnum|\Illuminate\Contracts\Support\Htmlable|null
    {
        return static::$navigationIcon;;
    }

    public static function getNavigationLabel(): string
    {
        return trans('planning-evaluation::planning.navigation_label');
    }

    public static function getModelLabel(): string
    {
        return trans('planning-evaluation::planning.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return trans('planning-evaluation::planning.plural_model_label');
    }

    public static function getNavigationGroup(): ?string
    {
        return trans('planning-evaluation::planning.navigation_group');
    }
    public static function form(Schema $schema): Schema
    {
        return PlanningForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PlanningsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            EvaluationRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPlannings::route('/'),
            'create' => CreatePlanning::route('/create'),
            'edit' => EditPlanning::route('/{record}/edit'),
        ];
    }
}
