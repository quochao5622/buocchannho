<?php

namespace Quochao56\SessionLog\Policies;

use Quochao56\Core\Models\User;
use Quochao56\SessionLog\Models\BehaviorIncident;

class BehaviorIncidentPolicy
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
        return $user->hasPermissionTo('behavior_incidents.index');
    }

    public function view(User $user, BehaviorIncident $record): bool
    {
        return $user->hasPermissionTo('behavior_incidents.show');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('behavior_incidents.create');
    }

    public function update(User $user, BehaviorIncident $record): bool
    {
        return $user->hasPermissionTo('behavior_incidents.edit');
    }

    public function delete(User $user, BehaviorIncident $record): bool
    {
        return $user->hasPermissionTo('behavior_incidents.destroy');
    }
}
