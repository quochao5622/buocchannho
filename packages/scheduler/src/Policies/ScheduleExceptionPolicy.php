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
        return $user->hasPermissionTo('schedule_exceptions.index');
    }

    public function view(User $user, ScheduleException $record): bool
    {
        return $user->hasPermissionTo('schedule_exceptions.show');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('schedule_exceptions.create');
    }

    public function update(User $user, ScheduleException $record): bool
    {
        return $user->hasPermissionTo('schedule_exceptions.edit');
    }

    public function delete(User $user, ScheduleException $record): bool
    {
        return $user->hasPermissionTo('schedule_exceptions.destroy');
    }

    public function approve(User $user): bool
    {
        return $user->hasPermissionTo('schedule_exceptions.approve');
    }
}
