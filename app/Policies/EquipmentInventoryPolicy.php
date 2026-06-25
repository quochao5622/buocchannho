<?php

namespace App\Policies;

use App\Models\User;
use Quochao56\Equipment\Models\EquipmentInventory;

class EquipmentInventoryPolicy
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

    public function view(User $user, EquipmentInventory $record): bool
    {
        return $user->hasPermissionTo('check_equipment');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('check_equipment');
    }

    public function update(User $user, EquipmentInventory $record): bool
    {
        return $user->hasPermissionTo('check_equipment');
    }

    public function delete(User $user, EquipmentInventory $record): bool
    {
        return $user->hasPermissionTo('check_equipment');
    }
}
