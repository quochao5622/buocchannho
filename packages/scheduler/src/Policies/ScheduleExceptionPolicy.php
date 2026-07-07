<?php

namespace Quochao56\Scheduler\Policies;

use Quochao56\Core\Models\User;
use Quochao56\Scheduler\Models\ScheduleException;

class ScheduleExceptionPolicy
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
        return $user->hasPermissionTo('schedules.index');
    }

    public function view(User $user, ScheduleException $record): bool
    {
        return $user->hasPermissionTo('schedules.show');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('schedules.edit');
    }

    public function update(User $user, ScheduleException $record): bool
    {
        return $user->hasPermissionTo('schedules.edit');
    }

    public function delete(User $user, ScheduleException $record): bool
    {
        return $user->hasPermissionTo('schedules.edit');
    }
}
