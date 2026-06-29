<?php

namespace App\Policies;

use App\Models\User;
use Quochao56\Equipment\Models\Equipment;

class EquipmentPolicy
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
        return $user->hasPermissionTo('equipments.index');
    }

    public function view(User $user, Equipment $record): bool
    {
        return $user->hasPermissionTo('equipments.show');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('equipments.create');
    }

    public function update(User $user, Equipment $record): bool
    {
        return $user->hasPermissionTo('equipments.edit');
    }

    public function delete(User $user, Equipment $record): bool
    {
        return $user->hasPermissionTo('equipments.destroy');
    }
}
