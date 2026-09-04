<?php

namespace Tests\Feature\Notifications;

use App\Enums\NotificationEventType;
use App\Models\NotificationDelivery;
use App\Models\NotificationDispatch;
use App\Models\User;
use App\Services\Notifications\NotificationDispatcher;
use Database\Seeders\NotificationTemplateSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class NotificationDispatcherTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_traceable_in_app_notification(): void
    {
        Mail::fake();

        config([
            'notifications.email.enabled' => false,
            'notifications.sms.enabled' => false,
        ]);

        $this->seed(
            NotificationTemplateSeeder::class
        );

        $user = User::factory()->create();

        $dispatch = app(
            NotificationDispatcher::class
        )->dispatch(
            user: $user,
            event: NotificationEventType::PaymentPaid,
            templateKey: 'payment.paid.client',
            payload: [
                'amount' => '4,500.00',
                'currency' => 'LKR',
                'reference' => 'PAY-001',
            ],
            url: '/client/payments',
            deduplicationKey: 'test-payment-paid-1'
        );

        $this->assertNotNull($dispatch);

        $this->assertDatabaseHas(
            'notification_dispatches',
            [
                'id' => $dispatch->id,
                'user_id' => $user->id,
                'event_type' => 'payment.paid',
                'template_key' => 'payment.paid.client',
            ]
        );

        $this->assertDatabaseHas(
            'notification_deliveries',
            [
                'notification_dispatch_id' => $dispatch->id,
                'channel' => 'database',
                'status' => NotificationDelivery::STATUS_SENT,
            ]
        );

        $this->assertDatabaseCount(
            'notifications',
            1
        );

        $notification = $user
            ->notifications()
            ->first();

        $this->assertSame(
            'Payment received',
            $notification->data['title']
        );

        $this->assertFalse(
            $notification->read()
        );
    }

    public function test_deduplication_key_prevents_duplicate_dispatch(): void
    {
        config([
            'notifications.email.enabled' => false,
            'notifications.sms.enabled' => false,
        ]);

        $this->seed(
            NotificationTemplateSeeder::class
        );

        $user = User::factory()->create();

        $dispatcher = app(
            NotificationDispatcher::class
        );

        for ($i = 0; $i < 2; $i++) {
            $dispatcher->dispatch(
                user: $user,
                event: NotificationEventType::PaymentPaid,
                templateKey: 'payment.paid.client',
                payload: [
                    'amount' => '1,000.00',
                    'currency' => 'LKR',
                    'reference' => 'PAY-001',
                ],
                deduplicationKey: 'same-key'
            );
        }

        $this->assertSame(
            1,
            NotificationDispatch::query()
                ->where(
                    'deduplication_key',
                    'same-key'
                )
                ->count()
        );

        $this->assertDatabaseCount(
            'notifications',
            1
        );
    }
}
