<?php

use App\Models\ClientConsent;
use App\Models\ClientEmergencyContact;
use App\Models\ClientPreference;
use App\Models\ClientProfile;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    app(PermissionRegistrar::class)->forgetCachedPermissions();

    $this->seed(RolePermissionSeeder::class);
});

function createAdminUserForModule05(): User
{
    $user = User::factory()->create([
        'name' => 'Admin User',
        'email' => 'admin@example.com',
        'is_active' => true,
    ]);

    $user->assignRole('admin');

    return $user;
}

function createRestrictedAdminUserForModule05(): User
{
    $role = Role::create([
        'name' => 'limited_client_admin',
        'guard_name' => 'web',
    ]);

    $role->givePermissionTo([
        'dashboard.admin.view',
    ]);

    $user = User::factory()->create([
        'name' => 'Limited Admin',
        'email' => 'limited.admin@example.com',
        'is_active' => true,
    ]);

    $user->assignRole($role);

    return $user;
}

function createClientProfileForAdminModule05(
    array $userOverrides = [],
    array $profileOverrides = []
): ClientProfile {
    $user = User::factory()->create(array_merge([
        'name' => 'Managed Client',
        'email' => fake()->unique()->safeEmail(),
        'phone' => '+94 77 123 4567',
        'is_active' => true,
    ], $userOverrides));

    $user->assignRole('client');

    $profile = ClientProfile::factory()
        ->for($user)
        ->create(array_merge([
            'first_name' => 'Managed',
            'last_name' => 'Client',
            'preferred_language' => 'english',
            'preferred_contact_method' => 'email',
            'status' => 'active',
        ], $profileOverrides));

    ClientPreference::factory()
        ->for($profile)
        ->create([
            'preferred_language' => $profile->preferred_language,
        ]);

    ClientEmergencyContact::factory()
        ->for($profile)
        ->create([
            'name' => 'Emergency Contact',
            'is_primary' => true,
        ]);

    ClientConsent::factory()
        ->terms()
        ->for($profile)
        ->create();

    ClientConsent::factory()
        ->privacyPolicy()
        ->for($profile)
        ->create();

    return $profile->refresh();
}

it('allows an admin to view the client index and detail pages', function (): void {
    $admin = createAdminUserForModule05();

    $clientProfile = createClientProfileForAdminModule05([
        'email' => 'managed.client@example.com',
    ]);

    $this
        ->actingAs($admin)
        ->get(route('admin.clients.index'))
        ->assertOk()
        ->assertSee('Clients');

    $this
        ->actingAs($admin)
        ->get(route('admin.clients.show', $clientProfile))
        ->assertOk()
        ->assertSee('Managed Client')
        ->assertSee('Emergency Contact');
});

it('filters clients by search term status and completion state', function (): void {
    $admin = createAdminUserForModule05();

    $alphaProfile = createClientProfileForAdminModule05([
        'email' => 'alpha.client@example.com',
    ], [
        'first_name' => 'Alpha',
        'last_name' => 'Client',
        'status' => 'active',
        'profile_completed_at' => now(),
    ]);

    createClientProfileForAdminModule05([
        'email' => 'beta.client@example.com',
        'is_active' => false,
    ], [
        'first_name' => 'Beta',
        'last_name' => 'Client',
        'status' => 'inactive',
        'profile_completed_at' => null,
    ]);

    $response = $this
        ->actingAs($admin)
        ->get(route('admin.clients.index', [
            'search' => 'Alpha',
            'status' => 'active',
            'completion' => 'complete',
        ]));

    $response->assertOk();

    $clients = $response->viewData('page')['props']['clients']['data'];

    expect($clients)
        ->toHaveCount(1)
        ->and($clients[0]['id'])->toBe($alphaProfile->id)
        ->and($clients[0]['email'])->toBe('alpha.client@example.com')
        ->and($clients[0]['status'])->toBe('active')
        ->and($clients[0]['is_complete'])->toBeTrue();

    expect($alphaProfile->refresh()->first_name)->toBe('Alpha')
        ->and($alphaProfile->last_name)->toBe('Client');
});

it('prevents an admin without clients view permission from seeing clients', function (): void {
    $restrictedAdmin = createRestrictedAdminUserForModule05();

    $this
        ->actingAs($restrictedAdmin)
        ->get(route('admin.clients.index'))
        ->assertForbidden();
});

it('allows an admin to deactivate and reactivate a client', function (): void {
    $admin = createAdminUserForModule05();

    $clientProfile = createClientProfileForAdminModule05();

    $this
        ->actingAs($admin)
        ->patch(route('admin.clients.status', $clientProfile), [
            'status' => 'inactive',
        ])
        ->assertRedirect();

    $clientProfile->refresh();

    expect($clientProfile->status)->toBe('inactive')
        ->and($clientProfile->user->refresh()->is_active)->toBeFalse();

    $this
        ->actingAs($admin)
        ->patch(route('admin.clients.status', $clientProfile), [
            'status' => 'active',
        ])
        ->assertRedirect();

    $clientProfile->refresh();

    expect($clientProfile->status)->toBe('active')
        ->and($clientProfile->user->refresh()->is_active)->toBeTrue();
});

it('allows an admin to archive and restore a client', function (): void {
    $admin = createAdminUserForModule05();

    $clientProfile = createClientProfileForAdminModule05();

    $this
        ->actingAs($admin)
        ->delete(route('admin.clients.destroy', $clientProfile))
        ->assertRedirect(route('admin.clients.index'));

    $clientProfile->refresh();

    expect($clientProfile->status)->toBe('archived')
        ->and($clientProfile->archived_at)->not->toBeNull()
        ->and($clientProfile->archived_by)->toBe($admin->id)
        ->and($clientProfile->user->refresh()->is_active)->toBeFalse();

    $this
        ->actingAs($admin)
        ->patch(route('admin.clients.restore', $clientProfile))
        ->assertRedirect();

    $clientProfile->refresh();

    expect($clientProfile->status)->toBe('active')
        ->and($clientProfile->archived_at)->toBeNull()
        ->and($clientProfile->archived_by)->toBeNull()
        ->and($clientProfile->user->refresh()->is_active)->toBeTrue();
});

it('rejects invalid admin client status updates', function (): void {
    $admin = createAdminUserForModule05();

    $clientProfile = createClientProfileForAdminModule05();

    $this
        ->actingAs($admin)
        ->from(route('admin.clients.show', $clientProfile))
        ->patch(route('admin.clients.status', $clientProfile), [
            'status' => 'archived',
        ])
        ->assertRedirect(route('admin.clients.show', $clientProfile))
        ->assertSessionHasErrors('status');

    expect($clientProfile->refresh()->status)->toBe('active');
});
