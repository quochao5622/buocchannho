<?php

namespace App\Policies;

use App\Models\User;
use Quochao56\PlanningEvaluation\Models\Planning;

class PlanningPolicy
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
        return $user->hasPermissionTo('manage_plans_evaluations');
    }

    public function view(User $user, Planning $record): bool
    {
        return $user->hasPermissionTo('manage_plans_evaluations');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('manage_plans_evaluations');
    }

    public function update(User $user, Planning $record): bool
    {
        return $user->hasPermissionTo('manage_plans_evaluations');
    }

    public function delete(User $user, Planning $record): bool
    {
        return $user->hasPermissionTo('manage_plans_evaluations');
    }
}
