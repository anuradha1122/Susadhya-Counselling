<?php

namespace Database\Factories;

use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SupportTicket>
 */
class SupportTicketFactory extends Factory
{
    protected $model =
        SupportTicket::class;

    public function definition(): array
    {
        return [
            'requester_id' => User::factory(),

            'category' => SupportTicket::CATEGORY_GENERAL,

            'subject' => fake()->sentence(5),

            'description' => fake()->paragraph(),

            'priority' => SupportTicket::PRIORITY_NORMAL,

            'status' => SupportTicket::STATUS_OPEN,

            'last_activity_at' => now(),
        ];
    }

    public function guest(): static
    {
        return $this->state(
            fn (): array => [
                'requester_id' => null,
                'guest_name' => fake()->name(),
                'guest_email' => fake()->safeEmail(),
            ]
        );
    }

    public function complaint(): static
    {
        return $this->state(
            fn (): array => [
                'category' => SupportTicket::CATEGORY_COMPLAINT,
            ]
        );
    }

    public function resolved(): static
    {
        return $this->state(
            fn (): array => [
                'status' => SupportTicket::STATUS_RESOLVED,
                'resolved_at' => now(),
            ]
        );
    }
}
