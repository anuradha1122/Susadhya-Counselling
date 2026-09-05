<?php

namespace Database\Factories;

use App\Models\PrivacyRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PrivacyRequestFactory extends Factory
{
    protected $model = PrivacyRequest::class;

    public function definition(): array
    {
        return [
            'requester_user_id' => User::factory(),

            'subject_user_id' => User::factory(),

            'type' => PrivacyRequest::TYPE_EXPORT,

            'status' => PrivacyRequest::STATUS_SUBMITTED,

            'request_details' => $this->faker->sentence(),

            'submitted_at' => now(),
        ];
    }

    public function forUser(
        User $user
    ): static {
        return $this->state(fn () => [
            'requester_user_id' => $user->id,
            'subject_user_id' => $user->id,
        ]);
    }
}
