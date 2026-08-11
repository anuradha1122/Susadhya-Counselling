<?php

namespace App\Providers;

use App\Models\User;
use App\Policies\RolePolicy;
use App\Policies\UserPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Spatie\Permission\Models\Role;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Role::class, RolePolicy::class);

        Gate::before(function (
            User $user,
            string $ability,
            array $arguments
        ): ?bool {
            /*
             * RolePolicy must handle role operations so protected roles
             * cannot bypass its update/delete rules.
             */
            if (($arguments[0] ?? null) instanceof Role) {
                return null;
            }

            return $user->hasRole('super_admin')
                ? true
                : null;
        });
    }
}
