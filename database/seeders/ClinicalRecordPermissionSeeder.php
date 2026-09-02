<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class ClinicalRecordPermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)
            ->forgetCachedPermissions();

        $manage = Permission::findOrCreate(
            'clinical.records.manage',
            'web',
        );

        $review = Permission::findOrCreate(
            'clinical.records.review',
            'web',
        );

        $counsellor = Role::findOrCreate(
            'counsellor',
            'web',
        );

        $supervisor = Role::findOrCreate(
            'clinical_supervisor',
            'web',
        );

        $superAdmin = Role::findOrCreate(
            'super_admin',
            'web',
        );

        $counsellor->givePermissionTo($manage);

        $supervisor->givePermissionTo($review);

        $superAdmin->givePermissionTo([
            $manage,
            $review,
        ]);

        app(PermissionRegistrar::class)
            ->forgetCachedPermissions();
    }
}
