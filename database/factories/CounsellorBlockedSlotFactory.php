<?php

namespace Database\Factories;

use App\Models\CounsellorBlockedSlot;
use App\Models\CounsellorProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

class CounsellorBlockedSlotFactory extends Factory
{
    protected $model = CounsellorBlockedSlot::class;

    public function definition(): array
    {
        return [
            'counsellor_profile_id' => CounsellorProfile::factory(),
            'blocked_date' => now()->addWeek()->toDateString(),
            'start_time' => '10:00',
            'end_time' => '11:00',
            'is_full_day' => false,
            'reason' => 'Personal commitment',
            'notes' => fake()->optional()->sentence(),
            'created_by' => null,
            'updated_by' => null,
        ];
    }

    public function fullDay(): static
    {
        return $this->state(fn (): array => [
            'start_time' => null,
            'end_time' => null,
            'is_full_day' => true,
        ]);
    }
}
