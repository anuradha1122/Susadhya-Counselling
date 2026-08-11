<?php

namespace App\Policies;

use App\Models\ServiceCategory;
use App\Models\User;

class ServiceCategoryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('services.view');
    }

    public function view(
        User $user,
        ServiceCategory $category
    ): bool {
        return $user->can('services.view');
    }

    public function create(User $user): bool
    {
        return $user->can('services.create');
    }

    public function update(
        User $user,
        ServiceCategory $category
    ): bool {
        return ! $category->isArchived()
            && $user->can('services.update');
    }

    public function delete(
        User $user,
        ServiceCategory $category
    ): bool {
        return ! $category->isArchived()
            && ! $category->hasActiveServices()
            && $user->can('services.archive');
    }

    public function restore(
        User $user,
        ServiceCategory $category
    ): bool {
        return $category->isArchived()
            && $user->can('services.update');
    }

    public function forceDelete(
        User $user,
        ServiceCategory $category
    ): bool {
        return false;
    }
}
