<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class SupportPermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(
            PermissionRegistrar::class
        )->forgetCachedPermissions();

        $permissions = [
            'support.client.manage',
            'support.admin.view',
            'support.admin.manage',
            'support.feedback.view',
        ];

        foreach (
            $permissions as $permission
        ) {
            Permission::findOrCreate(
                $permission,
                'web'
            );
        }

        $client =
            Role::findOrCreate(
                'client',
                'web'
            );

        $admin =
            Role::findOrCreate(
                'admin',
                'web'
            );

        $superAdmin =
            Role::findOrCreate(
                'super_admin',
                'web'
            );

        /*
         * Give, do not sync.
         *
         * Other module permissions must remain untouched.
         */
        $client->givePermissionTo([
            'support.client.manage',
        ]);

        $admin->givePermissionTo([
            'support.admin.view',
            'support.admin.manage',
            'support.feedback.view',
        ]);

        $superAdmin->givePermissionTo(
            $permissions
        );

        app(
            PermissionRegistrar::class
        )->forgetCachedPermissions();
    }
}
