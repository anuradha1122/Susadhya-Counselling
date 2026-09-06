<?php

namespace App\Http\Middleware;

use App\Services\PublicSite\PublicSiteDataService;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(
        Request $request
    ): ?string {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        $user = $request->user();

        $roles = $user
            ? collect($user->getRoleNames())->values()->all()
            : [];

        $permissions = $user
            ? collect($user->getAllPermissions())
                ->pluck('name')
                ->values()
                ->all()
            : [];

        return [
            ...parent::share($request),

            'app' => [
                'name' => config('app.name'),
            ],

            'auth' => [
                'user' => $user
                    ? [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'phone' => $user->phone,
                        'is_active' => (bool) $user->is_active,
                        'initials' => $user->initials,
                    ]
                    : null,

                'roles' => $roles,
                'permissions' => $permissions,
                'can' => [
                    'manageRoles' => $user?->can('roles.manage') ?? false,
                ],
            ],

            'flash' => [
                'success' => fn () => $request
                    ->session()
                    ->get('success'),

                'error' => fn () => $request
                    ->session()
                    ->get('error'),
            ],

            'publicSite' => fn () => app(
                PublicSiteDataService::class
            )->settings(),

            'publicSiteNavigation' => fn () => app(
                PublicSiteDataService::class
            )->navigation(),


        ];
    }
}
