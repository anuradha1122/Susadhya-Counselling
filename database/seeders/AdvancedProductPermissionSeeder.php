<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AdvancedProductPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'advanced-products.view',
            'advanced-products.manage',
            'advanced-products.ai-summaries.view',
            'advanced-products.ai-summaries.manage',
            'advanced-products.translations.manage',
            'group-counselling.client.view',
            'group-counselling.client.enroll',
            'packages.client.view',
            'packages.client.subscribe',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        Role::query()
            ->whereIn('name', ['Super Admin', 'Admin'])
            ->get()
            ->each(fn (Role $role) => $role->givePermissionTo($permissions));

        Role::query()
            ->where('name', 'Client')
            ->get()
            ->each(fn (Role $role) => $role->givePermissionTo([
                'group-counselling.client.view',
                'group-counselling.client.enroll',
                'packages.client.view',
                'packages.client.subscribe',
            ]));
    }
}
