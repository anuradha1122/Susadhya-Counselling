<?php

namespace Tests\Feature\Notifications;

use App\Models\Appointment;
use App\Models\ClientProfile;
use App\Models\CounsellorProfile;
use App\Models\Payment;
use App\Models\User;
use Database\Seeders\NotificationTemplateSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_paid_payment_produces_traceable_client_notification(): void
    {
        config([
            'notifications.email.enabled' => false,
            'notifications.sms.enabled' => false,
        ]);

        $this->seed(
            NotificationTemplateSeeder::class
        );

        $clientUser = User::factory()->create();

        $clientProfile = ClientProfile::factory()
            ->create([
                'user_id' => $clientUser->id,
            ]);

        $counsellorUser = User::factory()->create();

        $counsellorProfile = CounsellorProfile::factory()
            ->create([
                'user_id' => $counsellorUser->id,
            ]);

        $appointment = Appointment::factory()
            ->withCounsellingService()
            ->create([
                'client_profile_id' => $clientProfile->id,
                'counsellor_profile_id' => $counsellorProfile->id,
            ]);

        $payment = Payment::factory()->create([
            'appointment_id' => $appointment->id,
            'status' => 'paid',
            'amount' => 4500,
            'currency' => 'LKR',
        ]);

        $this->assertDatabaseHas(
            'notification_dispatches',
            [
                'user_id' => $clientUser->id,
                'event_type' => 'payment.paid',
                'source_id' => (string) $payment->getKey(),
            ]
        );

        $this->assertDatabaseHas(
            'notification_deliveries',
            [
                'channel' => 'database',
                'status' => 'sent',
            ]
        );
    }
}
