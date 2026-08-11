<?php

use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('module roles are created', function () {
    expect(Role::where('name', 'super_admin')->exists())
        ->toBeTrue()
        ->and(Role::where('name', 'admin')->exists())
        ->toBeTrue()
        ->and(Role::where('name', 'counsellor')->exists())
        ->toBeTrue();
});

test('required permissions are created', function () {
    $requiredPermissions = [
        'dashboard.admin.view',
        'dashboard.counsellor.view',
        'users.view',
        'users.create',
        'users.update',
        'users.delete',
        'roles.view',
        'roles.manage',
        'settings.view',
        'settings.update',
        'audit-logs.view',
        'counsellors.view',
        'counsellors.create',
        'counsellors.update',
        'counsellors.archive',
        'services.view',
        'services.create',
        'services.update',
        'services.archive',
        'appointments.view-all',
        'appointments.view-own',
        'appointments.manage',
        'appointments.update-own',
        'availability.view-own',
        'availability.manage-own',
        'payments.view-all',
        'payments.view-own',
        'reports.view',
    ];

    foreach ($requiredPermissions as $permission) {
        expect(
            Permission::where('name', $permission)
                ->where('guard_name', 'web')
                ->exists()
        )->toBeTrue();
    }
});

test('super administrator receives all permissions', function () {
    $role = Role::findByName('super_admin', 'web');

    expect($role->permissions->count())
        ->toBe(Permission::count());
});

test('admin receives admin dashboard permission', function () {
    $role = Role::findByName('admin', 'web');

    expect($role->hasPermissionTo('dashboard.admin.view'))
        ->toBeTrue()
        ->and($role->hasPermissionTo('dashboard.counsellor.view'))
        ->toBeFalse();
});

test('counsellor receives only counsellor workspace permissions', function () {
    $role = Role::findByName('counsellor', 'web');

    expect($role->hasPermissionTo('dashboard.counsellor.view'))
        ->toBeTrue()
        ->and($role->hasPermissionTo('appointments.view-own'))
        ->toBeTrue()
        ->and($role->hasPermissionTo('dashboard.admin.view'))
        ->toBeFalse();
});
