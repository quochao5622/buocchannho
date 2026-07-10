<?php

namespace Quochao56\Acl\Policies;

use Quochao56\Core\Models\User;
use Spatie\Activitylog\Models\Activity;

class ActivityPolicy
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
        return $user->hasPermissionTo('activities.index');
    }

    public function view(User $user, Activity $record): bool
    {
        return $user->hasPermissionTo('activities.view');
    }
}
