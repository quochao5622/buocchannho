<?php

namespace Quochao56\SessionLog\Policies;

use Quochao56\Core\Models\User;
use Quochao56\SessionLog\Models\DailyLog;

class DailyLogPolicy
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
        return $user->hasPermissionTo('daily_logs.index');
    }

    public function view(User $user, DailyLog $record): bool
    {
        return $user->hasPermissionTo('daily_logs.show');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('daily_logs.create');
    }

    public function update(User $user, DailyLog $record): bool
    {
        return $user->hasPermissionTo('daily_logs.edit');
    }

    public function delete(User $user, DailyLog $record): bool
    {
        return $user->hasPermissionTo('daily_logs.destroy');
    }
}
