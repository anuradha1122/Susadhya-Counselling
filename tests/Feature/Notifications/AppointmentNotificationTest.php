<?php

namespace Tests\Feature\Notifications;

use App\Models\Appointment;
use App\Models\ClientProfile;
use App\Models\CounsellorProfile;
use App\Models\User;
use Database\Seeders\NotificationTemplateSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AppointmentNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_creating_appointment_notifies_client_and_counsellor(): void
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
                'status' => 'pending',
            ]);

        $this->assertDatabaseHas(
            'notification_dispatches',
            [
                'user_id' => $clientUser->id,
                'event_type' => 'appointment.booked',
                'source_id' => (string) $appointment->getKey(),
            ]
        );

        $this->assertDatabaseHas(
            'notification_dispatches',
            [
                'user_id' => $counsellorUser->id,
                'event_type' => 'appointment.booked',
                'source_id' => (string) $appointment->getKey(),
            ]
        );

        $this->assertSame(
            1,
            $clientUser
                ->notifications()
                ->count()
        );

        $this->assertSame(
            1,
            $counsellorUser
                ->notifications()
                ->count()
        );
    }

    public function test_confirmed_appointment_notifies_client(): void
    {
        config([
            'notifications.email.enabled' => false,
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
                'status' => 'pending',
            ]);

        $appointment->update([
            'status' => 'confirmed',
        ]);

        $this->assertDatabaseHas(
            'notification_dispatches',
            [
                'user_id' => $clientUser->id,
                'event_type' => 'appointment.confirmed',
                'source_id' => (string) $appointment->getKey(),
            ]
        );
    }
}
