<?php

namespace App\Notifications\Data;

readonly class SmsGatewayResult
{
    public function __construct(
        public bool $successful,
        public string $status,
        public ?string $providerMessageId = null,
        public ?string $error = null,
    ) {}

    public static function sent(?string $providerMessageId = null): self
    {
        return new self(
            successful: true,
            status: 'sent',
            providerMessageId: $providerMessageId,
        );
    }

    public static function failed(string $error): self
    {
        return new self(
            successful: false,
            status: 'failed',
            error: $error,
        );
    }

    public static function unavailable(string $error): self
    {
        return new self(
            successful: false,
            status: 'unavailable',
            error: $error,
        );
    }
}
