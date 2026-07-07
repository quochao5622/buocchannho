<?php

namespace Quochao56\Scheduler\Filament\Resources;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Quochao56\Scheduler\Filament\Resources\ScheduleResource\Pages\CreateSchedule;
use Quochao56\Scheduler\Filament\Resources\ScheduleResource\Pages\EditSchedule;
use Quochao56\Scheduler\Filament\Resources\ScheduleResource\Pages\ListSchedules;
use Quochao56\Scheduler\Filament\Resources\ScheduleResource\RelationManagers\ExceptionsRelationManager;
use Quochao56\Scheduler\Filament\Resources\ScheduleResource\Schemas\ScheduleForm;
use Quochao56\Scheduler\Filament\Resources\ScheduleResource\Tables\ScheduleTable;
use Quochao56\Scheduler\Models\Schedule;

class ScheduleResource extends Resource
{
    protected static ?string $model = Schedule::class;

    protected static ?int $navigationSort = 1;

    public static function getNavigationIcon(): string|\BackedEnum|Htmlable|null
    {
        return 'heroicon-o-calendar';
    }

    public static function getNavigationGroup(): ?string
    {
        return trans('packages.scheduler::scheduler.navigation_group');
    }

    public static function getNavigationLabel(): string
    {
        return trans('packages.scheduler::scheduler.schedules.navigation_label');
    }

    public static function getModelLabel(): string
    {
        return trans('packages.scheduler::scheduler.schedules.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return trans('packages.scheduler::scheduler.schedules.plural_model_label');
    }

    public static function form(Schema $schema): Schema
    {
        return ScheduleForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ScheduleTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            ExceptionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSchedules::route('/'),
            'create' => CreateSchedule::route('/create'),
            'edit' => EditSchedule::route('/{record}/edit'),
        ];
    }
}
