<?php

namespace Quochao56\Scheduler\Filament\Resources;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Quochao56\Employee\Models\Employee;
use Quochao56\Scheduler\Filament\Resources\ScheduleResource\Pages\CreateSchedule;
use Quochao56\Scheduler\Filament\Resources\ScheduleResource\Pages\EditSchedule;
use Quochao56\Scheduler\Filament\Resources\ScheduleResource\Pages\ListSchedules;
use Quochao56\Scheduler\Filament\Resources\ScheduleResource\Pages\ViewSchedule;
use Quochao56\Scheduler\Filament\Resources\ScheduleResource\RelationManagers\ExceptionsRelationManager;
use Quochao56\Scheduler\Filament\Resources\ScheduleResource\Schemas\ScheduleForm;
use Quochao56\Scheduler\Filament\Resources\ScheduleResource\Tables\ScheduleTable;
use Quochao56\Scheduler\Models\Schedule;
use Quochao56\Scheduler\Models\ScheduleException;

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

    public static function getNavigationBadge(): ?string
    {
        $user = auth()->user();
        if (! $user?->hasPermissionTo('schedule_exceptions.approve')) {
            return null;
        }

        $count = ScheduleException::pending()->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'warning';
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
            'view' => ViewSchedule::route('/{record}'),
            'edit' => EditSchedule::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = Auth::user();

        if (! $user) {
            return $query->whereKey(-1);
        }

        $canViewAll = $user->isSuperAdmin() || $user->hasPermissionTo('schedules.view_all');

        if (! $canViewAll) {
            $employee = $user->employee ?? Employee::where('email', $user->email)->first();
            if ($employee) {
                $employeeId = (int) $employee->id;

                $query->where(function (Builder $builder) use ($employeeId) {
                    $builder->where('employee_id', $employeeId)
                        ->orWhereHas('exceptions', function (Builder $sub) use ($employeeId) {
                            $sub->where('action', 'substitute')
                                ->where('new_employee_id', $employeeId);
                        });
                });
            } else {
                return $query->whereKey(-1);
            }
        }

        return $query;
    }
}
