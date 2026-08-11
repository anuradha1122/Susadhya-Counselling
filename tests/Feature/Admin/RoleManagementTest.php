<?php

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

function roleManager(string $role): User
{
    $user = User::factory()->create();
    $user->assignRole($role);

    return $user;
}

test('super administrator can view and create roles', function () {
    $superAdmin = roleManager('super_admin');

    $this->actingAs($superAdmin)->get(route('admin.roles.index'))->assertOk();
    $this->actingAs($superAdmin)->post(route('admin.roles.store'), [
        'name' => 'office_manager',
        'permissions' => ['dashboard.admin.view', 'users.view'],
    ])->assertRedirect(route('admin.roles.index'));

    $role = Role::findByName('office_manager');
    expect($role->hasAllPermissions(['dashboard.admin.view', 'users.view']))->toBeTrue();
});

test('regular admin cannot manage roles', function () {
    $admin = roleManager('admin');

    $this->actingAs($admin)->get(route('admin.roles.index'))->assertForbidden();
    $this->actingAs($admin)->post(route('admin.roles.store'), ['name' => 'unsafe', 'permissions' => []])->assertForbidden();
});

test('super administrator role cannot be edited or deleted', function () {
    $superAdmin = roleManager('super_admin');
    $role = Role::findByName('super_admin');

    $this->actingAs($superAdmin)->get(route('admin.roles.edit', $role))->assertForbidden();
    $this->actingAs($superAdmin)->delete(route('admin.roles.destroy', $role))->assertStatus(422);
});

test('custom role assigned to users cannot be deleted', function () {
    $superAdmin = roleManager('super_admin');
    $role = Role::create(['name' => 'assigned_role', 'guard_name' => 'web']);
    User::factory()->create()->assignRole($role);

    $this->actingAs($superAdmin)->delete(route('admin.roles.destroy', $role))->assertStatus(422);
});

test('custom role with admin dashboard permission reaches admin workspace', function () {
    $superAdmin = roleManager('super_admin');
    $role = Role::create(['name' => 'office_manager', 'guard_name' => 'web']);
    $role->givePermissionTo('dashboard.admin.view');
    $user = User::factory()->create();
    $user->assignRole($role);

    $this->actingAs($user)->get('/dashboard')->assertRedirect(route('admin.dashboard'));
    $this->actingAs($user)->get(route('admin.dashboard'))->assertOk();
});
