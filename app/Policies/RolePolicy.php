<?php

namespace App\Policies;

use App\Models\User;
use Spatie\Permission\Models\Role;

class RolePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('super_admin')
            || $user->can('roles.view');
    }

    public function view(User $user, Role $role): bool
    {
        return $user->hasRole('super_admin')
            || $user->can('roles.view');
    }

    public function create(User $user): bool
    {
        return $user->hasRole('super_admin')
            || $user->can('roles.create');
    }

    public function update(User $user, Role $role): bool
    {
        return $role->name !== 'super_admin'
            && (
                $user->hasRole('super_admin')
                || $user->can('roles.update')
            );
    }

    public function delete(User $user, Role $role): bool
    {
        return $user->hasRole('super_admin');
    }

    public function restore(User $user, Role $role): bool
    {
        return false;
    }

    public function forceDelete(User $user, Role $role): bool
    {
        return false;
    }
}
