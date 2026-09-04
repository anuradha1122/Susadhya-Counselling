<?php

namespace App\Notifications\Channels;

use App\Models\NotificationDelivery;
use App\Notifications\TemplatedNotification;
use Illuminate\Notifications\Channels\MailChannel;
use Throwable;

class TrackedMailChannel
{
    public function __construct(
        private readonly MailChannel $mailChannel
    ) {}

    public function send(
        object $notifiable,
        TemplatedNotification $notification
    ): mixed {
        $delivery = NotificationDelivery::query()
            ->where(
                'notification_dispatch_id',
                $notification->dispatchId
            )
            ->where('channel', 'mail')
            ->where('attempt', 1)
            ->first();

        if (! $delivery) {
            return null;
        }

        $delivery->markAttempting();

        try {
            $result = $this->mailChannel->send(
                $notifiable,
                $notification
            );

            $delivery->markSent();

            return $result;
        } catch (Throwable $exception) {
            $delivery->markFailed(
                $exception->getMessage()
            );

            report($exception);

            return null;
        }
    }
}
