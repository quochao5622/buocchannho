<?php

namespace App\Policies;

use App\Models\User;
use Quochao56\PlanningEvaluation\Models\Evaluation;

class EvaluationPolicy
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

    public function view(User $user, Evaluation $record): bool
    {
        return $user->hasPermissionTo('manage_plans_evaluations');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('manage_plans_evaluations');
    }

    public function update(User $user, Evaluation $record): bool
    {
        return $user->hasPermissionTo('manage_plans_evaluations');
    }

    public function delete(User $user, Evaluation $record): bool
    {
        return $user->hasPermissionTo('manage_plans_evaluations');
    }
}
