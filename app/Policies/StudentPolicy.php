<?php

namespace App\Policies;

use App\Models\User;
use Quochao56\Student\Models\Student;

class StudentPolicy
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
        return $user->hasRole('teacher');
    }

    public function view(User $user, Student $record): bool
    {
        return $user->hasRole('teacher');
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, Student $record): bool
    {
        return false;
    }

    public function delete(User $user, Student $record): bool
    {
        return false;
    }
}
