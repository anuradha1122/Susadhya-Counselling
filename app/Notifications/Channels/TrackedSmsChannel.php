<?php

namespace App\Notifications\Channels;

use App\Models\NotificationDelivery;
use App\Notifications\Contracts\SmsGateway;
use App\Notifications\TemplatedNotification;
use App\Services\Notifications\NotificationPreferenceService;
use Throwable;

class TrackedSmsChannel
{
    public function __construct(
        private readonly SmsGateway $gateway,
        private readonly NotificationPreferenceService $preferences
    ) {}

    public function send(
        object $notifiable,
        TemplatedNotification $notification
    ): void {
        $delivery = NotificationDelivery::query()
            ->where(
                'notification_dispatch_id',
                $notification->dispatchId
            )
            ->where('channel', 'sms')
            ->where('attempt', 1)
            ->first();

        if (! $delivery) {
            return;
        }

        $recipient = $this->preferences->smsRecipient(
            $notifiable
        );

        if (! $recipient) {
            $delivery->markSkipped(
                'No SMS recipient number is available.'
            );

            return;
        }

        $delivery->markAttempting();

        try {
            $result = $this->gateway->send(
                $recipient,
                $notification->toSms($notifiable)
            );

            if ($result->successful) {
                $delivery->markSent(
                    $result->providerMessageId
                );

                return;
            }

            if ($result->status === 'unavailable') {
                $delivery->markUnavailable(
                    $result->error ?? 'SMS service unavailable.'
                );

                return;
            }

            $delivery->markFailed(
                $result->error ?? 'SMS delivery failed.'
            );
        } catch (Throwable $exception) {
            $delivery->markFailed(
                $exception->getMessage()
            );

            report($exception);
        }
    }
}
