<?php

namespace Quochao56\Student\Filament\Resources;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Quochao56\Student\Filament\Resources\StudentLeaveRequestResource\Pages\CreateStudentLeaveRequest;
use Quochao56\Student\Filament\Resources\StudentLeaveRequestResource\Pages\EditStudentLeaveRequest;
use Quochao56\Student\Filament\Resources\StudentLeaveRequestResource\Pages\ListStudentLeaveRequests;
use Quochao56\Student\Filament\Resources\StudentLeaveRequestResource\Schemas\StudentLeaveRequestForm;
use Quochao56\Student\Filament\Resources\StudentLeaveRequestResource\Tables\StudentLeaveRequestTable;
use Quochao56\Student\Models\StudentLeaveRequest;

class StudentLeaveRequestResource extends Resource
{
    protected static ?string $model = StudentLeaveRequest::class;

    protected static ?int $navigationSort = 3;

    public static function getNavigationIcon(): string|\BackedEnum|Htmlable|null
    {
        return 'heroicon-o-calendar-days';
    }

    public static function getNavigationLabel(): string
    {
        return trans('packages.student::student_leave_request.navigation_label');
    }

    public static function getModelLabel(): string
    {
        return trans('packages.student::student_leave_request.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return trans('packages.student::student_leave_request.plural_model_label');
    }

    public static function getNavigationGroup(): ?string
    {
        return trans('packages.student::student_leave_request.navigation_group');
    }

    public static function getNavigationBadge(): ?string
    {
        $user = auth()->user();
        if (! $user) {
            return null;
        }

        $query = static::getModel()::query()->where('status', 'pending');

        if ($user->isSuperAdmin() || $user->hasPermissionTo('student_leave_requests.view_all')) {
            $count = $query->count();
        } else {
            $employee = $user->employee;
            if ($employee) {
                $count = $query->whereHas('student.assignments', function ($q) use ($employee) {
                    $q->where('employee_id', $employee->id)
                        ->where('status', 'active');
                })->count();
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
        return StudentLeaveRequestForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return StudentLeaveRequestTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListStudentLeaveRequests::route('/'),
            'create' => CreateStudentLeaveRequest::route('/create'),
            'edit' => EditStudentLeaveRequest::route('/{record}/edit'),
        ];
    }
}
