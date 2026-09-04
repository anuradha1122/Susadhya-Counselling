<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\PaymentEvent;
use App\Payments\Data\GatewayPaymentResult;
use App\Payments\PaymentGatewayManager;
use App\Services\Payments\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class PaymentWebhookController extends Controller
{
    public function __invoke(
        Request $request,
        string $provider,
        PaymentGatewayManager $gateways,
        PaymentService $payments
    ): JsonResponse {
        try {
            $gateway =
                $gateways->driver(
                    $provider
                );
        } catch (Throwable) {
            abort(404);
        }

        abort_unless(
            $gateway->verifyWebhook(
                $request
            ),
            401
        );

        $data =
            $gateway->parseWebhook(
                $request
            );

        $event = PaymentEvent::query()
            ->firstOrCreate(
                [
                    'provider_event_id' => $data->eventId,
                ],
                [
                    'source' => 'provider',
                    'event_type' => 'provider_callback',
                    'status' => 'received',
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'metadata' => [
                        'provider' => $provider,
                    ],
                    'created_at' => now(),
                ]
            );

        if (! $event->wasRecentlyCreated) {
            return response()->json([
                'ok' => true,
                'duplicate' => true,
            ]);
        }

        $payment = Payment::query()
            ->where(
                'uuid',
                $data->paymentUuid
            )
            ->where(
                'provider',
                $provider
            )
            ->firstOrFail();

        abort_unless(
            $payment->provider_payment_id
                === $data->providerPaymentId,
            422
        );

        try {
            $payments->applyGatewayResult(
                $payment,
                new GatewayPaymentResult(
                    status: $data->status,
                    providerPaymentId: $data->providerPaymentId,
                    providerReference: $payment
                        ->provider_reference,
                    amount: $data->amount,
                    currency: $data->currency,
                    metadata: $data->metadata,
                )
            );

            $event->forceFill([
                'payment_id' => $payment->id,
                'status' => 'processed',
            ])->save();

            return response()->json([
                'ok' => true,
            ]);
        } catch (Throwable $exception) {
            $event->forceFill([
                'payment_id' => $payment->id,
                'status' => 'failed',
                'metadata' => [
                    'provider' => $provider,
                    'error' => $exception->getMessage(),
                ],
            ])->save();

            throw $exception;
        }
    }
}
