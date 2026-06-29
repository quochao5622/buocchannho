<?php

namespace Quochao56\PlanningEvaluation\Filament\Resources\Evaluations;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Quochao56\Employee\Models\Employee;
use Quochao56\PlanningEvaluation\Filament\Resources\Evaluations\Pages\CreateEvaluation;
use Quochao56\PlanningEvaluation\Filament\Resources\Evaluations\Pages\EditEvaluation;
use Quochao56\PlanningEvaluation\Filament\Resources\Evaluations\Pages\ListEvaluations;
use Quochao56\PlanningEvaluation\Filament\Resources\Evaluations\Schemas\EvaluationForm;
use Quochao56\PlanningEvaluation\Filament\Resources\Evaluations\Tables\EvaluationsTable;
use Quochao56\PlanningEvaluation\Filament\Resources\Plannings\PlanningResource;
use Quochao56\PlanningEvaluation\Models\Evaluation;

class EvaluationResource extends Resource
{
    protected static ?string $model = Evaluation::class;

    protected static ?string $parentResource = PlanningResource::class;

    protected static ?int $navigationSort = 30;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    public static function getNavigationLabel(): string
    {
        return trans('packages.planning_evaluation::evaluation.navigation_label');
    }

    public static function getModelLabel(): string
    {
        return trans('packages.planning_evaluation::evaluation.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return trans('packages.planning_evaluation::evaluation.plural_model_label');
    }

    public static function getNavigationGroup(): ?string
    {
        return trans('packages.planning_evaluation::evaluation.navigation_group');
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        if (auth()->check() && (auth()->user()->isSuperAdmin() || auth()->user()->can('evaluations.view_all'))) {
            return $query;
        }

        if (auth()->check()) {
            $employee = Employee::where('email', auth()->user()->email)->first();
            if ($employee) {
                return $query->whereHas('planning.student.currentAssignment', function ($q) use ($employee) {
                    $q->where('employee_id', $employee->id);
                });
            }
        }

        return $query->whereRaw('1=0');
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
        return [
            RelationManagers\EvaluationHistoryRelationManager::class,
        ];
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
