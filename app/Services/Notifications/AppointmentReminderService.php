<?php

namespace App\Services\Notifications;

use App\Enums\NotificationEventType;
use App\Models\Appointment;
use App\Models\User;

class AppointmentReminderService
{
    public function __construct(
        private readonly NotificationDispatcher $dispatcher
    ) {}

    public function send(Appointment $appointment): void
    {
        $appointment->loadMissing([
            'clientProfile.user',
            'counsellorProfile.user',
            'counsellingService',
        ]);

        $startsAt = $appointment->starts_at
            ? $appointment->starts_at
                ->timezone(config('app.timezone'))
                ->format('d M Y, h:i A')
            : 'your scheduled time';

        $serviceName = $appointment
            ->counsellingService
            ?->name ?? 'counselling';

        $client = $appointment
            ->clientProfile
            ?->user;

        if ($client instanceof User) {
            $this->dispatcher->dispatch(
                user: $client,
                event: NotificationEventType::AppointmentReminder,
                templateKey: 'appointment.reminder.client',
                payload: [
                    'service_name' => $serviceName,
                    'starts_at' => $startsAt,
                ],
                source: $appointment,
                url: '/client/appointments',
                deduplicationKey: sprintf(
                    'appointment:%s:reminder:client:%s',
                    $appointment->getKey(),
                    $client->getKey()
                )
            );
        }

        $counsellor = $appointment
            ->counsellorProfile
            ?->user;

        if ($counsellor instanceof User) {
            $this->dispatcher->dispatch(
                user: $counsellor,
                event: NotificationEventType::AppointmentReminder,
                templateKey: 'appointment.reminder.counsellor',
                payload: [
                    'service_name' => $serviceName,
                    'starts_at' => $startsAt,
                ],
                source: $appointment,
                url: '/counsellor/appointments',
                deduplicationKey: sprintf(
                    'appointment:%s:reminder:counsellor:%s',
                    $appointment->getKey(),
                    $counsellor->getKey()
                )
            );
        }
    }
}
