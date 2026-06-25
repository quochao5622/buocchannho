<?php

namespace App\Policies;

use App\Models\User;
use Quochao56\Equipment\Models\EquipmentCategory;

class EquipmentCategoryPolicy
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
        return $user->hasPermissionTo('check_equipment');
    }

    public function view(User $user, EquipmentCategory $record): bool
    {
        return $user->hasPermissionTo('check_equipment');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('check_equipment');
    }

    public function update(User $user, EquipmentCategory $record): bool
    {
        return $user->hasPermissionTo('check_equipment');
    }

    public function delete(User $user, EquipmentCategory $record): bool
    {
        return $user->hasPermissionTo('check_equipment');
    }
}
