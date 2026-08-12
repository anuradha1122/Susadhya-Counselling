<?php

namespace Database\Factories;

use App\Models\ClientPreference;
use App\Models\ClientProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClientPreferenceFactory extends Factory
{
    protected $model = ClientPreference::class;

    public function definition(): array
    {
        return [
            'client_profile_id' => ClientProfile::factory(),
            'preferred_counselling_mode' => fake()->randomElement([
                'online',
                'in_person',
                'no_preference',
            ]),
            'preferred_counsellor_gender' => fake()->randomElement([
                'male',
                'female',
                'no_preference',
            ]),
            'preferred_language' => fake()->randomElement([
                'sinhala',
                'tamil',
                'english',
                'no_preference',
                'other',
            ]),
            'general_availability_notes' => fake()->optional()->sentence(),
            'accessibility_requirements' => fake()->optional()->sentence(),
            'additional_preferences' => fake()->optional()->sentence(),
            'created_by' => null,
            'updated_by' => null,
        ];
    }
}
