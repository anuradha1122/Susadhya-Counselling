<?php

namespace Database\Factories;

use App\Models\Appointment;
use App\Models\ClientProfile;
use App\Models\CounsellingSession;
use App\Models\CounsellorProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CounsellingSession>
 */
class CounsellingSessionFactory extends Factory
{
    protected $model = CounsellingSession::class;

    public function definition(): array
    {
        return [
            'appointment_id' => Appointment::factory(),
            'client_profile_id' => ClientProfile::factory(),
            'counsellor_profile_id' => CounsellorProfile::factory(),
            'status' => CounsellingSession::STATUS_DRAFT,
            'mode' => 'online',
            'started_at' => null,
            'ended_at' => null,
            'completed_at' => null,
            'presenting_summary' => fake()->sentence(),
            'intervention_summary' => null,
            'outcome_summary' => null,
            'client_visible_summary' => null,
            'homework' => null,
            'private_notes' => null,
            'admin_notes' => null,
            'clinical_risk_level' => CounsellingSession::RISK_LOW,
            'follow_up_recommended' => false,
            'follow_up_notes' => null,
            'next_session_recommended_at' => null,
            'created_by' => null,
            'updated_by' => null,
        ];
    }

    public function inProgress(?User $user = null): static
    {
        return $this->state(fn (): array => [
            'status' => CounsellingSession::STATUS_IN_PROGRESS,
            'started_at' => now(),
            'created_by' => $user?->id,
            'updated_by' => $user?->id,
        ]);
    }

    public function completed(?User $user = null): static
    {
        return $this->state(fn (): array => [
            'status' => CounsellingSession::STATUS_COMPLETED,
            'started_at' => now()->subHour(),
            'ended_at' => now(),
            'completed_at' => now(),
            'intervention_summary' => 'Supportive counselling and grounding techniques.',
            'outcome_summary' => 'Client showed improved clarity at the end of session.',
            'client_visible_summary' => 'We discussed coping strategies and next steps.',
            'homework' => 'Practice breathing exercise daily.',
            'created_by' => $user?->id,
            'updated_by' => $user?->id,
        ]);
    }
}
