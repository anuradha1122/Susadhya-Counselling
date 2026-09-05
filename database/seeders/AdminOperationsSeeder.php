<?php

namespace Database\Seeders;

use App\Models\ContentSnippet;
use App\Models\SystemSetting;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class AdminOperationsSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'admin.operations.view',
            'admin.operations.manage',
            'admin.case-escalations.manage',
            'settings.view',
            'settings.update',
            'admin.content.manage',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        $admin = Role::firstOrCreate([
            'name' => 'admin',
            'guard_name' => 'web',
        ]);

        $superAdmin = Role::firstOrCreate([
            'name' => 'super_admin',
            'guard_name' => 'web',
        ]);

        $admin->givePermissionTo($permissions);
        $superAdmin->givePermissionTo($permissions);

        $settings = [
            [
                'group' => 'operations',
                'key' => 'operations.support_email',
                'label' => 'Operational support email',
                'description' => 'Email used for routine operational support.',
                'type' => SystemSetting::TYPE_EMAIL,
                'value' => null,
                'is_public' => false,
            ],
            [
                'group' => 'operations',
                'key' => 'operations.support_phone',
                'label' => 'Operational support phone',
                'description' => 'Phone number displayed to staff handling routine exceptions.',
                'type' => SystemSetting::TYPE_TEXT,
                'value' => null,
                'is_public' => false,
            ],
            [
                'group' => 'operations',
                'key' => 'operations.exception_due_hours',
                'label' => 'Default exception due hours',
                'description' => 'Target response period for newly recorded operational exceptions.',
                'type' => SystemSetting::TYPE_INTEGER,
                'value' => '24',
                'is_public' => false,
            ],
            [
                'group' => 'operations',
                'key' => 'operations.show_maintenance_notice',
                'label' => 'Show maintenance notice',
                'description' => 'Controls whether the configured maintenance notice is enabled.',
                'type' => SystemSetting::TYPE_BOOLEAN,
                'value' => '0',
                'is_public' => false,
            ],
        ];

        foreach ($settings as $setting) {
            SystemSetting::query()->firstOrCreate(
                [
                    'key' => $setting['key'],
                ],
                $setting
            );
        }

        $snippets = [
            [
                'key' => 'operations.booking_support',
                'title' => 'Booking support guidance',
                'body' => 'Review the appointment history and resolve only non-clinical scheduling issues.',
                'placement' => 'admin_operations',
                'status' => ContentSnippet::STATUS_PUBLISHED,
                'published_at' => now(),
            ],
            [
                'key' => 'operations.case_escalation_warning',
                'title' => 'Case escalation privacy warning',
                'body' => 'Record operational routing metadata only. Do not enter clinical notes, screening details, diagnoses, presenting concerns or session narratives.',
                'placement' => 'case_escalations',
                'status' => ContentSnippet::STATUS_PUBLISHED,
                'published_at' => now(),
            ],
        ];

        foreach ($snippets as $snippet) {
            ContentSnippet::query()->firstOrCreate(
                [
                    'key' => $snippet['key'],
                ],
                $snippet
            );
        }

        app(PermissionRegistrar::class)
            ->forgetCachedPermissions();
    }
}
