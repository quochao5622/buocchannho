<?php

namespace Quochao56\Employee\Policies;

use Quochao56\Core\Models\User;
use Quochao56\Employee\Models\EmployeeAttendance;

class EmployeeAttendancePolicy
{
    public function before(User $user, string $ability): ?bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('employee_attendances.index');
    }

    public function view(User $user, EmployeeAttendance $record): bool
    {
        if ($user->hasPermissionTo('employee_attendances.view_all')) {
            return true;
        }

        return $user->hasPermissionTo('employee_attendances.index')
            && $user->employee
            && $user->employee->id === $record->employee_id;
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('employee_attendances.create');
    }

    public function update(User $user, EmployeeAttendance $record): bool
    {
        return $user->hasPermissionTo('employee_attendances.edit');
    }

    public function delete(User $user, EmployeeAttendance $record): bool
    {
        return $user->hasPermissionTo('employee_attendances.destroy');
    }

    public function approveFlaggedLocation(User $user): bool
    {
        return $user->hasPermissionTo('employee_attendances.manage');
    }

    public function rejectFlaggedLocation(User $user): bool
    {
        return $user->hasPermissionTo('employee_attendances.manage');
    }
}
