<?php

namespace Database\Factories;

use App\Models\RetentionPolicy;
use Illuminate\Database\Eloquent\Factories\Factory;

class RetentionPolicyFactory extends Factory
{
    protected $model =
        RetentionPolicy::class;

    public function definition(): array
    {
        return [
            'category' => $this->faker
                ->unique()
                ->word(),

            'name' => $this->faker
                ->words(3, true),

            'retention_days' => 365,

            'action' => RetentionPolicy::ACTION_REVIEW,

            'enabled' => false,

            'automatic_execution' => false,
        ];
    }
}
