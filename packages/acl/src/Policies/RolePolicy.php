<?php

namespace Quochao56\Acl\Policies;

use Quochao56\Core\Models\User;
use Spatie\Permission\Models\Role;

class RolePolicy
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
        return $user->hasPermissionTo('roles.index');
    }

    public function view(User $user, Role $record): bool
    {
        return $user->hasPermissionTo('roles.show');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('roles.create');
    }

    public function update(User $user, Role $record): bool
    {
        return $user->hasPermissionTo('roles.edit');
    }

    public function delete(User $user, Role $record): bool
    {
        return $user->hasPermissionTo('roles.destroy');
    }
}
