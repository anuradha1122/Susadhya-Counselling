<?php

namespace App\Console\Commands;

use App\Models\Appointment;
use App\Notifications\AppointmentReminderNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

class SendAppointmentReminders extends Command
{
    protected $signature = 'appointments:send-reminders
        {--dry-run : Show due reminders without sending notifications or marking them as sent}';

    protected $description = 'Send due appointment reminder notifications to clients and counsellors.';

    public function handle(): int
    {
        $appointments = Appointment::query()
            ->with([
                'clientProfile.user',
                'counsellorProfile.user',
                'counsellingService',
            ])
            ->reminderDue()
            ->orderBy('appointment_date')
            ->orderBy('start_time')
            ->get();

        if ($appointments->isEmpty()) {
            $this->info('No due appointment reminders found.');

            return self::SUCCESS;
        }

        $sentNotifications = 0;
        $processedAppointments = 0;
        $skippedAppointments = 0;

        foreach ($appointments as $appointment) {
            $recipients = $appointment->reminderRecipients();

            if ($recipients->isEmpty()) {
                $skippedAppointments++;

                $this->warn("Skipped appointment {$appointment->id}: no active reminder recipients.");

                continue;
            }

            if ($this->option('dry-run')) {
                $this->line(
                    "Due reminder: appointment {$appointment->id} on {$appointment->formattedDate()} {$appointment->formattedTimeRange()} for {$recipients->count()} recipient(s)."
                );

                $processedAppointments++;

                continue;
            }

            Notification::send(
                $recipients,
                new AppointmentReminderNotification($appointment)
            );

            $appointment->markReminderSent();

            $sentNotifications += $recipients->count();
            $processedAppointments++;
        }

        if ($this->option('dry-run')) {
            $this->info("Dry run completed. {$processedAppointments} appointment reminder(s) are due. {$skippedAppointments} skipped.");

            return self::SUCCESS;
        }

        $this->info("Reminder command completed. {$processedAppointments} appointment(s) processed. {$sentNotifications} notification(s) sent. {$skippedAppointments} appointment(s) skipped.");

        return self::SUCCESS;
    }
}
