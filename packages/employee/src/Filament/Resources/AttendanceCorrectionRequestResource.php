<?php

namespace Quochao56\Employee\Filament\Resources;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Quochao56\Employee\Filament\Resources\AttendanceCorrectionRequestResource\Pages\CreateAttendanceCorrectionRequest;
use Quochao56\Employee\Filament\Resources\AttendanceCorrectionRequestResource\Pages\EditAttendanceCorrectionRequest;
use Quochao56\Employee\Filament\Resources\AttendanceCorrectionRequestResource\Pages\ListAttendanceCorrectionRequests;
use Quochao56\Employee\Filament\Resources\AttendanceCorrectionRequestResource\Schemas\AttendanceCorrectionRequestForm;
use Quochao56\Employee\Filament\Resources\AttendanceCorrectionRequestResource\Tables\AttendanceCorrectionRequestTable;
use Quochao56\Employee\Models\AttendanceCorrectionRequest;
use Quochao56\Employee\Models\Employee;

class AttendanceCorrectionRequestResource extends Resource
{
    protected static ?string $model = AttendanceCorrectionRequest::class;

    protected static ?int $navigationSort = 3;

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();

        if (! $user) {
            return $query->whereKey(-1);
        }

        if ($user->isSuperAdmin() || $user->hasPermissionTo('attendance_correction_requests.view_all')) {
            return $query;
        }

        $employee = Employee::where('email', $user->email)->first();
        if ($employee) {
            return $query->where('employee_id', $employee->id);
        }

        return $query->whereKey(-1);
    }

    public static function getNavigationIcon(): string|Htmlable|null
    {
        return 'heroicon-o-pencil-square';
    }

    public static function getNavigationLabel(): string
    {
        return trans('packages.employee::attendance_correction_request.navigation_label');
    }

    public static function getModelLabel(): string
    {
        return trans('packages.employee::attendance_correction_request.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return trans('packages.employee::attendance_correction_request.plural_model_label');
    }

    public static function getNavigationGroup(): ?string
    {
        return trans('packages.employee::employee.navigation_group');
    }

    public static function getNavigationBadge(): ?string
    {
        $user = auth()->user();
        if (! $user?->hasPermissionTo('attendance_correction_requests.approve')) {
            return null;
        }

        $count = AttendanceCorrectionRequest::pending()->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'warning';
    }

    public static function form(Schema $schema): Schema
    {
        return AttendanceCorrectionRequestForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AttendanceCorrectionRequestTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAttendanceCorrectionRequests::route('/'),
            'create' => CreateAttendanceCorrectionRequest::route('/create'),
            'edit' => EditAttendanceCorrectionRequest::route('/{record}/edit'),
        ];
    }
}
