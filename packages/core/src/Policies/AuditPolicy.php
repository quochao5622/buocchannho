<?php

namespace Quochao56\Core\Policies;

use OwenIt\Auditing\Models\Audit;
use Quochao56\Core\Models\User;

class AuditPolicy
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
        return $user->hasPermissionTo('audits.index');
    }

    public function view(User $user, Audit $audit): bool
    {
        return $user->hasPermissionTo('audits.index');
    }

    public function restore(User $user, Audit $audit): bool
    {
        return $user->hasPermissionTo('audits.restore');
    }
}
