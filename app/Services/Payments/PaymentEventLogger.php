<?php

namespace App\Services\Payments;

use App\Models\Payment;
use App\Models\PaymentEvent;
use App\Models\Refund;
use App\Models\User;

class PaymentEventLogger
{
    public function log(
        string $eventType,
        string $source,
        ?Payment $payment = null,
        ?Refund $refund = null,
        ?User $actor = null,
        array $metadata = [],
        ?string $providerEventId = null,
        string $status = 'recorded',
    ): PaymentEvent {
        $request = app()->runningInConsole()
            ? null
            : request();

        return PaymentEvent::query()->create([
            'payment_id' => $payment?->id,
            'refund_id' => $refund?->id,
            'actor_id' => $actor?->id,
            'source' => $source,
            'event_type' => $eventType,
            'provider_event_id' => $providerEventId,
            'status' => $status,
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
            'metadata' => $metadata ?: null,
            'created_at' => now(),
        ]);
    }
}
