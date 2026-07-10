<?php

namespace Quochao56\Employee\Policies;

use Quochao56\Core\Models\User;
use Quochao56\Employee\Models\LeaveRequest;

class LeaveRequestPolicy
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
        return $user->hasPermissionTo('leave_requests.index');
    }

    public function view(User $user, LeaveRequest $record): bool
    {
        if ($user->hasPermissionTo('leave_requests.view_all')) {
            return true;
        }

        return $user->hasPermissionTo('leave_requests.index')
            && $user->employee
            && $user->employee->id === $record->employee_id;
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('leave_requests.create');
    }

    public function update(User $user, LeaveRequest $record): bool
    {
        if ($user->hasPermissionTo('leave_requests.edit')) {
            return true;
        }

        // Allow teachers to edit their own leave requests ONLY if they are still pending
        return $record->status === 'pending'
            && $user->employee
            && $user->employee->id === $record->employee_id;
    }

    public function delete(User $user, LeaveRequest $record): bool
    {
        if ($user->hasPermissionTo('leave_requests.destroy')) {
            return true;
        }

        // Allow teachers to delete their own leave requests if they are pending or approved
        return in_array($record->status, ['pending', 'approved'])
            && $user->employee
            && $user->employee->id === $record->employee_id;
    }

    public function approve(User $user): bool
    {
        return $user->hasPermissionTo('leave_requests.approve');
    }
}
