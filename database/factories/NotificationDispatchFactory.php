<?php

namespace Database\Factories;

use App\Enums\NotificationEventType;
use App\Models\NotificationDispatch;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<NotificationDispatch>
 */
class NotificationDispatchFactory extends Factory
{
    protected $model = NotificationDispatch::class;

    public function definition(): array
    {
        return [
            'id' => (string) Str::uuid(),
            'user_id' => User::factory(),
            'event_type' => NotificationEventType::AppointmentBooked->value,
            'template_key' => 'appointment.booked.client',
            'channels' => ['database'],
            'source_type' => 'appointment',
            'source_id' => (string) Str::uuid(),
            'url' => '/client/appointments',
            'payload' => [
                'service_name' => 'Counselling service',
                'starts_at' => now()->addDay()->format('d M Y, h:i A'),
            ],
            'deduplication_key' => (string) Str::uuid(),
            'dispatched_at' => now(),
        ];
    }
}
