<?php

namespace Database\Factories;

use App\Models\Appointment;
use App\Models\ClientProfile;
use App\Models\SessionFeedback;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SessionFeedback>
 */
class SessionFeedbackFactory extends Factory
{
    protected $model =
        SessionFeedback::class;

    public function definition(): array
    {
        return [
            /*
             * Tests/domain code should override these together
             * when relationship consistency matters.
             */
            'appointment_id' => Appointment::factory(),

            'client_profile_id' => ClientProfile::factory(),

            'overall_rating' => fake()->numberBetween(
                1,
                5
            ),

            'technical_rating' => fake()->numberBetween(
                1,
                5
            ),

            'comment' => fake()->paragraph(),

            'would_recommend' => true,

            'consent_to_follow_up' => false,

            'submitted_at' => now(),
        ];
    }
}
