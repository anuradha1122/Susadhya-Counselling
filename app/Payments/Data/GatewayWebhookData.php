<?php

namespace App\Payments\Data;

final readonly class GatewayWebhookData
{
    public function __construct(
        public string $eventId,
        public string $paymentUuid,
        public string $providerPaymentId,
        public string $status,
        public string $amount,
        public string $currency,
        public array $metadata = [],
    ) {}
}
