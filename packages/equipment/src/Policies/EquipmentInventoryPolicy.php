<?php

namespace Quochao56\Equipment\Policies;

use Quochao56\Core\Models\User;
use Quochao56\Equipment\Enum\InventoryStatus;
use Quochao56\Equipment\Models\EquipmentInventory;

class EquipmentInventoryPolicy
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
        return $user->hasPermissionTo('equipment_inventories.index');
    }

    public function view(User $user, EquipmentInventory $record): bool
    {
        return $user->hasPermissionTo('equipment_inventories.show');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('equipment_inventories.create');
    }

    public function update(User $user, EquipmentInventory $record): bool
    {
        if (($record->status?->value ?? $record->status) === InventoryStatus::Approved->value) {
            return false;
        }

        return $user->isSuperAdmin() || $user->hasPermissionTo('equipment_inventories.edit');
    }

    public function delete(User $user, EquipmentInventory $record): bool
    {
        if (($record->status?->value ?? $record->status) === InventoryStatus::Approved->value) {
            return false;
        }

        return $user->isSuperAdmin() || $user->hasPermissionTo('equipment_inventories.destroy');
    }

    public function approve(User $user, EquipmentInventory $record): bool
    {
        if (($record->status?->value ?? $record->status) !== InventoryStatus::Completed->value) {
            return false;
        }

        return $user->isSuperAdmin() || $user->hasPermissionTo('equipment_inventories.approve');
    }
}
