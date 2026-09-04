<?php

namespace App\Payments\Contracts;

use App\Models\Payment;
use App\Models\Refund;
use App\Payments\Data\GatewayPaymentResult;
use App\Payments\Data\GatewayRefundResult;
use App\Payments\Data\GatewayWebhookData;
use Illuminate\Http\Request;

interface PaymentGateway
{
    public function createIntent(
        Payment $payment
    ): GatewayPaymentResult;

    public function retrieve(
        Payment $payment
    ): GatewayPaymentResult;

    public function refund(
        Payment $payment,
        Refund $refund
    ): GatewayRefundResult;

    public function verifyWebhook(
        Request $request
    ): bool;

    public function parseWebhook(
        Request $request
    ): GatewayWebhookData;
}
