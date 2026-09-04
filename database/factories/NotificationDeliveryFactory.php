<?php

namespace Database\Factories;

use App\Models\NotificationDelivery;
use App\Models\NotificationDispatch;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<NotificationDelivery>
 */
class NotificationDeliveryFactory extends Factory
{
    protected $model = NotificationDelivery::class;

    public function definition(): array
    {
        return [
            'notification_dispatch_id' => NotificationDispatch::factory(),
            'channel' => 'database',
            'status' => NotificationDelivery::STATUS_SENT,
            'attempt' => 1,
            'attempted_at' => now(),
            'sent_at' => now(),
            'delivered_at' => null,
            'failed_at' => null,
            'provider_message_id' => null,
            'error_message' => null,
            'metadata' => null,
        ];
    }
}
