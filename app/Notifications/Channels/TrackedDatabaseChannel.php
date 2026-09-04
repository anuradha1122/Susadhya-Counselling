<?php

namespace App\Notifications\Channels;

use App\Models\NotificationDelivery;
use App\Notifications\TemplatedNotification;
use Illuminate\Notifications\Channels\DatabaseChannel;
use Throwable;

class TrackedDatabaseChannel
{
    public function __construct(
        private readonly DatabaseChannel $databaseChannel
    ) {}

    public function send(
        object $notifiable,
        TemplatedNotification $notification
    ): mixed {
        $delivery = $this->delivery($notification);

        if (! $delivery) {
            return null;
        }

        $delivery->markAttempting();

        try {
            $result = $this->databaseChannel->send(
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

    private function delivery(
        TemplatedNotification $notification
    ): ?NotificationDelivery {
        return NotificationDelivery::query()
            ->where(
                'notification_dispatch_id',
                $notification->dispatchId
            )
            ->where('channel', 'database')
            ->where('attempt', 1)
            ->first();
    }
}
