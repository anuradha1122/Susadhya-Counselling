<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\StoreManualPaymentRequest;
use App\Models\Appointment;
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
        $query = Payment::query()
            ->with([
                'clientProfile.user:id,name,email',
                'appointment.counsellingService:id,name',
                'invoice',
            ]);

        if ($search = trim(
            (string) $request->input(
                'search'
            )
        )) {
            $query->where(
                function ($query) use (
                    $search
                ): void {
                    $query
                        ->where(
                            'provider_reference',
                            'like',
                            "%{$search}%"
                        )
                        ->orWhereHas(
                            'clientProfile.user',
                            fn ($query) => $query
                                ->where(
                                    'name',
                                    'like',
                                    "%{$search}%"
                                )
                                ->orWhere(
                                    'email',
                                    'like',
                                    "%{$search}%"
                                )
                        )
                        ->orWhereHas(
                            'invoice',
                            fn ($query) => $query
                                ->where(
                                    'invoice_number',
                                    'like',
                                    "%{$search}%"
                                )
                                ->orWhere(
                                    'receipt_number',
                                    'like',
                                    "%{$search}%"
                                )
                        );
                }
            );
        }

        if ($status =
            $request->input('status')) {
            $query->where(
                'status',
                $status
            );
        }

        if ($reconciliation =
            $request->input(
                'reconciliation'
            )) {
            $query->where(
                'reconciliation_status',
                $reconciliation
            );
        }

        $payments = $query
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        $unpaidAppointments =
            Appointment::query()
                ->whereNotIn(
                    'status',
                    [
                        Appointment::STATUS_CANCELLED,
                        Appointment::STATUS_RESCHEDULED,
                    ]
                )
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
                ->with([
                    'clientProfile.user:id,name,email',
                    'counsellingService:id,name,price,currency',
                ])
                ->orderByDesc(
                    'appointment_date'
                )
                ->limit(100)
                ->get();

        return Inertia::render(
            'Finance/Payments/Index',
            [
                'payments' => $payments,
                'unpaidAppointments' => $unpaidAppointments,
                'filters' => [
                    'search' => $request->input(
                        'search',
                        ''
                    ),
                    'status' => $request->input(
                        'status',
                        ''
                    ),
                    'reconciliation' => $request->input(
                        'reconciliation',
                        ''
                    ),
                ],
                'statuses' => Payment::statuses(),
            ]
        );
    }

    public function storeManual(
        StoreManualPaymentRequest $request,
        PaymentService $payments
    ): RedirectResponse {
        $appointment =
            Appointment::query()
                ->findOrFail(
                    $request->integer(
                        'appointment_id'
                    )
                );

        $payments->recordManual(
            $appointment,
            $request->user(),
            $request->validated(
                'reference'
            ),
            $request->validated(
                'paid_at'
            )
        );

        return to_route(
            'finance.payments.index'
        )->with(
            'success',
            'Manual payment recorded successfully.'
        );
    }

    public function reconcile(
        Request $request,
        Payment $payment,
        PaymentReconciliationService $service
    ): RedirectResponse {
        abort_unless(
            $request->user()->can(
                'payments.reconciliation.manage'
            ),
            403
        );

        $service->reconcile(
            $payment,
            $request->user()
        );

        return back()->with(
            'success',
            'Payment reconciliation completed.'
        );
    }

    public function invoice(
        Payment $payment,
        FinancialDocumentService $documents
    ): Response {
        abort_unless(
            $payment->invoice,
            404
        );

        return $documents->invoice(
            $payment->invoice
        );
    }

    public function receipt(
        Payment $payment,
        FinancialDocumentService $documents
    ): Response {
        abort_unless(
            $payment->invoice
                ?->receipt_number,
            404
        );

        return $documents->receipt(
            $payment->invoice
        );
    }
}
