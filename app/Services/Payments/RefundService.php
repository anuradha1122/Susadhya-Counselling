<?php

namespace App\Services\Payments;

use App\Models\Payment;
use App\Models\Refund;
use App\Models\User;
use App\Payments\PaymentGatewayManager;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Throwable;

class RefundService
{
    public function __construct(
        private PaymentGatewayManager $gateways,
        private PaymentEventLogger $events,
    ) {}

    public function request(
        Payment $payment,
        User $client,
        string $amount,
        string $reason
    ): Refund {
        if (
            $payment->clientProfile?->user_id
            !== $client->id
        ) {
            abort(403);
        }

        if (! $payment->canRequestRefund()) {
            throw ValidationException::withMessages([
                'payment' => 'This payment is not eligible for a refund request.',
            ]);
        }

        return DB::transaction(
            function () use (
                $payment,
                $client,
                $amount,
                $reason
            ): Refund {
                $payment = Payment::query()
                    ->lockForUpdate()
                    ->findOrFail(
                        $payment->id
                    );

                $processed =
                    Refund::query()
                        ->where(
                            'payment_id',
                            $payment->id
                        )
                        ->where(
                            'status',
                            Refund::STATUS_REFUNDED
                        )
                        ->sum(
                            'approved_amount'
                        );

                $reserved =
                    Refund::query()
                        ->where(
                            'payment_id',
                            $payment->id
                        )
                        ->whereIn(
                            'status',
                            Refund::activeStatuses()
                        )
                        ->sum(
                            'requested_amount'
                        );

                $available =
                    (float) $payment->amount
                    - (float) $processed
                    - (float) $reserved;

                if (
                    (float) $amount <= 0
                    || (float) $amount
                        > $available
                ) {
                    throw ValidationException::withMessages([
                        'requested_amount' => 'The refund amount exceeds the refundable payment balance.',
                    ]);
                }

                $refund =
                    Refund::query()->create([
                        'payment_id' => $payment->id,
                        'refund_number' => 'TMP-'.str()->uuid(),
                        'requested_by' => $client->id,
                        'requested_amount' => number_format(
                            (float) $amount,
                            2,
                            '.',
                            ''
                        ),
                        'currency' => $payment->currency,
                        'reason' => $reason,
                        'status' => Refund::STATUS_REQUESTED,
                        'requested_at' => now(),
                    ]);

                $refund->forceFill([
                    'refund_number' => sprintf(
                        'REF-%s-%06d',
                        now()->format('Y'),
                        $refund->id
                    ),
                ])->save();

                $this->events->log(
                    'refund_requested',
                    'client',
                    $payment,
                    $refund,
                    $client
                );

                return $refund->refresh();
            }
        );
    }

    public function approve(
        Refund $refund,
        User $financeUser,
        string $amount,
        ?string $notes = null
    ): Refund {
        if (
            $refund->status
            !== Refund::STATUS_REQUESTED
        ) {
            throw ValidationException::withMessages([
                'refund' => 'Only requested refunds can be approved.',
            ]);
        }

        if (
            (float) $amount <= 0
            || (float) $amount
                > (float) $refund
                    ->requested_amount
        ) {
            throw ValidationException::withMessages([
                'approved_amount' => 'Approved amount must be within the requested refund amount.',
            ]);
        }

        $refund->forceFill([
            'approved_amount' => number_format(
                (float) $amount,
                2,
                '.',
                ''
            ),
            'status' => Refund::STATUS_APPROVED,
            'decision_notes' => $notes,
            'decided_by' => $financeUser->id,
            'decided_at' => now(),
        ])->save();

        $this->events->log(
            'refund_approved',
            'finance',
            $refund->payment,
            $refund,
            $financeUser
        );

        return $refund;
    }

    public function reject(
        Refund $refund,
        User $financeUser,
        string $notes
    ): Refund {
        if (
            $refund->status
            !== Refund::STATUS_REQUESTED
        ) {
            throw ValidationException::withMessages([
                'refund' => 'Only requested refunds can be rejected.',
            ]);
        }

        $refund->forceFill([
            'status' => Refund::STATUS_REJECTED,
            'decision_notes' => $notes,
            'decided_by' => $financeUser->id,
            'decided_at' => now(),
        ])->save();

        $this->events->log(
            'refund_rejected',
            'finance',
            $refund->payment,
            $refund,
            $financeUser
        );

        return $refund;
    }

    public function process(
        Refund $refund,
        User $financeUser,
        ?string $manualReference = null
    ): Refund {
        if (
            $refund->status
            !== Refund::STATUS_APPROVED
        ) {
            throw ValidationException::withMessages([
                'refund' => 'Only approved refunds can be processed.',
            ]);
        }

        $payment = $refund->payment;

        if (
            $payment->provider
            === Payment::PROVIDER_MANUAL
            || $payment->provider
                === Payment::PROVIDER_SYSTEM
        ) {
            if (
                $payment->provider
                    === Payment::PROVIDER_MANUAL
                && blank($manualReference)
            ) {
                throw ValidationException::withMessages([
                    'manual_reference' => 'A refund reference is required for manual payments.',
                ]);
            }

            return $this->markRefunded(
                $refund,
                $financeUser,
                providerReference: $manualReference
            );
        }

        $refund->forceFill([
            'status' => Refund::STATUS_PROCESSING,
        ])->save();

        try {
            $result = $this
                ->gateways
                ->driver(
                    $payment->provider
                )
                ->refund(
                    $payment,
                    $refund
                );

            if (
                $result->status
                !== Refund::STATUS_REFUNDED
            ) {
                $refund->forceFill([
                    'status' => Refund::STATUS_FAILED,
                    'failed_at' => now(),
                    'failure_message' => 'The payment provider did not confirm the refund.',
                ])->save();

                return $refund;
            }

            return $this->markRefunded(
                $refund,
                $financeUser,
                $result->providerRefundId,
                $result->providerReference
            );
        } catch (Throwable $exception) {
            $refund->forceFill([
                'status' => Refund::STATUS_FAILED,
                'failed_at' => now(),
                'failure_message' => $exception->getMessage(),
            ])->save();

            $this->events->log(
                'refund_failed',
                'provider',
                $payment,
                $refund,
                $financeUser,
                [
                    'message' => $exception->getMessage(),
                ]
            );

            throw $exception;
        }
    }

    private function markRefunded(
        Refund $refund,
        User $financeUser,
        ?string $providerRefundId = null,
        ?string $providerReference = null,
    ): Refund {
        return DB::transaction(
            function () use (
                $refund,
                $financeUser,
                $providerRefundId,
                $providerReference
            ): Refund {
                $refund->forceFill([
                    'status' => Refund::STATUS_REFUNDED,
                    'provider_refund_id' => $providerRefundId,
                    'provider_reference' => $providerReference,
                    'processed_at' => now(),
                ])->save();

                $payment = Payment::query()
                    ->lockForUpdate()
                    ->findOrFail(
                        $refund->payment_id
                    );

                $refunded =
                    Refund::query()
                        ->where(
                            'payment_id',
                            $payment->id
                        )
                        ->where(
                            'status',
                            Refund::STATUS_REFUNDED
                        )
                        ->sum(
                            'approved_amount'
                        );

                $payment->forceFill([
                    'status' => (float) $refunded
                            >= (float) $payment->amount
                                ? Payment::STATUS_REFUNDED
                                : Payment::STATUS_PARTIALLY_REFUNDED,
                    'updated_by' => $financeUser->id,
                ])->save();

                $this->events->log(
                    'refund_processed',
                    'finance',
                    $payment,
                    $refund,
                    $financeUser
                );

                return $refund->refresh();
            }
        );
    }
}
