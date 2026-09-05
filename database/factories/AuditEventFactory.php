<?php

namespace Database\Factories;

use App\Models\AuditEvent;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AuditEventFactory extends Factory
{
    protected $model = AuditEvent::class;

    public function definition(): array
    {
        return [
            'actor_id' => User::factory(),
            'category' => 'operations',
            'event' => 'test.event',
            'action' => 'test.action',
            'result' => 'success',
            'metadata' => [
                'source' => 'factory',
            ],
            'occurred_at' => now(),
        ];
    }
}
