<?php

namespace Quochao56\Student\Filament\Resources;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Quochao56\Employee\Models\Employee;
use Quochao56\PlanningEvaluation\Filament\Resources\StudentAssignmentRelationManager;
use Quochao56\Student\Filament\Resources\StudentResource\Pages\CreateStudent;
use Quochao56\Student\Filament\Resources\StudentResource\Pages\EditStudent;
use Quochao56\Student\Filament\Resources\StudentResource\Pages\ListStudents;
use Quochao56\Student\Filament\Resources\StudentResource\Schemas\StudentForm;
use Quochao56\Student\Filament\Resources\StudentResource\Tables\StudentTable;
use Quochao56\Student\Models\Student;

class StudentResource extends Resource
{
    protected static ?string $model = Student::class;

    protected static ?int $navigationSort = 2;

    public static function getNavigationIcon(): string|\BackedEnum|Htmlable|null
    {
        return 'heroicon-o-academic-cap';
    }

    public static function getNavigationLabel(): string
    {
        return trans('packages.student::student.navigation_label');
    }

    public static function getModelLabel(): string
    {
        return trans('packages.student::student.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return trans('packages.student::student.plural_model_label');
    }

    public static function getNavigationGroup(): ?string
    {
        return trans('packages.student::student.navigation_group');
    }

    public static function form(Schema $schema): Schema
    {
        return StudentForm::configure($schema);
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        if (auth()->check() && (auth()->user()->isSuperAdmin() || auth()->user()->can('students.view_all'))) {
            return $query;
        }

        if (auth()->check()) {
            $employee = Employee::where('email', auth()->user()->email)->first();
            if ($employee) {
                return $query->whereHas('currentAssignment', function ($q) use ($employee) {
                    $q->where('employee_id', $employee->id);
                });
            }
        }

        return $query->whereRaw('1=0');
    }

    public static function table(Table $table): Table
    {
        return StudentTable::configure($table);
    }

    public static function getRelations(): array
    {
        $relations = [];

        if (auth()->check() && auth()->user()->can('students.assign')) {
            $relations[] = StudentAssignmentRelationManager::class;
        }

        return $relations;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListStudents::route('/'),
            'create' => CreateStudent::route('/create'),
            'edit' => EditStudent::route('/{record}/edit'),
        ];
    }
}
