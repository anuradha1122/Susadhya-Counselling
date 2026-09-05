<?php

namespace Database\Factories;

use App\Models\DataBreach;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class DataBreachFactory extends Factory
{
    protected $model =
        DataBreach::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker
                ->sentence(4),

            'severity' => DataBreach::SEVERITY_MEDIUM,

            'status' => DataBreach::STATUS_OPEN,

            'reported_by' => User::factory(),

            'detected_at' => now(),

            'affected_subject_count' => 0,

            'data_categories' => [
                'account_metadata',
            ],

            'systems_affected' => [
                'web_application',
            ],

            'summary' => 'Test breach summary.',
        ];
    }
}
