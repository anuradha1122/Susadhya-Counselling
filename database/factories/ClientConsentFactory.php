<?php

namespace Database\Factories;

use App\Models\ClientConsent;
use App\Models\ClientProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClientConsentFactory extends Factory
{
    protected $model = ClientConsent::class;

    public function definition(): array
    {
        return [
            'client_profile_id' => ClientProfile::factory(),
            'consent_type' => fake()->randomElement([
                ClientConsent::TYPE_TERMS,
                ClientConsent::TYPE_PRIVACY_POLICY,
                ClientConsent::TYPE_COMMUNICATION,
                ClientConsent::TYPE_EMERGENCY_CONTACT,
            ]),
            'version' => 'module-05-initial',
            'accepted_at' => now(),
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Feature test',
            'metadata' => [
                'source' => 'factory',
            ],
            'recorded_by' => null,
        ];
    }

    public function terms(): static
    {
        return $this->state(fn (): array => [
            'consent_type' => ClientConsent::TYPE_TERMS,
        ]);
    }

    public function privacyPolicy(): static
    {
        return $this->state(fn (): array => [
            'consent_type' => ClientConsent::TYPE_PRIVACY_POLICY,
        ]);
    }

    public function communication(): static
    {
        return $this->state(fn (): array => [
            'consent_type' => ClientConsent::TYPE_COMMUNICATION,
        ]);
    }

    public function emergencyContact(): static
    {
        return $this->state(fn (): array => [
            'consent_type' => ClientConsent::TYPE_EMERGENCY_CONTACT,
        ]);
    }
}
