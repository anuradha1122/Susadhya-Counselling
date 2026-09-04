<?php

namespace Tests\Feature\Notifications;

use App\Enums\NotificationEventType;
use App\Models\User;
use App\Services\Notifications\NotificationDispatcher;
use Database\Seeders\NotificationTemplateSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationReadStateTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_mark_own_notification_as_read(): void
    {
        config([
            'notifications.email.enabled' => false,
            'notifications.sms.enabled' => false,
        ]);

        $this->seed(
            NotificationTemplateSeeder::class
        );

        $user = User::factory()->create();

        app(NotificationDispatcher::class)
            ->dispatch(
                user: $user,
                event: NotificationEventType::PaymentPaid,
                templateKey: 'payment.paid.client',
                payload: [
                    'amount' => '5,000.00',
                    'currency' => 'LKR',
                    'reference' => 'TEST',
                ],
                deduplicationKey: 'read-state-test'
            );

        $notification = $user
            ->notifications()
            ->firstOrFail();

        $this
            ->actingAs($user)
            ->patch(
                route(
                    'notifications.read',
                    $notification->id
                )
            )
            ->assertRedirect();

        $this->assertNotNull(
            $notification->fresh()->read_at
        );
    }

    public function test_user_cannot_mark_another_users_notification_as_read(): void
    {
        config([
            'notifications.email.enabled' => false,
        ]);

        $this->seed(
            NotificationTemplateSeeder::class
        );

        $owner = User::factory()->create();

        $attacker = User::factory()->create();

        app(NotificationDispatcher::class)
            ->dispatch(
                user: $owner,
                event: NotificationEventType::PaymentPaid,
                templateKey: 'payment.paid.client',
                payload: [
                    'amount' => '5,000.00',
                    'currency' => 'LKR',
                    'reference' => 'TEST',
                ],
                deduplicationKey: 'ownership-test'
            );

        $notification = $owner
            ->notifications()
            ->firstOrFail();

        $this
            ->actingAs($attacker)
            ->patch(
                route(
                    'notifications.read',
                    $notification->id
                )
            )
            ->assertNotFound();

        $this->assertNull(
            $notification->fresh()->read_at
        );
    }
}
