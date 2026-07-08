<?php

namespace Quochao56\Employee\Filament\Resources;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Quochao56\Employee\Filament\Resources\EmployeeAttendanceResource\Pages\CreateEmployeeAttendance;
use Quochao56\Employee\Filament\Resources\EmployeeAttendanceResource\Pages\EditEmployeeAttendance;
use Quochao56\Employee\Filament\Resources\EmployeeAttendanceResource\Pages\ListEmployeeAttendances;
use Quochao56\Employee\Filament\Resources\EmployeeAttendanceResource\Schemas\EmployeeAttendanceForm;
use Quochao56\Employee\Filament\Resources\EmployeeAttendanceResource\Tables\EmployeeAttendanceTable;
use Quochao56\Employee\Models\EmployeeAttendance;

class EmployeeAttendanceResource extends Resource
{
    protected static ?string $model = EmployeeAttendance::class;

    protected static ?int $navigationSort = 2;

    public static function getNavigationIcon(): string|\BackedEnum|Htmlable|null
    {
        return 'heroicon-o-clock';
    }

    public static function getNavigationLabel(): string
    {
        return trans('packages.employee::employee_attendance.navigation_label');
    }

    public static function getModelLabel(): string
    {
        return trans('packages.employee::employee_attendance.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return trans('packages.employee::employee_attendance.plural_model_label');
    }

    public static function getNavigationGroup(): ?string
    {
        return trans('packages.employee::employee.navigation_group');
    }

    public static function form(Schema $schema): Schema
    {
        return EmployeeAttendanceForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EmployeeAttendanceTable::configure($table);
    }

    public static function getNavigationBadge(): ?string
    {
        $user = auth()->user();
        if (! $user) {
            return null;
        }

        if (! $user->can('approveFlaggedLocation', EmployeeAttendance::class)) {
            return null;
        }

        $count = EmployeeAttendance::where('verification_status', 'pending')
            ->where('flagged_location', true)
            ->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEmployeeAttendances::route('/'),
            'create' => CreateEmployeeAttendance::route('/create'),
            'edit' => EditEmployeeAttendance::route('/{record}/edit'),
        ];
    }
}
