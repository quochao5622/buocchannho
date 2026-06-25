<?php

namespace App\Policies;

use App\Models\User;
use Quochao56\Employee\Models\Employee;

class EmployeePolicy
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
        return false;
    }

    public function view(User $user, Employee $record): bool
    {
        return false;
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, Employee $record): bool
    {
        return false;
    }

    public function delete(User $user, Employee $record): bool
    {
        return false;
    }
}
