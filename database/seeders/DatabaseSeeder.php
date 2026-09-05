<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            SuperAdminSeeder::class,
            CounsellorReferenceSeeder::class,
            ClinicalRecordPermissionSeeder::class,
            DocumentPermissionSeeder::class,
            PaymentPermissionSeeder::class,
            NotificationTemplateSeeder::class,
            NotificationPermissionSeeder::class,
            AdminOperationsSeeder::class,
            ReportPermissionSeeder::class,
            CompliancePermissionSeeder::class,
            RetentionPolicySeeder::class,
        ]);
    }
}
