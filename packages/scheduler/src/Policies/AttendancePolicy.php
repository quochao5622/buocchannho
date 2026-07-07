<?php

namespace Quochao56\Scheduler\Policies;

use Quochao56\Core\Models\User;
use Quochao56\Scheduler\Models\Attendance;

class AttendancePolicy
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
        return $user->hasPermissionTo('attendances.index');
    }

    public function view(User $user, Attendance $record): bool
    {
        return $user->hasPermissionTo('attendances.show');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('attendances.create');
    }

    public function update(User $user, Attendance $record): bool
    {
        return $user->hasPermissionTo('attendances.edit');
    }

    public function delete(User $user, Attendance $record): bool
    {
        return $user->hasPermissionTo('attendances.destroy');
    }
}
