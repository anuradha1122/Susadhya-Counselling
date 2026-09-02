<?php

use App\Models\CounsellorProfile;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(
        RolePermissionSeeder::class
    );
});

function dashboardUser(
    string $role
): User {
    $user = User::factory()->create([
        'is_active' => true,
        'email_verified_at' => now(),
    ]);

    $user->assignRole($role);

    return $user;
}

test(
    'guest is redirected to login from dashboard',
    function (): void {
        $this
            ->get(
                route('dashboard')
            )
            ->assertRedirect(
                route('login')
            );
    }
);

test(
    'super administrator is redirected to admin dashboard',
    function (): void {
        $user = dashboardUser(
            'super_admin'
        );

        $this
            ->actingAs($user)
            ->get(
                route('dashboard')
            )
            ->assertRedirect(
                route(
                    'admin.dashboard'
                )
            );
    }
);

test(
    'admin is redirected to admin dashboard',
    function (): void {
        $user = dashboardUser(
            'admin'
        );

        $this
            ->actingAs($user)
            ->get(
                route('dashboard')
            )
            ->assertRedirect(
                route(
                    'admin.dashboard'
                )
            );
    }
);

test(
    'counsellor is redirected to counsellor dashboard',
    function (): void {
        $user = dashboardUser(
            'counsellor'
        );

        $this
            ->actingAs($user)
            ->get(
                route('dashboard')
            )
            ->assertRedirect(
                route(
                    'counsellor.dashboard'
                )
            );
    }
);

test(
    'admin can access admin dashboard',
    function (): void {
        $admin = dashboardUser(
            'admin'
        );

        $this
            ->actingAs($admin)
            ->get(
                route(
                    'admin.dashboard'
                )
            )
            ->assertOk()
            ->assertInertia(
                fn (Assert $page) => $page
                    ->component(
                        'Admin/Dashboard'
                    )
            );
    }
);

test(
    'counsellor cannot access admin dashboard',
    function (): void {
        $counsellor =
            dashboardUser(
                'counsellor'
            );

        CounsellorProfile::factory()
            ->create([
                'user_id' => $counsellor->id,
            ]);

        $this
            ->actingAs(
                $counsellor
            )
            ->get(
                route(
                    'admin.dashboard'
                )
            )
            ->assertForbidden();
    }
);

test(
    'counsellor can access counsellor dashboard',
    function (): void {
        $counsellor =
            dashboardUser(
                'counsellor'
            );

        CounsellorProfile::factory()
            ->create([
                'user_id' => $counsellor->id,

                'status' => 'active',
            ]);

        $this
            ->actingAs(
                $counsellor
            )
            ->get(
                route(
                    'counsellor.dashboard'
                )
            )
            ->assertOk()
            ->assertInertia(
                fn (Assert $page) => $page
                    ->component(
                        'Counsellor/Dashboard'
                    )
            );
    }
);

test(
    'admin cannot access counsellor dashboard',
    function (): void {
        $admin =
            dashboardUser(
                'admin'
            );

        $this
            ->actingAs(
                $admin
            )
            ->get(
                route(
                    'counsellor.dashboard'
                )
            )
            ->assertForbidden();
    }
);

test(
    'inactive user is logged out',
    function (): void {
        $user = dashboardUser(
            'admin'
        );

        $user->update([
            'is_active' => false,
        ]);

        $response = $this
            ->actingAs($user)
            ->get(
                route('dashboard')
            );

        $response->assertRedirect(
            route('login')
        );

        $this->assertGuest();

        $response
            ->assertSessionHasErrors(
                'email'
            );
    }
);
