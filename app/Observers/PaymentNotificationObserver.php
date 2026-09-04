<?php

namespace App\Observers;

use App\Enums\NotificationEventType;
use App\Models\Payment;
use App\Models\User;
use App\Services\Notifications\NotificationDispatcher;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

class PaymentNotificationObserver implements ShouldHandleEventsAfterCommit
{
    public function __construct(
        private readonly NotificationDispatcher $dispatcher
    ) {}

    public function created(Payment $payment): void
    {
        if (in_array(
            $payment->status,
            ['paid', 'failed'],
            true
        )) {
            $this->sendStatusNotification($payment);
        }
    }

    public function updated(Payment $payment): void
    {
        if (! $payment->wasChanged('status')) {
            return;
        }

        if (! in_array(
            $payment->status,
            ['paid', 'failed'],
            true
        )) {
            return;
        }

        $this->sendStatusNotification($payment);
    }

    private function sendStatusNotification(
        Payment $payment
    ): void {
        $payment->loadMissing([
            'appointment.clientProfile.user',
        ]);

        $client = $payment
            ->appointment
            ?->clientProfile
            ?->user;

        if (! $client instanceof User) {
            return;
        }

        $amount = number_format(
            (float) $payment->amount,
            2
        );

        if ($payment->status === 'paid') {
            $this->dispatcher->dispatch(
                user: $client,
                event: NotificationEventType::PaymentPaid,
                templateKey: 'payment.paid.client',
                payload: [
                    'amount' => $amount,
                    'currency' => $payment->currency ?? 'LKR',
                    'reference' => $payment->reference
                        ?? $payment->provider_payment_id
                        ?? 'Payment',
                ],
                source: $payment,
                url: '/client/payments',
                deduplicationKey: sprintf(
                    'payment:%s:paid:%s',
                    $payment->getKey(),
                    $payment->updated_at?->timestamp
                        ?? $payment->created_at?->timestamp
                        ?? now()->timestamp
                )
            );

            return;
        }

        $this->dispatcher->dispatch(
            user: $client,
            event: NotificationEventType::PaymentFailed,
            templateKey: 'payment.failed.client',
            payload: [
                'amount' => $amount,
                'currency' => $payment->currency ?? 'LKR',
            ],
            source: $payment,
            url: '/client/payments',
            deduplicationKey: sprintf(
                'payment:%s:failed:%s',
                $payment->getKey(),
                $payment->updated_at?->timestamp
                    ?? $payment->created_at?->timestamp
                    ?? now()->timestamp
            )
        );
    }
}
