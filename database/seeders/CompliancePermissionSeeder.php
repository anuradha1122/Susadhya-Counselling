<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class CompliancePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(
            PermissionRegistrar::class
        )->forgetCachedPermissions();

        $permissions = [
            'compliance.audit.view',

            'compliance.privacy.view',
            'compliance.privacy.manage',

            'compliance.retention.view',
            'compliance.retention.manage',

            'compliance.consents.view',

            'compliance.breaches.view',
            'compliance.breaches.manage',

            'privacy.requests.submit',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        $privacyOfficer =
            Role::firstOrCreate([
                'name' => 'privacy_officer',
                'guard_name' => 'web',
            ]);

        $privacyOfficer->syncPermissions([
            'compliance.audit.view',

            'compliance.privacy.view',
            'compliance.privacy.manage',

            'compliance.retention.view',
            'compliance.retention.manage',

            'compliance.consents.view',

            'compliance.breaches.view',
            'compliance.breaches.manage',
        ]);

        $superAdmin =
            Role::firstOrCreate([
                'name' => 'super_admin',
                'guard_name' => 'web',
            ]);

        $superAdmin->givePermissionTo(
            $permissions
        );

        $client =
            Role::firstOrCreate([
                'name' => 'client',
                'guard_name' => 'web',
            ]);

        $client->givePermissionTo(
            'privacy.requests.submit'
        );

        app(
            PermissionRegistrar::class
        )->forgetCachedPermissions();
    }
}
