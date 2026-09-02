<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class DocumentPermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(
            PermissionRegistrar::class
        )->forgetCachedPermissions();

        $adminPermission =
            Permission::findOrCreate(
                'documents.admin.manage'
            );

        $caseManagePermission =
            Permission::findOrCreate(
                'documents.case.manage'
            );

        $caseReviewPermission =
            Permission::findOrCreate(
                'documents.case.review'
            );

        $clientPermission =
            Permission::findOrCreate(
                'documents.client.manage'
            );

        $admin =
            Role::findOrCreate(
                'admin'
            );

        $counsellor =
            Role::findOrCreate(
                'counsellor'
            );

        $clinicalSupervisor =
            Role::findOrCreate(
                'clinical_supervisor'
            );

        $client =
            Role::findOrCreate(
                'client'
            );

        $superAdmin =
            Role::findOrCreate(
                'super_admin'
            );

        $admin->givePermissionTo(
            $adminPermission
        );

        $counsellor->givePermissionTo(
            $caseManagePermission
        );

        $clinicalSupervisor->givePermissionTo(
            $caseReviewPermission
        );

        $client->givePermissionTo(
            $clientPermission
        );

        $superAdmin->givePermissionTo([
            $adminPermission,
            $caseManagePermission,
            $caseReviewPermission,
            $clientPermission,
        ]);

        app(
            PermissionRegistrar::class
        )->forgetCachedPermissions();
    }
}
