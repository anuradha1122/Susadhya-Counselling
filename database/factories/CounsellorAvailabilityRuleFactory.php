<?php

namespace Database\Factories;

use App\Models\CounsellorAvailabilityRule;
use App\Models\CounsellorProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

class CounsellorAvailabilityRuleFactory extends Factory
{
    protected $model = CounsellorAvailabilityRule::class;

    public function definition(): array
    {
        return [
            'counsellor_profile_id' => CounsellorProfile::factory(),
            'day_of_week' => fake()->numberBetween(0, 6),
            'start_time' => '09:00',
            'end_time' => '17:00',
            'mode' => CounsellorAvailabilityRule::MODE_BOTH,
            'slot_duration_minutes' => 60,
            'buffer_minutes' => 0,
            'capacity_per_slot' => 1,
            'timezone' => 'Asia/Colombo',
            'effective_from' => null,
            'effective_until' => null,
            'is_active' => true,
            'notes' => fake()->optional()->sentence(),
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

    public function monday(): static
    {
        return $this->state(fn (): array => [
            'day_of_week' => CounsellorAvailabilityRule::MONDAY,
        ]);
    }
}
