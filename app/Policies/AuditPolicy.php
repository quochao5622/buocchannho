<?php

namespace App\Policies;

use App\Models\User;
use OwenIt\Auditing\Models\Audit;

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
}
