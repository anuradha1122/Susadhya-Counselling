<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $actor): bool
    {
        return $actor->can('users.view');
    }

    public function create(User $actor): bool
    {
        return $actor->can('users.create');
    }

    public function update(User $actor, User $subject): bool
    {
        if (! $actor->can('users.update')) {
            return false;
        }

        return ! $subject->isSuperAdmin() || $actor->isSuperAdmin();
    }

    public function delete(User $actor, User $subject): bool
    {
        return $actor->can('users.delete')
            && ! $subject->isSuperAdmin()
            && ! $actor->is($subject);
    }
}
