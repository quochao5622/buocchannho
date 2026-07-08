<?php

namespace Quochao56\Employee\Filament\Resources;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Quochao56\Employee\Filament\Resources\LeaveRequestResource\Pages\CreateLeaveRequest;
use Quochao56\Employee\Filament\Resources\LeaveRequestResource\Pages\EditLeaveRequest;
use Quochao56\Employee\Filament\Resources\LeaveRequestResource\Pages\ListLeaveRequests;
use Quochao56\Employee\Filament\Resources\LeaveRequestResource\Schemas\LeaveRequestForm;
use Quochao56\Employee\Filament\Resources\LeaveRequestResource\Tables\LeaveRequestTable;
use Quochao56\Employee\Models\LeaveRequest;

class LeaveRequestResource extends Resource
{
    protected static ?string $model = LeaveRequest::class;

    protected static ?int $navigationSort = 3;

    public static function getNavigationIcon(): string|\BackedEnum|Htmlable|null
    {
        return 'heroicon-o-calendar-days';
    }

    public static function getNavigationLabel(): string
    {
        return trans('packages.employee::leave_request.navigation_label');
    }

    public static function getModelLabel(): string
    {
        return trans('packages.employee::leave_request.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return trans('packages.employee::leave_request.plural_model_label');
    }

    public static function getNavigationGroup(): ?string
    {
        return trans('packages.employee::employee.navigation_group');
    }

    public static function getNavigationBadge(): ?string
    {
        $user = auth()->user();
        if (! $user) {
            return null;
        }

        $query = static::getModel()::query()->where('status', 'pending');

        if ($user->hasPermissionTo('leave_requests.view_all')) {
            $count = $query->count();
        } else {
            $employee = $user->employee;
            if ($employee) {
                $count = $query->where('employee_id', $employee->id)->count();
            } else {
                $count = 0;
            }
        }

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'warning';
    }

    public static function form(Schema $schema): Schema
    {
        return LeaveRequestForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LeaveRequestTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLeaveRequests::route('/'),
            'create' => CreateLeaveRequest::route('/create'),
            'edit' => EditLeaveRequest::route('/{record}/edit'),
        ];
    }
}
