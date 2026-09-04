<?php

namespace Tests\Feature\Notifications;

use App\Models\Appointment;
use App\Models\ClientProfile;
use App\Models\CounsellorProfile;
use App\Models\User;
use App\Notifications\AppointmentReminderNotification;
use Carbon\Carbon;
use Database\Seeders\NotificationTemplateSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AppointmentReminderCommandTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_reminder_command_sends_once_for_upcoming_appointment(): void
    {
        Carbon::setTestNow(
            Carbon::parse(
                '2026-09-10 09:00:00',
                config('app.timezone')
            )
        );

        $this->seed(
            NotificationTemplateSeeder::class
        );

        Notification::fake();

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

                'appointment_date' => now()
                    ->addDay()
                    ->toDateString(),

                'start_time' => '09:00:00',
                'end_time' => '10:00:00',

                'timezone' => config('app.timezone'),

                'mode' => 'online',

                'status' => 'confirmed',

                /*
                 * Make the reminder due now.
                 */
                'reminder_scheduled_at' => now()
                    ->subMinute(),

                'reminder_sent_at' => null,
            ]);

        /*
         * First execution should send the reminder.
         */
        $this->artisan(
            'appointments:send-reminders'
        )->assertSuccessful();

        $appointment->refresh();

        $this->assertNotNull(
            $appointment->reminder_sent_at
        );

        Notification::assertSentTo(
            $clientUser,
            AppointmentReminderNotification::class
        );

        Notification::assertSentTo(
            $counsellorUser,
            AppointmentReminderNotification::class
        );

        /*
         * Capture how many reminder notifications were
         * sent after the first execution.
         */
        $clientReminderCount = Notification::sent(
            $clientUser,
            AppointmentReminderNotification::class
        )->count();

        $counsellorReminderCount = Notification::sent(
            $counsellorUser,
            AppointmentReminderNotification::class
        )->count();

        $this->assertSame(
            1,
            $clientReminderCount
        );

        $this->assertSame(
            1,
            $counsellorReminderCount
        );

        /*
         * Second execution must not send the same
         * appointment reminder again because
         * reminder_sent_at has already been recorded.
         */
        $this->artisan(
            'appointments:send-reminders'
        )->assertSuccessful();

        $this->assertSame(
            1,
            Notification::sent(
                $clientUser,
                AppointmentReminderNotification::class
            )->count()
        );

        $this->assertSame(
            1,
            Notification::sent(
                $counsellorUser,
                AppointmentReminderNotification::class
            )->count()
        );
    }

    public function test_dry_run_does_not_send_or_mark_reminder_as_sent(): void
    {
        Carbon::setTestNow(
            Carbon::parse(
                '2026-09-10 09:00:00',
                config('app.timezone')
            )
        );

        $this->seed(
            NotificationTemplateSeeder::class
        );

        /*
        * Fake notifications while creating the fixture because
        * appointment creation itself legitimately sends M15
        * booking notifications.
        */
        Notification::fake();

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

                'appointment_date' => now()
                    ->addDay()
                    ->toDateString(),

                'start_time' => '09:00:00',
                'end_time' => '10:00:00',

                'timezone' => config('app.timezone'),

                'mode' => 'online',

                'status' => 'confirmed',

                'reminder_scheduled_at' => now()
                    ->subMinute(),

                'reminder_sent_at' => null,
            ]);

        /*
        * Reset the notification fake here.
        *
        * The appointment creation above legitimately triggered
        * appointment-booked TemplatedNotification instances.
        * They are unrelated to the dry-run command and must not
        * be included in this assertion.
        */
        Notification::fake();

        $this->artisan(
            'appointments:send-reminders',
            [
                '--dry-run' => true,
            ]
        )->assertSuccessful();

        $appointment->refresh();

        /*
        * Dry-run must not mark the reminder as sent.
        */
        $this->assertNull(
            $appointment->reminder_sent_at
        );

        /*
        * Dry-run must not send any notification.
        */
        Notification::assertNothingSent();
    }
}
