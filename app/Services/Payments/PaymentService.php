<?php

namespace App\Services\Payments;

use App\Models\Appointment;
use App\Models\Payment;
use App\Models\User;
use App\Payments\Data\GatewayPaymentResult;
use App\Payments\PaymentGatewayManager;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class PaymentService
{
    public function __construct(
        private AppointmentFeeService $fees,
        private InvoiceService $invoices,
        private PaymentEventLogger $events,
        private PaymentGatewayManager $gateways,
    ) {}

    public function createGatewayPayment(
        Appointment $appointment,
        User $client
    ): array {
        $clientProfile = $client->clientProfile;

        if (
            ! $clientProfile
            || $appointment->client_profile_id
                !== $clientProfile->id
        ) {
            abort(403);
        }

        if (
            ! in_array(
                $appointment->status,
                [
                    Appointment::STATUS_PENDING,
                    Appointment::STATUS_CONFIRMED,
                ],
                true
            )
        ) {
            throw ValidationException::withMessages([
                'appointment' => 'Payment cannot be started for this appointment status.',
            ]);
        }

        $existing = Payment::query()
            ->where(
                'appointment_id',
                $appointment->id
            )
            ->whereIn(
                'status',
                [
                    Payment::STATUS_PENDING,
                    Payment::STATUS_PROCESSING,
                    Payment::STATUS_PAID,
                    Payment::STATUS_PARTIALLY_REFUNDED,
                    Payment::STATUS_REFUNDED,
                ]
            )
            ->latest('id')
            ->first();

        if ($existing) {
            if (
                in_array(
                    $existing->status,
                    [
                        Payment::STATUS_PENDING,
                        Payment::STATUS_PROCESSING,
                    ],
                    true
                )
            ) {
                return [
                    'payment' => $existing,
                    'redirect_url' => route(
                        'client.payments.checkout',
                        $existing
                    ),
                ];
            }

            throw ValidationException::withMessages([
                'appointment' => 'This appointment already has a completed payment.',
            ]);
        }

        $fee = $this->fees->snapshot(
            $appointment
        );

        $amount = number_format(
            (float) $fee['amount'],
            2,
            '.',
            ''
        );

        if ((float) $amount <= 0) {
            return [
                'payment' => $this->createWaivedPayment(
                    $appointment,
                    $client,
                    $amount,
                    $fee['currency']
                ),
                'redirect_url' => route(
                    'client.payments.index'
                ),
            ];
        }

        $payment = DB::transaction(
            function () use (
                $appointment,
                $client,
                $clientProfile,
                $amount,
                $fee
            ): Payment {
                $payment =
                    Payment::query()->create([
                        'appointment_id' => $appointment->id,
                        'client_profile_id' => $clientProfile->id,
                        'provider' => config(
                            'payments.gateway'
                        ),
                        'method' => Payment::METHOD_GATEWAY,
                        'idempotency_key' => (string) Str::uuid(),
                        'amount' => $amount,
                        'currency' => strtoupper(
                            $fee['currency']
                        ),
                        'status' => Payment::STATUS_PENDING,
                        'reconciliation_status' => Payment::RECONCILIATION_PENDING,
                        'created_by' => $client->id,
                        'updated_by' => $client->id,
                    ]);

                $this->invoices->issue(
                    $payment
                );

                $this->events->log(
                    'payment_intent_created',
                    'client',
                    $payment,
                    actor: $client
                );

                return $payment;
            }
        );

        try {
            $result = $this
                ->gateways
                ->default()
                ->createIntent(
                    $payment
                );

            $payment->forceFill([
                'provider_payment_id' => $result->providerPaymentId,
                'provider_reference' => $result->providerReference,
                'provider_status' => $result->status,
                'provider_amount' => $result->amount,
                'provider_currency' => $result->currency,
                'provider_synced_at' => now(),
                'metadata' => $result->metadata,
            ])->save();

            return [
                'payment' => $payment->refresh(),
                'redirect_url' => $result->redirectUrl
                        ?? route(
                            'client.payments.index'
                        ),
            ];
        } catch (Throwable $exception) {
            $payment->forceFill([
                'status' => Payment::STATUS_FAILED,
                'failed_at' => now(),
                'failure_message' => $exception->getMessage(),
            ])->save();

            $this->events->log(
                'gateway_intent_failed',
                'system',
                $payment,
                metadata: [
                    'message' => $exception->getMessage(),
                ]
            );

            throw $exception;
        }
    }

    public function applyGatewayResult(
        Payment $payment,
        GatewayPaymentResult $result,
        string $source = 'provider'
    ): Payment {
        return DB::transaction(
            function () use (
                $payment,
                $result,
                $source
            ): Payment {
                $payment = Payment::query()
                    ->lockForUpdate()
                    ->findOrFail(
                        $payment->id
                    );

                $payment->forceFill([
                    'provider_payment_id' => $result
                        ->providerPaymentId
                            ?? $payment
                                ->provider_payment_id,
                    'provider_reference' => $result
                        ->providerReference
                            ?? $payment
                                ->provider_reference,
                    'provider_status' => $result->status,
                    'provider_amount' => $result->amount,
                    'provider_currency' => strtoupper(
                        $result->currency
                    ),
                    'provider_synced_at' => now(),
                ]);

                if (
                    $result->status
                    === Payment::STATUS_PAID
                ) {
                    if (
                        number_format(
                            (float) $payment->amount,
                            2,
                            '.',
                            ''
                        )
                        !== number_format(
                            (float) $result->amount,
                            2,
                            '.',
                            ''
                        )
                        || strtoupper(
                            $payment->currency
                        )
                        !== strtoupper(
                            $result->currency
                        )
                    ) {
                        $payment->status =
                            Payment::STATUS_PROCESSING;

                        $payment->reconciliation_status =
                            Payment::RECONCILIATION_MISMATCH;

                        $payment->reconciled_at =
                            now();

                        $payment->save();

                        $this->events->log(
                            'payment_reconciliation_mismatch',
                            $source,
                            $payment,
                            metadata: [
                                'expected_amount' => $payment->amount,
                                'provider_amount' => $result->amount,
                                'expected_currency' => $payment->currency,
                                'provider_currency' => $result->currency,
                            ]
                        );

                        return $payment;
                    }

                    $payment->status =
                        Payment::STATUS_PAID;

                    $payment->paid_at ??= now();

                    $payment->reconciliation_status =
                        Payment::RECONCILIATION_MATCHED;

                    $payment->reconciled_at =
                        now();

                    $payment->failure_code = null;
                    $payment->failure_message = null;

                    $payment->save();

                    $this->invoices
                        ->issueReceipt(
                            $payment
                        );

                    $this->events->log(
                        'payment_paid',
                        $source,
                        $payment
                    );

                    return $payment;
                }

                if (
                    $result->status
                    === Payment::STATUS_FAILED
                ) {
                    $payment->status =
                        Payment::STATUS_FAILED;

                    $payment->failed_at =
                        now();

                    $payment->reconciliation_status =
                        Payment::RECONCILIATION_MATCHED;

                    $payment->reconciled_at =
                        now();

                    $payment->save();

                    $this->events->log(
                        'payment_failed',
                        $source,
                        $payment
                    );

                    return $payment;
                }

                $payment->status =
                    $result->status
                    === Payment::STATUS_PROCESSING
                        ? Payment::STATUS_PROCESSING
                        : Payment::STATUS_PENDING;

                $payment->save();

                return $payment;
            }
        );
    }

    public function recordManual(
        Appointment $appointment,
        User $financeUser,
        string $reference,
        ?string $paidAt = null,
    ): Payment {
        $existing = Payment::query()
            ->where(
                'appointment_id',
                $appointment->id
            )
            ->whereIn(
                'status',
                [
                    Payment::STATUS_PAID,
                    Payment::STATUS_PARTIALLY_REFUNDED,
                    Payment::STATUS_REFUNDED,
                ]
            )
            ->exists();

        if ($existing) {
            throw ValidationException::withMessages([
                'appointment_id' => 'This appointment already has a completed payment.',
            ]);
        }

        $fee = $this->fees->snapshot(
            $appointment
        );

        return DB::transaction(
            function () use (
                $appointment,
                $financeUser,
                $reference,
                $paidAt,
                $fee
            ): Payment {
                $payment =
                    Payment::query()->create([
                        'appointment_id' => $appointment->id,
                        'client_profile_id' => $appointment
                            ->client_profile_id,
                        'provider' => Payment::PROVIDER_MANUAL,
                        'method' => Payment::METHOD_MANUAL,
                        'provider_reference' => $reference,
                        'idempotency_key' => (string) Str::uuid(),
                        'amount' => $fee['amount'],
                        'currency' => strtoupper(
                            $fee['currency']
                        ),
                        'status' => Payment::STATUS_PAID,
                        'provider_status' => Payment::STATUS_PAID,
                        'provider_amount' => $fee['amount'],
                        'provider_currency' => strtoupper(
                            $fee['currency']
                        ),
                        'reconciliation_status' => Payment::RECONCILIATION_MATCHED,
                        'provider_synced_at' => now(),
                        'paid_at' => $paidAt
                                ? now()->parse(
                                    $paidAt
                                )
                                : now(),
                        'reconciled_at' => now(),
                        'created_by' => $financeUser->id,
                        'updated_by' => $financeUser->id,
                    ]);

                $this->invoices
                    ->issueReceipt(
                        $payment
                    );

                $this->events->log(
                    'manual_payment_recorded',
                    'finance',
                    $payment,
                    actor: $financeUser
                );

                return $payment;
            }
        );
    }

    private function createWaivedPayment(
        Appointment $appointment,
        User $client,
        string $amount,
        string $currency
    ): Payment {
        return DB::transaction(
            function () use (
                $appointment,
                $client,
                $amount,
                $currency
            ): Payment {
                $payment =
                    Payment::query()->create([
                        'appointment_id' => $appointment->id,
                        'client_profile_id' => $appointment
                            ->client_profile_id,
                        'provider' => Payment::PROVIDER_SYSTEM,
                        'method' => Payment::METHOD_WAIVED,
                        'idempotency_key' => (string) Str::uuid(),
                        'amount' => $amount,
                        'currency' => strtoupper($currency),
                        'status' => Payment::STATUS_PAID,
                        'provider_status' => Payment::STATUS_PAID,
                        'provider_amount' => $amount,
                        'provider_currency' => strtoupper($currency),
                        'reconciliation_status' => Payment::RECONCILIATION_MATCHED,
                        'paid_at' => now(),
                        'reconciled_at' => now(),
                        'created_by' => $client->id,
                        'updated_by' => $client->id,
                    ]);

                $this->invoices
                    ->issueReceipt(
                        $payment
                    );

                return $payment;
            }
        );
    }
}
