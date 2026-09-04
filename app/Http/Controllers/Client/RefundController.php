<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Client\StoreRefundRequest;
use App\Models\Payment;
use App\Services\Payments\RefundService;
use Illuminate\Http\RedirectResponse;

class RefundController extends Controller
{
    public function store(
        StoreRefundRequest $request,
        Payment $payment,
        RefundService $refunds
    ): RedirectResponse {
        $payment->loadMissing(
            'clientProfile'
        );

        $refunds->request(
            $payment,
            $request->user(),
            (string) $request->validated(
                'requested_amount'
            ),
            $request->validated(
                'reason'
            )
        );

        return to_route(
            'client.payments.index'
        )->with(
            'success',
            'Refund request submitted successfully.'
        );
    }
}
