<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
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

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        $superAdmin = Role::firstOrCreate([
            'name' => 'super_admin',
            'guard_name' => 'web',
        ]);

        $admin = Role::firstOrCreate([
            'name' => 'admin',
            'guard_name' => 'web',
        ]);

        $counsellor = Role::firstOrCreate([
            'name' => 'counsellor',
            'guard_name' => 'web',
        ]);

        $superAdmin->syncPermissions(Permission::all());

        $admin->syncPermissions([
            'dashboard.admin.view',

            'users.view',
            'users.create',
            'users.update',

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
            'appointments.manage',

            'payments.view-all',
            'reports.view',
        ]);

        $counsellor->syncPermissions([
            'dashboard.counsellor.view',
            'appointments.view-own',
            'appointments.update-own',
            'availability.view-own',
            'availability.manage-own',
            'payments.view-own',
        ]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
