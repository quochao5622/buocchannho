<?php

namespace Quochao56\PlanningEvaluation\Policies;

use Quochao56\Core\Enum\BaseStatusEnum;
use Quochao56\Core\Models\User;
use Quochao56\PlanningEvaluation\Models\Evaluation;

class EvaluationPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        if ($user->isSuperAdmin() && ! in_array($ability, ['update', 'delete', 'approve'])) {
            return true;
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('evaluations.index');
    }

    public function view(User $user, Evaluation $record): bool
    {
        return $user->hasPermissionTo('evaluations.show');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('evaluations.create');
    }

    public function update(User $user, Evaluation $record): bool
    {
        if (($record->status?->value ?? $record->status) === BaseStatusEnum::Published->value) {
            return false;
        }

        return $user->isSuperAdmin() || $user->hasPermissionTo('evaluations.edit');
    }

    public function delete(User $user, Evaluation $record): bool
    {
        if (($record->status?->value ?? $record->status) === BaseStatusEnum::Published->value) {
            return false;
        }

        return $user->isSuperAdmin() || $user->hasPermissionTo('evaluations.destroy');
    }

    public function approve(User $user, Evaluation $record): bool
    {
        if (($record->status?->value ?? $record->status) === BaseStatusEnum::Published->value) {
            return false;
        }

        return $user->isSuperAdmin() || $user->hasPermissionTo('evaluations.approve');
    }
}
