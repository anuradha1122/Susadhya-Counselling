<?php

namespace Database\Factories;

use App\Models\PrivacyRequest;
use App\Models\PrivacyRequestEvent;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PrivacyRequestEventFactory extends Factory
{
    protected $model =
        PrivacyRequestEvent::class;

    public function definition(): array
    {
        return [
            'privacy_request_id' => PrivacyRequest::factory(),

            'actor_id' => User::factory(),

            'event' => 'submitted',

            'to_status' => PrivacyRequest::STATUS_SUBMITTED,

            'metadata' => [],
        ];
    }
}
