<?php

namespace Database\Factories;

use App\Models\CounsellorAvailabilityBreak;
use App\Models\CounsellorAvailabilityRule;
use Illuminate\Database\Eloquent\Factories\Factory;

class CounsellorAvailabilityBreakFactory extends Factory
{
    protected $model = CounsellorAvailabilityBreak::class;

    public function definition(): array
    {
        return [
            'counsellor_availability_rule_id' => CounsellorAvailabilityRule::factory(),
            'title' => 'Lunch Break',
            'start_time' => '12:00',
            'end_time' => '13:00',
            'is_active' => true,
            'created_by' => null,
            'updated_by' => null,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (): array => [
            'is_active' => false,
        ]);
    }
}
