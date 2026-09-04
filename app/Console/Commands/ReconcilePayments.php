<?php

namespace App\Console\Commands;

use App\Models\Payment;
use App\Services\Payments\PaymentReconciliationService;
use Illuminate\Console\Command;
use Throwable;

class ReconcilePayments extends Command
{
    protected $signature =
        'payments:reconcile
        {--payment= : Reconcile one payment UUID}
        {--limit=100 : Maximum records to process}';

    protected $description =
        'Reconcile gateway payment records with provider status';

    public function handle(
        PaymentReconciliationService $service
    ): int {
        $query = Payment::query()
            ->where(
                'method',
                Payment::METHOD_GATEWAY
            );

        if ($uuid =
            $this->option('payment')) {
            $query->where(
                'uuid',
                $uuid
            );
        } else {
            $query->whereIn(
                'status',
                [
                    Payment::STATUS_PENDING,
                    Payment::STATUS_PROCESSING,
                    Payment::STATUS_PAID,
                ]
            );
        }

        $payments = $query
            ->limit(
                max(
                    1,
                    (int) $this->option(
                        'limit'
                    )
                )
            )
            ->get();

        $processed = 0;
        $failed = 0;

        foreach ($payments as $payment) {
            try {
                $service->reconcile(
                    $payment
                );

                $processed++;
            } catch (Throwable $exception) {
                $failed++;

                $this->warn(
                    "{$payment->uuid}: {$exception->getMessage()}"
                );
            }
        }

        $this->info(
            "Reconciled {$processed} payment(s); {$failed} failed."
        );

        return $failed > 0
            ? self::FAILURE
            : self::SUCCESS;
    }
}
