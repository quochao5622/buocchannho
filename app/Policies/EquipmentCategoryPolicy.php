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
        return $user->hasPermissionTo('equipment_categories.index');
    }

    public function view(User $user, EquipmentCategory $record): bool
    {
        return $user->hasPermissionTo('equipment_categories.show');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('equipment_categories.create');
    }

    public function update(User $user, EquipmentCategory $record): bool
    {
        return $user->hasPermissionTo('equipment_categories.edit');
    }

    public function delete(User $user, EquipmentCategory $record): bool
    {
        return $user->hasPermissionTo('equipment_categories.destroy');
    }
}
