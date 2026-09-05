<?php

namespace App\Services\Reports;

use App\Models\Payment;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

class FinancialReportService
{
    private const COLLECTED_STATUSES = [
        'paid',
        'partially_refunded',
        'refunded',
    ];

    public function summary(
        array $filters
    ): array {
        $paymentQuery =
            $this->collectedPaymentQuery(
                $filters
            );

        $paymentCount =
            (clone $paymentQuery)
                ->count();

        $fullyRefunded =
            (clone $paymentQuery)
                ->where(
                    'status',
                    'refunded'
                )
                ->count();

        $partiallyRefunded =
            (clone $paymentQuery)
                ->where(
                    'status',
                    'partially_refunded'
                )
                ->count();

        $reconciliationMismatch =
            (clone $paymentQuery)
                ->where(
                    'reconciliation_status',
                    'mismatch'
                )
                ->count();

        return [
            'payment_count' => $paymentCount,

            'fully_refunded_count' => $fullyRefunded,

            'partially_refunded_count' => $partiallyRefunded,

            'reconciliation_mismatch_count' => $reconciliationMismatch,

            'currency_totals' => $this->currencyTotals(
                $filters
            ),

            'method_breakdown' => $this->methodBreakdown(
                $filters
            ),
        ];
    }

    public function payments(
        array $filters,
        int $perPage = 25
    ): LengthAwarePaginator {
        return $this
            ->collectedPaymentQuery(
                $filters
            )
            ->with([
                'appointment.counsellorProfile.user:id,name',
                'appointment.counsellingService:id,name',
            ])
            ->orderByDesc(
                'paid_at'
            )
            ->paginate($perPage)
            ->withQueryString()
            ->through(
                fn (
                    Payment $payment
                ): array => $this->formatPayment(
                    $payment
                )
            );
    }

    public function exportPayments(
        array $filters
    ): Collection {
        return $this
            ->collectedPaymentQuery(
                $filters
            )
            ->with([
                'appointment.counsellorProfile.user:id,name',
                'appointment.counsellingService:id,name',
            ])
            ->orderBy(
                'paid_at'
            )
            ->get()
            ->map(
                fn (
                    Payment $payment
                ): array => $this->formatPayment(
                    $payment
                )
            );
    }

    public function currencyTotals(
        array $filters
    ): Collection {
        $gross = DB::table(
            'payments'
        )
            ->whereIn(
                'status',
                self::COLLECTED_STATUSES
            )
            ->whereBetween(
                'paid_at',
                [
                    $filters['from']
                        .' 00:00:00',

                    $filters['to']
                        .' 23:59:59',
                ]
            )
            ->when(
                $filters['method'],
                fn (
                    $query,
                    $method
                ) => $query->where(
                    'method',
                    $method
                )
            )
            ->when(
                $filters['currency'],
                fn (
                    $query,
                    $currency
                ) => $query->where(
                    'currency',
                    $currency
                )
            )
            ->select([
                'currency',
            ])
            ->selectRaw(
                'COUNT(id) as payment_count'
            )
            ->selectRaw(
                'SUM(amount) as gross_amount'
            )
            ->groupBy(
                'currency'
            )
            ->get()
            ->keyBy(
                'currency'
            );

        $refunds =
            $this->refundedTotals(
                $filters
            );

        $currencies =
            $gross
                ->keys()
                ->merge(
                    $refunds->keys()
                )
                ->unique()
                ->sort()
                ->values();

        return $currencies
            ->map(
                function (
                    string $currency
                ) use (
                    $gross,
                    $refunds
                ): array {
                    $grossRow =
                        $gross->get(
                            $currency
                        );

                    $refundRow =
                        $refunds->get(
                            $currency
                        );

                    $grossAmount =
                        (float) (
                            $grossRow
                                ?->gross_amount
                            ?? 0
                        );

                    $refundedAmount =
                        (float) (
                            $refundRow
                                ?->refunded_amount
                            ?? 0
                        );

                    return [
                        'currency' => $currency,

                        'payment_count' => (int) (
                            $grossRow
                                ?->payment_count
                            ?? 0
                        ),

                        'gross_amount' => round(
                            $grossAmount,
                            2
                        ),

                        'refunded_amount' => round(
                            $refundedAmount,
                            2
                        ),

                        'net_amount' => round(
                            $grossAmount
                                - $refundedAmount,
                            2
                        ),
                    ];
                }
            );
    }

    public function methodBreakdown(
        array $filters
    ): Collection {
        return DB::table(
            'payments'
        )
            ->whereIn(
                'status',
                self::COLLECTED_STATUSES
            )
            ->whereBetween(
                'paid_at',
                [
                    $filters['from']
                        .' 00:00:00',

                    $filters['to']
                        .' 23:59:59',
                ]
            )
            ->when(
                $filters['method'],
                fn (
                    $query,
                    $method
                ) => $query->where(
                    'method',
                    $method
                )
            )
            ->when(
                $filters['currency'],
                fn (
                    $query,
                    $currency
                ) => $query->where(
                    'currency',
                    $currency
                )
            )
            ->select([
                'method',
                'currency',
            ])
            ->selectRaw(
                'COUNT(id) as payment_count'
            )
            ->selectRaw(
                'SUM(amount) as total_amount'
            )
            ->groupBy(
                'method',
                'currency'
            )
            ->orderBy(
                'method'
            )
            ->get()
            ->map(
                fn (
                    object $row
                ): array => [
                    'method' => $row->method,

                    'currency' => $row->currency,

                    'payment_count' => (int) (
                        $row
                            ->payment_count
                    ),

                    'total_amount' => round(
                        (float) (
                            $row
                                ->total_amount
                        ),
                        2
                    ),
                ]
            );
    }

    private function refundedTotals(
        array $filters
    ): Collection {
        /*
         * If there are no completed refunds in
         * the selected period there is nothing
         * to aggregate, and therefore no reason
         * to resolve an amount column at all.
         */
        $dateColumn =
            $this->refundDateColumn();

        $base = DB::table(
            'refunds as r'
        )
            ->join(
                'payments as p',
                'p.id',
                '=',
                'r.payment_id'
            )
            ->where(
                'r.status',
                'refunded'
            )
            ->whereBetween(
                'r.'.$dateColumn,
                [
                    $filters['from']
                        .' 00:00:00',

                    $filters['to']
                        .' 23:59:59',
                ]
            )
            ->when(
                $filters['method'],
                fn (
                    $query,
                    $method
                ) => $query->where(
                    'p.method',
                    $method
                )
            )
            ->when(
                $filters['currency'],
                fn (
                    $query,
                    $currency
                ) => $query->where(
                    'p.currency',
                    $currency
                )
            );

        if (! (clone $base)->exists()) {
            return collect();
        }

        $amountColumn =
            $this->refundAmountColumn();

        return $base
            ->select([
                'p.currency',
            ])
            ->selectRaw(
                sprintf(
                    'SUM(r.%s) as refunded_amount',
                    $amountColumn
                )
            )
            ->groupBy(
                'p.currency'
            )
            ->get()
            ->keyBy(
                'currency'
            );
    }

    private function collectedPaymentQuery(
        array $filters
    ): Builder {
        return Payment::query()
            ->whereIn(
                'status',
                self::COLLECTED_STATUSES
            )
            ->whereBetween(
                'paid_at',
                [
                    $filters['from']
                        .' 00:00:00',

                    $filters['to']
                        .' 23:59:59',
                ]
            )
            ->when(
                $filters['method'],
                fn (
                    Builder $query,
                    $method
                ) => $query->where(
                    'method',
                    $method
                )
            )
            ->when(
                $filters['currency'],
                fn (
                    Builder $query,
                    $currency
                ) => $query->where(
                    'currency',
                    $currency
                )
            );
    }

    private function refundDateColumn(): string
    {
        foreach (
            [
                'processed_at',
                'refunded_at',
                'completed_at',
                'updated_at',
            ] as $column
        ) {
            if (
                Schema::hasColumn(
                    'refunds',
                    $column
                )
            ) {
                return $column;
            }
        }

        throw new RuntimeException(
            'Unable to determine the completed refund date column.'
        );
    }

    private function refundAmountColumn(): string
    {
        /*
         * Prefer the amount that represents
         * money actually processed.
         *
         * The fallbacks support the existing
         * M14 schema without assuming a
         * generic "amount" column.
         */
        $candidates = [
            'processed_amount',
            'refunded_amount',
            'refund_amount',
            'approved_amount',
            'requested_amount',
            'amount_refunded',
            'amount_approved',
            'amount_requested',
            'amount',
        ];

        foreach (
            $candidates as $column
        ) {
            if (
                Schema::hasColumn(
                    'refunds',
                    $column
                )
            ) {
                return $column;
            }
        }

        $columns =
            Schema::getColumnListing(
                'refunds'
            );

        throw new RuntimeException(
            sprintf(
                'Unable to determine the refund amount column. Existing refund columns: %s',
                implode(
                    ', ',
                    $columns
                )
            )
        );
    }

    private function formatPayment(
        Payment $payment
    ): array {
        return [
            'uuid' => $payment->uuid,

            'paid_at' => $payment
                ->paid_at
                ?->format(
                    'Y-m-d H:i'
                ),

            'method' => $payment->method,

            'provider' => $payment->provider,

            'amount' => $payment->amount,

            'currency' => $payment->currency,

            'status' => $payment->status,

            'reconciliation_status' => $payment
                ->reconciliation_status,

            'appointment_uuid' => $payment
                ->appointment
                ?->uuid,

            'counsellor_name' => $payment
                ->appointment
                ?->counsellorProfile
                ?->user
                ?->name
                ?? 'Not available',

            'service_name' => $payment
                ->appointment
                ?->counsellingService
                ?->name
                ?? 'Legacy / unassigned',
        ];
    }
}
