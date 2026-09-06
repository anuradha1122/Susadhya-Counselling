<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class CmsPermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(
            PermissionRegistrar::class
        )->forgetCachedPermissions();

        $permissions = [
            'cms.view',
            'cms.pages.manage',
            'cms.sections.manage',
            'cms.settings.manage',
            'cms.faqs.manage',
            'cms.testimonials.manage',
            'cms.media.manage',
        ];

        foreach (
            $permissions as $permission
        ) {
            Permission::findOrCreate(
                $permission,
                'web'
            );
        }

        foreach (
            [
                'super_admin',
                'admin',
            ] as $roleName
        ) {
            $role =
                Role::findOrCreate(
                    $roleName,
                    'web'
                );

            foreach (
                $permissions as $permission
            ) {
                if (
                    ! $role->hasPermissionTo(
                        $permission
                    )
                ) {
                    $role->givePermissionTo(
                        $permission
                    );
                }
            }
        }

        app(
            PermissionRegistrar::class
        )->forgetCachedPermissions();
    }
}
