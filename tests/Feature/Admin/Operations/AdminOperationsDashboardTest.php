<?php

use App\Models\CaseEscalation;
use App\Models\OperationalException;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    app(PermissionRegistrar::class)
        ->forgetCachedPermissions();
});

function operationsDashboardAdmin(): User
{
    $permission = Permission::firstOrCreate([
        'name' => 'admin.operations.view',
        'guard_name' => 'web',
    ]);

    $user = User::factory()->create([
        'is_active' => true,
    ]);

    $user->givePermissionTo($permission);

    return $user;
}

it('allows authorised admin to view operations dashboard', function (): void {
    $admin = operationsDashboardAdmin();

    OperationalException::factory()->create([
        'created_by' => $admin->id,
    ]);

    CaseEscalation::factory()->create([
        'created_by' => $admin->id,
    ]);

    $this
        ->actingAs($admin)
        ->get(route('admin.operations.index'))
        ->assertOk()
        ->assertInertia(
            fn ($page) => $page
                ->component('Admin/Operations/Index')
                ->has('metrics')
                ->has('exceptions.data')
        );
});

it('prevents unauthorised user from viewing operations dashboard', function (): void {
    $user = User::factory()->create([
        'is_active' => true,
    ]);

    $this
        ->actingAs($user)
        ->get(route('admin.operations.index'))
        ->assertForbidden();
});
