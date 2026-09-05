<?php

namespace Database\Factories;

use App\Models\RetentionPolicy;
use App\Models\RetentionRun;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class RetentionRunFactory extends Factory
{
    protected $model =
        RetentionRun::class;

    public function definition(): array
    {
        return [
            'retention_policy_id' => RetentionPolicy::factory(),

            'initiated_by' => User::factory(),

            'mode' => 'dry_run',

            'status' => 'completed',

            'candidate_count' => 0,
            'processed_count' => 0,
            'skipped_count' => 0,
            'failed_count' => 0,

            'summary' => [],

            'started_at' => now(),
            'completed_at' => now(),
        ];
    }
}
