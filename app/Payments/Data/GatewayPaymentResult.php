<?php

namespace App\Payments\Data;

final readonly class GatewayPaymentResult
{
    public function __construct(
        public string $status,
        public ?string $providerPaymentId,
        public ?string $providerReference,
        public string $amount,
        public string $currency,
        public ?string $redirectUrl = null,
        public array $metadata = [],
    ) {}
}
