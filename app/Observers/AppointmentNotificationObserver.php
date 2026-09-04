<?php

namespace App\Observers;

use App\Enums\NotificationEventType;
use App\Models\Appointment;
use App\Models\User;
use App\Services\Notifications\NotificationDispatcher;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

class AppointmentNotificationObserver implements ShouldHandleEventsAfterCommit
{
    public function __construct(
        private readonly NotificationDispatcher $dispatcher
    ) {}

    public function created(Appointment $appointment): void
    {
        $appointment->loadMissing([
            'clientProfile.user',
            'counsellorProfile.user',
            'counsellingService',
        ]);

        if ($appointment->rescheduled_from_appointment_id) {
            $this->sendRescheduled($appointment);

            return;
        }

        $payload = $this->payload($appointment);

        $client = $appointment->clientProfile?->user;

        if ($client instanceof User) {
            $this->dispatcher->dispatch(
                user: $client,
                event: NotificationEventType::AppointmentBooked,
                templateKey: 'appointment.booked.client',
                payload: $payload,
                source: $appointment,
                url: '/client/appointments',
                deduplicationKey: sprintf(
                    'appointment:%s:booked:client:%s',
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
                event: NotificationEventType::AppointmentBooked,
                templateKey: 'appointment.booked.counsellor',
                payload: $payload,
                source: $appointment,
                url: '/counsellor/appointments',
                deduplicationKey: sprintf(
                    'appointment:%s:booked:counsellor:%s',
                    $appointment->getKey(),
                    $counsellor->getKey()
                )
            );
        }
    }

    public function updated(Appointment $appointment): void
    {
        if (! $appointment->wasChanged('status')) {
            return;
        }

        $appointment->loadMissing([
            'clientProfile.user',
            'counsellorProfile.user',
            'counsellingService',
        ]);

        match ($appointment->status) {
            'confirmed' => $this->sendConfirmed($appointment),
            'cancelled' => $this->sendCancelled($appointment),

            /*
             * Rescheduling is emitted by creation of the replacement
             * appointment so the client does not receive duplicates.
             */
            default => null,
        };
    }

    private function sendConfirmed(
        Appointment $appointment
    ): void {
        $client = $appointment->clientProfile?->user;

        if (! $client instanceof User) {
            return;
        }

        $this->dispatcher->dispatch(
            user: $client,
            event: NotificationEventType::AppointmentConfirmed,
            templateKey: 'appointment.confirmed.client',
            payload: $this->payload($appointment),
            source: $appointment,
            url: '/client/appointments',
            deduplicationKey: sprintf(
                'appointment:%s:confirmed:%s:%s',
                $appointment->getKey(),
                $client->getKey(),
                $appointment->updated_at?->timestamp ?? now()->timestamp
            )
        );
    }

    private function sendRescheduled(
        Appointment $appointment
    ): void {
        $payload = $this->payload($appointment);

        $client = $appointment->clientProfile?->user;

        if ($client instanceof User) {
            $this->dispatcher->dispatch(
                user: $client,
                event: NotificationEventType::AppointmentRescheduled,
                templateKey: 'appointment.rescheduled.client',
                payload: $payload,
                source: $appointment,
                url: '/client/appointments',
                deduplicationKey: sprintf(
                    'appointment:%s:rescheduled:client:%s',
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
                event: NotificationEventType::AppointmentRescheduled,
                templateKey: 'appointment.rescheduled.counsellor',
                payload: $payload,
                source: $appointment,
                url: '/counsellor/appointments',
                deduplicationKey: sprintf(
                    'appointment:%s:rescheduled:counsellor:%s',
                    $appointment->getKey(),
                    $counsellor->getKey()
                )
            );
        }
    }

    private function sendCancelled(
        Appointment $appointment
    ): void {
        $payload = $this->payload($appointment);

        foreach ([
            [
                $appointment->clientProfile?->user,
                'appointment.cancelled.client',
                '/client/appointments',
                'client',
            ],
            [
                $appointment->counsellorProfile?->user,
                'appointment.cancelled.counsellor',
                '/counsellor/appointments',
                'counsellor',
            ],
        ] as [$user, $template, $url, $audience]) {
            if (! $user instanceof User) {
                continue;
            }

            $this->dispatcher->dispatch(
                user: $user,
                event: NotificationEventType::AppointmentCancelled,
                templateKey: $template,
                payload: $payload,
                source: $appointment,
                url: $url,
                deduplicationKey: sprintf(
                    'appointment:%s:cancelled:%s:%s:%s',
                    $appointment->getKey(),
                    $audience,
                    $user->getKey(),
                    $appointment->updated_at?->timestamp ?? now()->timestamp
                )
            );
        }
    }

    private function payload(Appointment $appointment): array
    {
        return [
            'service_name' => $appointment
                ->counsellingService
                ?->name ?? 'Counselling service',

            'starts_at' => $appointment->starts_at
                ? $appointment->starts_at
                    ->timezone(config('app.timezone'))
                    ->format('d M Y, h:i A')
                : 'Scheduled time',

            'mode' => match ($appointment->mode) {
                'online' => 'Online',
                'in_person' => 'In person',
                default => ucfirst(
                    str_replace('_', ' ', (string) $appointment->mode)
                ),
            },
        ];
    }
}
