<?php

namespace Quochao56\Employee\Filament\Resources;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Quochao56\Employee\Filament\Resources\EmployeeResource\Pages\CreateEmployee;
use Quochao56\Employee\Filament\Resources\EmployeeResource\Pages\EditEmployee;
use Quochao56\Employee\Filament\Resources\EmployeeResource\Pages\ListEmployees;
use Quochao56\Employee\Filament\Resources\EmployeeResource\Schemas\EmployeeForm;
use Quochao56\Employee\Filament\Resources\EmployeeResource\Tables\EmployeeTable;
use Quochao56\Employee\Models\Employee;
use Quochao56\PlanningEvaluation\Filament\Resources\StudentsRelationManager;

class EmployeeResource extends Resource
{
    protected static ?string $model = Employee::class;

    protected static ?int $navigationSort = 1;

    public static function getNavigationIcon(): string|\BackedEnum|Htmlable|null
    {
        return 'heroicon-o-user-group';
    }

    public static function getNavigationLabel(): string
    {
        return trans('packages.employee::employee.navigation_label');
    }

    public static function getModelLabel(): string
    {
        return trans('packages.employee::employee.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return trans('packages.employee::employee.plural_model_label');
    }

    public static function getNavigationGroup(): ?string
    {
        return trans('packages.employee::employee.navigation_group');
    }

    public static function form(Schema $schema): Schema
    {
        return EmployeeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EmployeeTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            StudentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEmployees::route('/'),
            'create' => CreateEmployee::route('/create'),
            'edit' => EditEmployee::route('/{record}/edit'),
        ];
    }
}
