<?php

namespace App\Services\Payments;

use App\Models\Invoice;
use App\Models\Payment;

class InvoiceService
{
    public function issue(
        Payment $payment
    ): Invoice {
        $payment->loadMissing(
            'clientProfile.user'
        );

        $invoice = Invoice::query()
            ->firstOrCreate(
                [
                    'payment_id' => $payment->id,
                ],
                [
                    'invoice_number' => 'TMP-'.str()->uuid(),
                    'subtotal' => $payment->amount,
                    'total' => $payment->amount,
                    'currency' => $payment->currency,
                    'status' => Invoice::STATUS_ISSUED,
                    'billing_name' => $payment
                        ->clientProfile
                        ?->user
                        ?->name
                            ?? 'Client',
                    'billing_email' => $payment
                        ->clientProfile
                        ?->user
                        ?->email,
                    'issued_at' => now(),
                ]
            );

        if (
            str_starts_with(
                $invoice->invoice_number,
                'TMP-'
            )
        ) {
            $invoice->forceFill([
                'invoice_number' => sprintf(
                    'INV-%s-%06d',
                    now()->format('Y'),
                    $invoice->id
                ),
            ])->save();
        }

        return $invoice->refresh();
    }

    public function issueReceipt(
        Payment $payment
    ): Invoice {
        $invoice = $this->issue(
            $payment
        );

        if ($invoice->receipt_number) {
            return $invoice;
        }

        $invoice->forceFill([
            'receipt_number' => sprintf(
                'RCT-%s-%06d',
                now()->format('Y'),
                $invoice->id
            ),
            'status' => Invoice::STATUS_PAID,
            'paid_at' => $payment->paid_at ?? now(),
            'receipt_issued_at' => now(),
        ])->save();

        return $invoice->refresh();
    }
}
