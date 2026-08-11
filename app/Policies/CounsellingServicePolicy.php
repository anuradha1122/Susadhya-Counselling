<?php

namespace App\Policies;

use App\Models\CounsellingService;
use App\Models\User;

class CounsellingServicePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('services.view');
    }

    public function view(
        User $user,
        CounsellingService $counsellingService
    ): bool {
        return $user->can('services.view');
    }

    public function create(User $user): bool
    {
        return $user->can('services.create');
    }

    public function update(
        User $user,
        CounsellingService $counsellingService
    ): bool {
        return $user->can('services.update')
            && $counsellingService->status !== 'archived';
    }

    public function delete(
        User $user,
        CounsellingService $counsellingService
    ): bool {
        return $user->can('services.archive')
            && $counsellingService->status !== 'archived';
    }

    public function restore(
        User $user,
        CounsellingService $counsellingService
    ): bool {
        return $user->can('services.update')
            && $counsellingService->status === 'archived';
    }

    public function forceDelete(
        User $user,
        CounsellingService $counsellingService
    ): bool {
        return false;
    }
}
