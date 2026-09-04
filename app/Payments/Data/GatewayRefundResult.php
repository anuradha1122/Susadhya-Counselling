<?php

namespace App\Payments\Data;

final readonly class GatewayRefundResult
{
    public function __construct(
        public string $status,
        public ?string $providerRefundId,
        public ?string $providerReference,
        public string $amount,
        public string $currency,
        public array $metadata = [],
    ) {}
}
