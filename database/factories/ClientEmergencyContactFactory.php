<?php

namespace Database\Factories;

use App\Models\ClientEmergencyContact;
use App\Models\ClientProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClientEmergencyContactFactory extends Factory
{
    protected $model = ClientEmergencyContact::class;

    public function definition(): array
    {
        return [
            'client_profile_id' => ClientProfile::factory(),
            'name' => fake()->name(),
            'relationship' => fake()->randomElement([
                'Parent',
                'Spouse',
                'Sibling',
                'Friend',
                'Guardian',
            ]),
            'phone' => fake()->numerify('+94 77 ### ####'),
            'alternate_phone' => fake()->optional()->numerify('+94 71 ### ####'),
            'email' => fake()->optional()->safeEmail(),
            'may_contact_in_emergency' => true,
            'is_primary' => true,
            'created_by' => null,
            'updated_by' => null,
        ];
    }

    public function secondary(): static
    {
        return $this->state(fn (): array => [
            'is_primary' => false,
        ]);
    }
}
