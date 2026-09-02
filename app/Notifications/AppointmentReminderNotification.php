<?php

namespace App\Notifications;

use App\Models\Appointment;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AppointmentReminderNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly Appointment $appointment
    ) {}

    public function via(object $notifiable): array
    {
        return [
            'mail',
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $this->appointment->loadMissing([
            'clientProfile.user',
            'counsellorProfile.user',
            'counsellingService',
        ]);

        $isCounsellor = $notifiable instanceof User
            && (int) $notifiable->id === (int) $this->appointment->counsellorProfile?->user_id;

        $otherPartyLabel = $isCounsellor ? 'Client' : 'Counsellor';
        $otherPartyName = $isCounsellor
            ? $this->appointment->clientProfile?->user?->name
            : $this->appointment->counsellorProfile?->user?->name;

        $serviceName = $this->appointment->counsellingService?->name ?? 'Counselling session';
        $mode = str($this->appointment->mode)->replace('_', ' ')->title()->toString();

        return (new MailMessage)
            ->subject('Appointment Reminder - '.$this->appointment->formattedDate())
            ->greeting('Hello '.$notifiable->name.',')
            ->line('This is a reminder for your upcoming counselling appointment.')
            ->line('Service: '.$serviceName)
            ->line($otherPartyLabel.': '.($otherPartyName ?: 'Not provided'))
            ->line('Date: '.$this->appointment->formattedDate())
            ->line('Time: '.$this->appointment->formattedTimeRange())
            ->line('Mode: '.$mode)
            ->line('Location / Link: '.$this->appointment->meetingDetails())
            ->line('Please be ready on time.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'appointment_id' => $this->appointment->id,
            'appointment_uuid' => $this->appointment->uuid,
            'appointment_date' => $this->appointment->appointment_date?->toDateString(),
            'start_time' => $this->appointment->start_time?->format('H:i'),
            'end_time' => $this->appointment->end_time?->format('H:i'),
            'mode' => $this->appointment->mode,
            'status' => $this->appointment->status,
        ];
    }
}
