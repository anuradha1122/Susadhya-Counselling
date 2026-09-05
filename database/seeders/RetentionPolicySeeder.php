<?php

namespace Database\Seeders;

use App\Models\RetentionPolicy;
use Illuminate\Database\Seeder;

class RetentionPolicySeeder extends Seeder
{
    public function run(): void
    {
        $policies = [
            [
                'category' => RetentionPolicy::CATEGORY_CLINICAL,

                'name' => 'Clinical records',

                'description' => 'Clinical records require legal and professional review before retention execution.',
            ],

            [
                'category' => RetentionPolicy::CATEGORY_FINANCIAL,

                'name' => 'Financial records',

                'description' => 'Financial records require statutory/accounting review before retention execution.',
            ],

            [
                'category' => RetentionPolicy::CATEGORY_DOCUMENTS,

                'name' => 'Secure documents',

                'description' => 'Private documents require scope and legal-basis review before deletion or anonymisation.',
            ],

            [
                'category' => RetentionPolicy::CATEGORY_NOTIFICATIONS,

                'name' => 'Notifications',

                'description' => 'Low-risk notification metadata may be deleted automatically only after approval.',
            ],

            [
                'category' => RetentionPolicy::CATEGORY_AUDIT,

                'name' => 'Audit logs',

                'description' => 'Audit evidence is review-only by default and must not be casually purged.',
            ],

            [
                'category' => RetentionPolicy::CATEGORY_OPERATIONAL,

                'name' => 'Operational records',

                'description' => 'Operational exception metadata requires review before retention execution.',
            ],
        ];

        foreach ($policies as $policy) {
            RetentionPolicy::query()->firstOrCreate(
                [
                    'category' => $policy['category'],
                ],
                [
                    ...$policy,

                    'retention_days' => null,

                    'action' => RetentionPolicy::ACTION_REVIEW,

                    'enabled' => false,

                    'automatic_execution' => false,
                ]
            );
        }
    }
}
