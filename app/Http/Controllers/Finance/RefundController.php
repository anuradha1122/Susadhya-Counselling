<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\DecideRefundRequest;
use App\Http\Requests\Finance\ProcessRefundRequest;
use App\Models\Refund;
use App\Services\Payments\RefundService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RefundController extends Controller
{
    public function index(
        Request $request
    ): Response {
        $query = Refund::query()
            ->with([
                'payment.clientProfile.user:id,name,email',
                'payment.appointment.counsellingService:id,name',
                'requestedBy:id,name',
                'decidedBy:id,name',
            ]);

        if ($status =
            $request->input('status')) {
            $query->where(
                'status',
                $status
            );
        }

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
                            'refund_number',
                            'like',
                            "%{$search}%"
                        )
                        ->orWhereHas(
                            'payment.clientProfile.user',
                            fn ($query) => $query->where(
                                'name',
                                'like',
                                "%{$search}%"
                            )
                        );
                }
            );
        }

        return Inertia::render(
            'Finance/Refunds/Index',
            [
                'refunds' => $query
                    ->latest('id')
                    ->paginate(20)
                    ->withQueryString(),
                'filters' => [
                    'search' => $request->input(
                        'search',
                        ''
                    ),
                    'status' => $request->input(
                        'status',
                        ''
                    ),
                ],
            ]
        );
    }

    public function decide(
        DecideRefundRequest $request,
        Refund $refund,
        RefundService $refunds
    ): RedirectResponse {
        $validated =
            $request->validated();

        if (
            $validated['decision']
            === 'approve'
        ) {
            $refunds->approve(
                $refund,
                $request->user(),
                (string) $validated[
                    'approved_amount'
                ],
                $validated[
                    'decision_notes'
                ] ?? null
            );
        } else {
            $refunds->reject(
                $refund,
                $request->user(),
                $validated[
                    'decision_notes'
                ]
            );
        }

        return back()->with(
            'success',
            'Refund decision saved successfully.'
        );
    }

    public function process(
        ProcessRefundRequest $request,
        Refund $refund,
        RefundService $refunds
    ): RedirectResponse {
        $refunds->process(
            $refund,
            $request->user(),
            $request->validated(
                'manual_reference'
            )
        );

        return back()->with(
            'success',
            'Refund processed successfully.'
        );
    }
}
