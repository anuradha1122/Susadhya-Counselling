<?php

namespace App\Payments\Gateways;

use App\Models\Payment;
use App\Models\Refund;
use App\Payments\Contracts\PaymentGateway;
use App\Payments\Data\GatewayPaymentResult;
use App\Payments\Data\GatewayRefundResult;
use App\Payments\Data\GatewayWebhookData;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use RuntimeException;

class SandboxPaymentGateway implements PaymentGateway
{
    public function createIntent(
        Payment $payment
    ): GatewayPaymentResult {
        $this->guardProduction();

        return new GatewayPaymentResult(
            status: Payment::STATUS_PENDING,
            providerPaymentId: 'sbx_'.Str::lower(Str::random(28)),
            providerReference: 'SBX-'.Str::upper(Str::random(12)),
            amount: $payment->amount,
            currency: $payment->currency,
            redirectUrl: route(
                'client.payments.checkout',
                $payment
            ),
            metadata: [
                'sandbox_remote_status' => Payment::STATUS_PENDING,
                'sandbox_remote_amount' => $payment->amount,
                'sandbox_remote_currency' => $payment->currency,
            ],
        );
    }

    public function retrieve(
        Payment $payment
    ): GatewayPaymentResult {
        $this->guardProduction();

        $metadata = $payment->metadata ?? [];

        return new GatewayPaymentResult(
            status: $metadata[
                    'sandbox_remote_status'
                ]
                    ?? $payment->provider_status
                    ?? Payment::STATUS_PENDING,
            providerPaymentId: $payment->provider_payment_id,
            providerReference: $payment->provider_reference,
            amount: (string) (
                $metadata[
                    'sandbox_remote_amount'
                ]
                    ?? $payment->amount
            ),
            currency: (string) (
                $metadata[
                    'sandbox_remote_currency'
                ]
                    ?? $payment->currency
            ),
            metadata: $metadata,
        );
    }

    public function refund(
        Payment $payment,
        Refund $refund
    ): GatewayRefundResult {
        $this->guardProduction();

        return new GatewayRefundResult(
            status: Refund::STATUS_REFUNDED,
            providerRefundId: 'sbxr_'.Str::lower(
                Str::random(28)
            ),
            providerReference: 'SBXR-'.Str::upper(
                Str::random(12)
            ),
            amount: $refund->approved_amount
                    ?? $refund->requested_amount,
            currency: $refund->currency,
            metadata: [
                'sandbox' => true,
            ],
        );
    }

    public function verifyWebhook(
        Request $request
    ): bool {
        $this->guardProduction();

        $secret = (string) config(
            'payments.sandbox.webhook_secret'
        );

        if ($secret === '') {
            return false;
        }

        $provided = (string) $request->header(
            'X-Susadhya-Payment-Signature'
        );

        $expected = hash_hmac(
            'sha256',
            $request->getContent(),
            $secret
        );

        return hash_equals(
            $expected,
            $provided
        );
    }

    public function parseWebhook(
        Request $request
    ): GatewayWebhookData {
        $validated = $request->validate([
            'event_id' => [
                'required',
                'string',
                'max:150',
            ],
            'payment_uuid' => [
                'required',
                'uuid',
            ],
            'provider_payment_id' => [
                'required',
                'string',
                'max:255',
            ],
            'status' => [
                'required',
                'string',
                'in:pending,processing,paid,failed',
            ],
            'amount' => [
                'required',
                'numeric',
                'min:0',
            ],
            'currency' => [
                'required',
                'string',
                'size:3',
            ],
        ]);

        return new GatewayWebhookData(
            eventId: $validated['event_id'],
            paymentUuid: $validated['payment_uuid'],
            providerPaymentId: $validated['provider_payment_id'],
            status: $validated['status'],
            amount: number_format(
                (float) $validated['amount'],
                2,
                '.',
                ''
            ),
            currency: strtoupper(
                $validated['currency']
            ),
            metadata: [],
        );
    }

    private function guardProduction(): void
    {
        if (app()->environment('production')) {
            throw new RuntimeException(
                'The sandbox payment gateway cannot be used in production.'
            );
        }
    }
}
