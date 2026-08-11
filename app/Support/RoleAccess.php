<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Collection;
use Spatie\Permission\Models\Role;

class RoleAccess
{
    public static function assignableBy(User $actor): Collection
    {
        $query = Role::query()
            ->where('guard_name', 'web')
            ->when(! $actor->isSuperAdmin(), function ($query) use ($actor): void {
                $granted = $actor->getAllPermissions()->pluck('name');

                $query->where('name', '!=', 'super_admin')
                    ->where(function ($roles) use ($granted): void {
                        $roles->where('name', 'counsellor')
                            ->orWhereDoesntHave('permissions', fn ($permissions) => $permissions->whereNotIn('name', $granted));
                    });
            })
            ->orderBy('name');

        return $query->get(['id', 'name']);
    }

    public static function namesAssignableBy(User $actor): array
    {
        return self::assignableBy($actor)->pluck('name')->all();
    }
}
