<?php

namespace Quochao56\PlanningEvaluation\Policies;

use Quochao56\Core\Enum\BaseStatusEnum;
use Quochao56\Core\Models\User;
use Quochao56\PlanningEvaluation\Models\Planning;

class PlanningPolicy
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
        return $user->hasPermissionTo('plannings.index');
    }

    public function view(User $user, Planning $record): bool
    {
        return $user->hasPermissionTo('plannings.show');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('plannings.create');
    }

    public function update(User $user, Planning $record): bool
    {
        if (($record->status?->value ?? $record->status) === BaseStatusEnum::Published->value) {
            return false;
        }

        return $user->isSuperAdmin() || $user->hasPermissionTo('plannings.edit');
    }

    public function delete(User $user, Planning $record): bool
    {
        if (($record->status?->value ?? $record->status) === BaseStatusEnum::Published->value) {
            return false;
        }

        return $user->isSuperAdmin() || $user->hasPermissionTo('plannings.destroy');
    }

    public function approve(User $user, Planning $record): bool
    {
        if (($record->status?->value ?? $record->status) === BaseStatusEnum::Published->value) {
            return false;
        }

        return $user->isSuperAdmin() || $user->hasPermissionTo('plannings.approve');
    }
}
