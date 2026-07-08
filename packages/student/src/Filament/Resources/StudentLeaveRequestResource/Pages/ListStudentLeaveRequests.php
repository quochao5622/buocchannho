<?php

namespace Quochao56\Student\Filament\Resources\StudentLeaveRequestResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Quochao56\Student\Filament\Resources\StudentLeaveRequestResource;

class ListStudentLeaveRequests extends ListRecords
{
    protected static string $resource = StudentLeaveRequestResource::class;

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

        if ($user->hasPermissionTo('student_leave_requests.view_all')) {
            return $query;
        }

        $employee = $user->employee;
        if ($employee) {
            return $query->whereHas('student.assignments', function ($q) use ($employee) {
                $q->where('employee_id', $employee->id)
                    ->where('status', 'active');
            });
        }

        return $query->whereRaw('1 = 0');
    }
}
