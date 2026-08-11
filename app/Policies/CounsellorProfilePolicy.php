<?php

namespace App\Policies;

use App\Models\CounsellorProfile;
use App\Models\User;

class CounsellorProfilePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('counsellors.view');
    }

    public function view(
        User $user,
        CounsellorProfile $counsellor
    ): bool {
        return $user->can('counsellors.view');
    }

    public function create(User $user): bool
    {
        return $user->can('counsellors.create');
    }

    public function update(
        User $user,
        CounsellorProfile $counsellor
    ): bool {
        return ! $counsellor->isArchived()
            && $user->can('counsellors.update');
    }

    public function delete(
        User $user,
        CounsellorProfile $counsellor
    ): bool {
        return ! $counsellor->isArchived()
            && $user->can('counsellors.archive');
    }

    public function restore(
        User $user,
        CounsellorProfile $counsellor
    ): bool {
        return $counsellor->isArchived()
            && $user->can('counsellors.update');
    }

    public function forceDelete(
        User $user,
        CounsellorProfile $counsellor
    ): bool {
        return false;
    }
}
