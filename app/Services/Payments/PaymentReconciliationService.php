<?php

namespace App\Services\Payments;

use App\Models\Payment;
use App\Models\User;
use App\Payments\PaymentGatewayManager;
use Throwable;

class PaymentReconciliationService
{
    public function __construct(
        private PaymentGatewayManager $gateways,
        private PaymentService $payments,
        private PaymentEventLogger $events,
    ) {}

    public function reconcile(
        Payment $payment,
        ?User $actor = null
    ): Payment {
        if (
            $payment->provider
            === Payment::PROVIDER_MANUAL
            || $payment->provider
                === Payment::PROVIDER_SYSTEM
        ) {
            $payment->forceFill([
                'reconciliation_status' => Payment::RECONCILIATION_MATCHED,
                'reconciled_at' => now(),
            ])->save();

            return $payment;
        }

        try {
            $gateway = $this
                ->gateways
                ->driver(
                    $payment->provider
                );

            $result =
                $gateway->retrieve(
                    $payment
                );

            $payment =
                $this->payments
                    ->applyGatewayResult(
                        $payment,
                        $result,
                        'reconciliation'
                    );

            $this->events->log(
                'payment_reconciled',
                'finance',
                $payment,
                actor: $actor
            );

            return $payment;
        } catch (Throwable $exception) {
            $payment->forceFill([
                'reconciliation_status' => Payment::RECONCILIATION_UNAVAILABLE,
                'reconciled_at' => now(),
            ])->save();

            $this->events->log(
                'payment_reconciliation_unavailable',
                'system',
                $payment,
                actor: $actor,
                metadata: [
                    'message' => $exception->getMessage(),
                ]
            );

            throw $exception;
        }
    }
}
