<?php

namespace Database\Factories;

use App\Models\CounsellorProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CounsellorProfile>
 */
class CounsellorProfileFactory extends Factory
{
    protected $model = CounsellorProfile::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'registration_number' => fake()->unique()
                ->numerify('COUN-#####'),
            'professional_title' => fake()->randomElement([
                'Counsellor',
                'Senior Counsellor',
                'Clinical Counsellor',
            ]),
            'nic' => fake()->unique()->numerify(
                '############'
            ),
            'date_of_birth' => fake()->dateTimeBetween(
                '-60 years',
                '-23 years'
            ),
            'gender' => fake()->randomElement([
                'male',
                'female',
                'other',
                'prefer_not_to_say',
            ]),
            'years_of_experience' => fake()
                ->numberBetween(0, 30),
            'biography' => fake()->paragraph(),
            'address' => fake()->address(),
            'city' => fake()->city(),
            'status' => 'active',
        ];
    }
}
