<?php

namespace Database\Factories;

use App\Models\ClientIntake;
use App\Models\ClientProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ClientIntake>
 */
class ClientIntakeFactory extends Factory
{
    protected $model = ClientIntake::class;

    public function definition(): array
    {
        return [
            'client_profile_id' => ClientProfile::factory(),
            'status' => ClientIntake::STATUS_DRAFT,
            'presenting_concerns' => fake()->paragraph(),
            'current_symptoms' => fake()->paragraph(),
            'counselling_goals' => fake()->sentence(),
            'preferred_session_mode' => 'either',
            'previous_counselling' => false,
            'previous_counselling_notes' => null,
            'medication_notes' => null,
            'emergency_contact_name' => fake()->name(),
            'emergency_contact_phone' => '+94 77 123 4567',
            'emergency_contact_relationship' => 'Family member',
            'consent_terms_accepted' => false,
            'consent_privacy_accepted' => false,
            'consent_telehealth_accepted' => false,
            'consent_data_processing_accepted' => false,
            'consent_given_at' => null,
            'consent_version' => 'MVP-1.0',
            'risk_level' => ClientIntake::RISK_LOW,
            'risk_notes' => null,
            'submitted_at' => null,
            'reviewed_by' => null,
            'reviewed_at' => null,
            'reviewer_notes' => null,
        ];
    }

    public function submitted(): static
    {
        return $this->state(fn (): array => [
            'status' => ClientIntake::STATUS_SUBMITTED,
            'consent_terms_accepted' => true,
            'consent_privacy_accepted' => true,
            'consent_telehealth_accepted' => true,
            'consent_data_processing_accepted' => true,
            'consent_given_at' => now(),
            'submitted_at' => now(),
        ]);
    }

    public function reviewed(?User $reviewer = null): static
    {
        return $this->state(fn (): array => [
            'status' => ClientIntake::STATUS_REVIEWED,
            'reviewed_by' => $reviewer?->id ?? User::factory(),
            'reviewed_at' => now(),
            'reviewer_notes' => 'Reviewed intake.',
        ]);
    }
}
