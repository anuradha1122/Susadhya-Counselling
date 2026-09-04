<?php

namespace App\Observers;

use App\Enums\NotificationEventType;
use App\Models\Refund;
use App\Models\User;
use App\Services\Notifications\NotificationDispatcher;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

class RefundNotificationObserver implements ShouldHandleEventsAfterCommit
{
    public function __construct(
        private readonly NotificationDispatcher $dispatcher
    ) {}

    public function created(Refund $refund): void
    {
        if ($refund->status !== 'requested') {
            return;
        }

        $financeUsers = User::query()
            ->whereHas('roles', function ($query): void {
                $query->whereIn('name', [
                    'finance_admin',
                    'super_admin',
                ]);
            })
            ->get();

        foreach ($financeUsers as $financeUser) {
            $this->dispatcher->dispatch(
                user: $financeUser,
                event: NotificationEventType::RefundRequested,
                templateKey: 'refund.requested.finance',
                payload: [
                    'amount' => number_format(
                        (float) $refund->amount,
                        2
                    ),
                    'currency' => $refund
                        ->payment
                        ?->currency ?? 'LKR',
                ],
                source: $refund,
                url: '/finance/refunds',
                deduplicationKey: sprintf(
                    'refund:%s:requested:finance:%s',
                    $refund->getKey(),
                    $financeUser->getKey()
                )
            );
        }
    }

    public function updated(Refund $refund): void
    {
        if (! $refund->wasChanged('status')) {
            return;
        }

        $map = match ($refund->status) {
            'approved' => [
                NotificationEventType::RefundApproved,
                'refund.approved.client',
            ],

            'rejected' => [
                NotificationEventType::RefundRejected,
                'refund.rejected.client',
            ],

            'refunded' => [
                NotificationEventType::RefundCompleted,
                'refund.refunded.client',
            ],

            default => null,
        };

        if (! $map) {
            return;
        }

        $refund->loadMissing([
            'payment.appointment.clientProfile.user',
        ]);

        $client = $refund
            ->payment
            ?->appointment
            ?->clientProfile
            ?->user;

        if (! $client instanceof User) {
            return;
        }

        [$event, $template] = $map;

        $this->dispatcher->dispatch(
            user: $client,
            event: $event,
            templateKey: $template,
            payload: [
                'amount' => number_format(
                    (float) $refund->amount,
                    2
                ),
                'currency' => $refund
                    ->payment
                    ?->currency ?? 'LKR',
            ],
            source: $refund,
            url: '/client/payments',
            deduplicationKey: sprintf(
                'refund:%s:%s:%s',
                $refund->getKey(),
                $refund->status,
                $refund->updated_at?->timestamp
                    ?? now()->timestamp
            )
        );
    }
}
