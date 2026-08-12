<?php

namespace Database\Factories;

use App\Models\ClientProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClientProfileFactory extends Factory
{
    protected $model = ClientProfile::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'preferred_name' => fake()->optional()->firstName(),
            'date_of_birth' => fake()->dateTimeBetween('-60 years', '-18 years')->format('Y-m-d'),
            'gender' => fake()->randomElement([
                'male',
                'female',
                'non_binary',
                'other',
                'prefer_not_to_say',
            ]),
            'pronouns' => fake()->optional()->randomElement([
                'he/him',
                'she/her',
                'they/them',
            ]),
            'alternate_phone' => fake()->optional()->numerify('+94 77 ### ####'),
            'address_line_1' => fake()->streetAddress(),
            'address_line_2' => fake()->optional()->secondaryAddress(),
            'city' => fake()->city(),
            'district' => fake()->randomElement([
                'Colombo',
                'Gampaha',
                'Kegalle',
                'Ratnapura',
            ]),
            'province' => fake()->randomElement([
                'Western',
                'Sabaragamuwa',
                'Central',
            ]),
            'postal_code' => fake()->postcode(),
            'preferred_language' => fake()->randomElement([
                'sinhala',
                'tamil',
                'english',
                'no_preference',
            ]),
            'preferred_contact_method' => fake()->randomElement([
                'email',
                'phone',
                'sms',
                'whatsapp',
                'no_preference',
            ]),
            'occupation' => fake()->jobTitle(),
            'marital_status' => fake()->randomElement([
                'single',
                'married',
                'separated',
                'divorced',
                'widowed',
                'other',
                'prefer_not_to_say',
            ]),
            'profile_completed_at' => now(),
            'terms_accepted_at' => now(),
            'privacy_policy_accepted_at' => now(),
            'communication_consent' => true,
            'communication_consent_at' => now(),
            'emergency_contact_permission' => true,
            'emergency_contact_permission_at' => now(),
            'privacy_preferences' => [
                'allow_email_updates' => true,
                'allow_sms_updates' => false,
                'allow_whatsapp_updates' => false,
                'share_profile_with_assigned_counsellor' => true,
            ],
            'status' => 'active',
            'created_by' => null,
            'updated_by' => null,
            'archived_at' => null,
            'archived_by' => null,
        ];
    }

    public function incomplete(): static
    {
        return $this->state(fn (): array => [
            'date_of_birth' => null,
            'address_line_1' => null,
            'city' => null,
            'district' => null,
            'province' => null,
            'profile_completed_at' => null,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (): array => [
            'status' => 'inactive',
        ]);
    }

    public function archived(): static
    {
        return $this->state(fn (): array => [
            'status' => 'archived',
            'archived_at' => now(),
        ]);
    }
}
