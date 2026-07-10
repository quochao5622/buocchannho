<?php

namespace Quochao56\Student\Policies;

use Quochao56\Core\Models\User;
use Quochao56\Student\Models\StudentLeaveRequest;

class StudentLeaveRequestPolicy
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
        return $user->hasPermissionTo('student_leave_requests.index');
    }

    public function view(User $user, StudentLeaveRequest $record): bool
    {
        return $user->hasPermissionTo('student_leave_requests.index');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('student_leave_requests.create');
    }

    public function update(User $user, StudentLeaveRequest $record): bool
    {
        return $user->hasPermissionTo('student_leave_requests.edit');
    }

    public function delete(User $user, StudentLeaveRequest $record): bool
    {
        return $user->hasPermissionTo('student_leave_requests.destroy');
    }
}
