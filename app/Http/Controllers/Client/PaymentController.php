<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Invoice;
use App\Models\Payment;
use App\Services\Payments\FinancialDocumentService;
use App\Services\Payments\PaymentReconciliationService;
use App\Services\Payments\PaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Symfony\Component\HttpFoundation\Response;

class PaymentController extends Controller
{
    public function index(
        Request $request
    ): InertiaResponse {
        $client = $request->user();
        $profile = $client->clientProfile;

        abort_unless(
            $profile,
            404
        );

        $payments = Payment::query()
            ->where(
                'client_profile_id',
                $profile->id
            )
            ->with([
                'appointment.counsellingService:id,name',
                'invoice',
                'refunds',
            ])
            ->latest('id')
            ->paginate(12)
            ->withQueryString();

        $appointments = Appointment::query()
            ->where(
                'client_profile_id',
                $profile->id
            )
            ->whereIn(
                'status',
                [
                    Appointment::STATUS_PENDING,
                    Appointment::STATUS_CONFIRMED,
                ]
            )
            ->with([
                'counsellingService:id,name,price,currency',
            ])
            ->whereDoesntHave(
                'payments',
                fn ($query) => $query->whereIn(
                    'status',
                    [
                        Payment::STATUS_PAID,
                        Payment::STATUS_PARTIALLY_REFUNDED,
                        Payment::STATUS_REFUNDED,
                    ]
                )
            )
            ->orderBy(
                'appointment_date'
            )
            ->orderBy(
                'start_time'
            )
            ->get();

        return Inertia::render(
            'Client/Payments/Index',
            [
                'payments' => $payments,
                'appointments' => $appointments,
            ]
        );
    }

    public function store(
        Request $request,
        Appointment $appointment,
        PaymentService $payments
    ): RedirectResponse {
        $result =
            $payments->createGatewayPayment(
                $appointment,
                $request->user()
            );

        return redirect()
            ->to(
                $result[
                    'redirect_url'
                ]
            );
    }

    public function checkout(
        Request $request,
        Payment $payment
    ): InertiaResponse {
        $this->assertOwner(
            $request,
            $payment
        );

        abort_unless(
            $payment->provider
                === 'sandbox',
            404
        );

        return Inertia::render(
            'Client/Payments/Checkout',
            [
                'payment' => $payment->load([
                    'appointment.counsellingService:id,name',
                ]),
            ]
        );
    }

    public function completeSandbox(
        Request $request,
        Payment $payment,
        PaymentReconciliationService $reconciliation
    ): RedirectResponse {
        $this->assertOwner(
            $request,
            $payment
        );

        abort_unless(
            $payment->provider
                === 'sandbox',
            404
        );

        abort_if(
            app()->environment(
                'production'
            ),
            404
        );

        $validated =
            $request->validate([
                'result' => [
                    'required',
                    'in:success,failed',
                ],
            ]);

        $metadata =
            $payment->metadata ?? [];

        $metadata[
            'sandbox_remote_status'
        ] = $validated['result']
            === 'success'
                ? Payment::STATUS_PAID
                : Payment::STATUS_FAILED;

        $payment->forceFill([
            'metadata' => $metadata,
        ])->save();

        $reconciliation->reconcile(
            $payment,
            $request->user()
        );

        return to_route(
            'client.payments.index'
        )->with(
            'success',
            $validated['result']
                === 'success'
                    ? 'Sandbox payment completed successfully.'
                    : 'Sandbox payment was marked as failed.'
        );
    }

    public function invoice(
        Request $request,
        Payment $payment,
        FinancialDocumentService $documents
    ): Response {
        $this->assertOwner(
            $request,
            $payment
        );

        $invoice = $payment->invoice;

        abort_unless(
            $invoice instanceof Invoice,
            404
        );

        return $documents->invoice(
            $invoice
        );
    }

    public function receipt(
        Request $request,
        Payment $payment,
        FinancialDocumentService $documents
    ): Response {
        $this->assertOwner(
            $request,
            $payment
        );

        $invoice = $payment->invoice;

        abort_unless(
            $invoice
            && $invoice->receipt_number,
            404
        );

        return $documents->receipt(
            $invoice
        );
    }

    private function assertOwner(
        Request $request,
        Payment $payment
    ): void {
        abort_unless(
            $request->user()
                ->clientProfile?->id
            === $payment
                ->client_profile_id,
            403
        );
    }
}
