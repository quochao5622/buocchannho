<?php

namespace Quochao56\Scheduler\Filament\Resources;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Quochao56\Scheduler\Filament\Resources\AttendanceResource\Pages\CreateAttendance;
use Quochao56\Scheduler\Filament\Resources\AttendanceResource\Pages\EditAttendance;
use Quochao56\Scheduler\Filament\Resources\AttendanceResource\Pages\ListAttendances;
use Quochao56\Scheduler\Filament\Resources\AttendanceResource\Schemas\AttendanceForm;
use Quochao56\Scheduler\Filament\Resources\AttendanceResource\Tables\AttendanceTable;
use Quochao56\Scheduler\Models\Attendance;

class AttendanceResource extends Resource
{
    protected static ?string $model = Attendance::class;

    protected static ?int $navigationSort = 2;

    public static function getNavigationIcon(): string|\BackedEnum|Htmlable|null
    {
        return 'heroicon-o-check-circle';
    }

    public static function getNavigationGroup(): ?string
    {
        return trans('packages.scheduler::scheduler.navigation_group');
    }

    public static function getNavigationLabel(): string
    {
        return trans('packages.scheduler::scheduler.attendances.navigation_label');
    }

    public static function getModelLabel(): string
    {
        return trans('packages.scheduler::scheduler.attendances.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return trans('packages.scheduler::scheduler.attendances.plural_model_label');
    }

    public static function form(Schema $schema): Schema
    {
        return AttendanceForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AttendanceTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAttendances::route('/'),
            'create' => CreateAttendance::route('/create'),
            'edit' => EditAttendance::route('/{record}/edit'),
        ];
    }
}
