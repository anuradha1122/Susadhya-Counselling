<?php

namespace Database\Factories;

use App\Models\ClientCase;
use App\Models\ClientProfile;
use App\Models\CounsellorProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ClientCase>
 */
class ClientCaseFactory extends Factory
{
    protected $model = ClientCase::class;

    public function definition(): array
    {
        return [
            'client_profile_id' => ClientProfile::factory(),

            'counsellor_profile_id' => CounsellorProfile::factory(),

            'opened_by' => User::factory(),

            'status' => ClientCase::STATUS_OPEN,

            'summary' => fake()->paragraph(),

            'formulation' => fake()->paragraph(),

            'risk_level' => ClientCase::RISK_LOW,

            'risk_flag' => false,

            'risk_notes' => null,

            'opened_at' => now(),

            'closed_at' => null,

            'closed_by' => null,
        ];
    }
}
