<?php

namespace App\Policies;

use App\Models\ClientProfile;
use App\Models\User;

class ClientProfilePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('clients.view');
    }

    public function view(
        User $user,
        ClientProfile $clientProfile
    ): bool {
        return $user->can('clients.view')
            || $clientProfile->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->can('clients.create');
    }

    public function update(
        User $user,
        ClientProfile $clientProfile
    ): bool {
        if ($clientProfile->isArchived()) {
            return false;
        }

        return $user->can('clients.update')
            || $clientProfile->user_id === $user->id;
    }

    public function delete(
        User $user,
        ClientProfile $clientProfile
    ): bool {
        return ! $clientProfile->isArchived()
            && $user->can('clients.archive');
    }

    public function restore(
        User $user,
        ClientProfile $clientProfile
    ): bool {
        return $clientProfile->isArchived()
            && $user->can('clients.update');
    }

    public function forceDelete(
        User $user,
        ClientProfile $clientProfile
    ): bool {
        return false;
    }
}
