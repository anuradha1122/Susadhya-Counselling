<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class NotificationPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'notifications.templates.manage',
            'notifications.deliveries.view',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate(
                $permission,
                'web'
            );
        }

        foreach ([
            'admin',
            'super_admin',
        ] as $roleName) {
            $role = Role::query()
                ->where('name', $roleName)
                ->where('guard_name', 'web')
                ->first();

            if (! $role) {
                continue;
            }

            $role->givePermissionTo(
                $permissions
            );
        }
    }
}
