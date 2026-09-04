<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PaymentPermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(
            PermissionRegistrar::class
        )->forgetCachedPermissions();

        $permissions = [
            'payments.client.manage',
            'payments.finance.view',
            'payments.finance.manage',
            'payments.refunds.manage',
            'payments.reconciliation.manage',
        ];

        foreach (
            $permissions as $permission
        ) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        $client =
            Role::firstOrCreate([
                'name' => 'client',
                'guard_name' => 'web',
            ]);

        $finance =
            Role::firstOrCreate([
                'name' => 'finance_admin',
                'guard_name' => 'web',
            ]);

        $superAdmin =
            Role::firstOrCreate([
                'name' => 'super_admin',
                'guard_name' => 'web',
            ]);

        $client->givePermissionTo(
            'payments.client.manage'
        );

        $finance->syncPermissions([
            'payments.finance.view',
            'payments.finance.manage',
            'payments.refunds.manage',
            'payments.reconciliation.manage',
        ]);

        $superAdmin->givePermissionTo(
            $permissions
        );

        app(
            PermissionRegistrar::class
        )->forgetCachedPermissions();
    }
}
