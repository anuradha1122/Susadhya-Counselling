<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class ReportPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $operationalPermissions = [
            'reports.operational.view',
            'reports.operational.export',
        ];

        $financePermissions = [
            'reports.finance.view',
            'reports.finance.export',
        ];

        foreach (
            [
                ...$operationalPermissions,
                ...$financePermissions,
            ] as $permission
        ) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        $admin = Role::firstOrCreate([
            'name' => 'admin',
            'guard_name' => 'web',
        ]);

        $financeAdmin = Role::firstOrCreate([
            'name' => 'finance_admin',
            'guard_name' => 'web',
        ]);

        $superAdmin = Role::firstOrCreate([
            'name' => 'super_admin',
            'guard_name' => 'web',
        ]);

        $admin->givePermissionTo(
            $operationalPermissions
        );

        $financeAdmin->givePermissionTo(
            $financePermissions
        );

        $superAdmin->givePermissionTo([
            ...$operationalPermissions,
            ...$financePermissions,
        ]);

        app(PermissionRegistrar::class)
            ->forgetCachedPermissions();
    }
}
