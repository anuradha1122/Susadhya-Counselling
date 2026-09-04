<?php

namespace Database\Factories;

use App\Models\NotificationTemplate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<NotificationTemplate>
 */
class NotificationTemplateFactory extends Factory
{
    protected $model = NotificationTemplate::class;

    public function definition(): array
    {
        $key = 'test.'.fake()->unique()->slug(2, '.');

        return [
            'key' => $key,
            'name' => fake()->sentence(3),
            'subject' => fake()->sentence(4),
            'in_app_body' => 'Test notification for {{ name }}.',
            'email_body' => 'Test email notification for {{ name }}.',
            'sms_body' => 'Test notification for {{ name }}.',
            'variables' => ['name'],
            'is_active' => true,
        ];
    }
}
