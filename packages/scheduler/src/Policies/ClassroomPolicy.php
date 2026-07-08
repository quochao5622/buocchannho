<?php

namespace Quochao56\Scheduler\Policies;

use Quochao56\Core\Models\User;
use Quochao56\Scheduler\Models\Classroom;

class ClassroomPolicy
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
        return $user->hasPermissionTo('classrooms.index');
    }

    public function view(User $user, Classroom $record): bool
    {
        return $user->hasPermissionTo('classrooms.show');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('classrooms.create');
    }

    public function update(User $user, Classroom $record): bool
    {
        return $user->hasPermissionTo('classrooms.edit');
    }

    public function delete(User $user, Classroom $record): bool
    {
        return $user->hasPermissionTo('classrooms.destroy');
    }
}