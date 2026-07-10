<?php

namespace Quochao56\Employee\Filament\Resources\AttendanceCorrectionRequestResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Quochao56\Employee\Filament\Resources\AttendanceCorrectionRequestResource;
use Quochao56\Employee\Models\Employee;

class ListAttendanceCorrectionRequests extends ListRecords
{
    protected static string $resource = AttendanceCorrectionRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    protected function getTableQuery(): Builder
    {
        $query = parent::getTableQuery();
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
}
