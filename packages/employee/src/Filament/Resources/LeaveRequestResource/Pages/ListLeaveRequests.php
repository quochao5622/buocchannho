<?php

namespace Quochao56\Employee\Filament\Resources\LeaveRequestResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Quochao56\Employee\Filament\Resources\LeaveRequestResource;
use Quochao56\Employee\Models\Employee;

class ListLeaveRequests extends ListRecords
{
    protected static string $resource = LeaveRequestResource::class;

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

        if ($user->isSuperAdmin() || $user->hasPermissionTo('leave_requests.view_all')) {
            return $query;
        }

        $employee = Employee::where('email', $user->email)->first();
        if ($employee) {
            return $query->where('employee_id', $employee->id);
        }

        return $query->whereRaw('1 = 0');
    }
}
