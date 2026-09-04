<?php

namespace App\Services\Payments;

use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Symfony\Component\HttpFoundation\Response;

class FinancialDocumentService
{
    public function invoice(
        Invoice $invoice
    ): Response {
        $invoice->loadMissing([
            'payment.appointment.counsellingService',
            'payment.clientProfile.user',
        ]);

        return Pdf::loadView(
            'finance.invoice',
            [
                'invoice' => $invoice,
            ]
        )->download(
            $invoice->invoice_number.'.pdf'
        );
    }

    public function receipt(
        Invoice $invoice
    ): Response {
        abort_unless(
            filled(
                $invoice->receipt_number
            ),
            404
        );

        $invoice->loadMissing([
            'payment.appointment.counsellingService',
            'payment.clientProfile.user',
        ]);

        return Pdf::loadView(
            'finance.receipt',
            [
                'invoice' => $invoice,
            ]
        )->download(
            $invoice->receipt_number.'.pdf'
        );
    }
}
