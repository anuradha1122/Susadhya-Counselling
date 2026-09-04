<?php

namespace Tests\Feature\Notifications;

use App\Enums\NotificationEventType;
use App\Models\NotificationPreference;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationPreferencesTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_view_notification_preferences(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get(
                route(
                    'notifications.preferences.edit'
                )
            );

        $response->assertOk();
    }

    public function test_user_can_update_own_preferences(): void
    {
        $user = User::factory()->create();

        $payload = collect(
            NotificationEventType::cases()
        )->map(
            fn (NotificationEventType $event) => [
                'event_type' => $event->value,
                'in_app_enabled' => false,
                'email_enabled' => false,
                'sms_enabled' => false,
            ]
        )->values()->all();

        $response = $this
            ->actingAs($user)
            ->put(
                route(
                    'notifications.preferences.update'
                ),
                [
                    'preferences' => $payload,
                ]
            );

        $response->assertRedirect();

        $this->assertDatabaseHas(
            'notification_preferences',
            [
                'user_id' => $user->id,
                'event_type' => NotificationEventType::PaymentPaid->value,

                /*
                 * Payment paid is critical, so in-app
                 * cannot be disabled.
                 */
                'in_app_enabled' => true,

                'email_enabled' => false,
                'sms_enabled' => false,
            ]
        );
    }

    public function test_user_cannot_update_another_users_preferences(): void
    {
        $user = User::factory()->create();

        $other = User::factory()->create();

        NotificationPreference::factory()
            ->create([
                'user_id' => $other->id,
                'event_type' => NotificationEventType::PaymentPaid->value,
                'email_enabled' => true,
            ]);

        $payload = collect(
            NotificationEventType::cases()
        )->map(
            fn (NotificationEventType $event) => [
                'event_type' => $event->value,
                'in_app_enabled' => true,
                'email_enabled' => false,
                'sms_enabled' => false,
            ]
        )->values()->all();

        $this
            ->actingAs($user)
            ->put(
                route(
                    'notifications.preferences.update'
                ),
                [
                    'preferences' => $payload,
                ]
            )
            ->assertRedirect();

        $this->assertDatabaseHas(
            'notification_preferences',
            [
                'user_id' => $other->id,
                'event_type' => NotificationEventType::PaymentPaid->value,
                'email_enabled' => true,
            ]
        );
    }
}
