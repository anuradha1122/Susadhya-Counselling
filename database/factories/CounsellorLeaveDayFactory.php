<?php

namespace Database\Factories;

use App\Models\CounsellorLeaveDay;
use App\Models\CounsellorProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

class CounsellorLeaveDayFactory extends Factory
{
    protected $model = CounsellorLeaveDay::class;

    public function definition(): array
    {
        return [
            'counsellor_profile_id' => CounsellorProfile::factory(),
            'leave_date' => now()->addWeeks(2)->toDateString(),
            'start_time' => null,
            'end_time' => null,
            'is_full_day' => true,
            'reason' => 'Leave',
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

    public function partialDay(): static
    {
        return $this->state(fn (): array => [
            'start_time' => '14:00',
            'end_time' => '16:00',
            'is_full_day' => false,
        ]);
    }
}
