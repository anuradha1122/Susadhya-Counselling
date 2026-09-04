<?php

namespace App\Payments;

use App\Payments\Contracts\PaymentGateway;
use App\Payments\Gateways\SandboxPaymentGateway;
use InvalidArgumentException;

class PaymentGatewayManager
{
    public function default(): PaymentGateway
    {
        return $this->driver(
            (string) config(
                'payments.gateway'
            )
        );
    }

    public function driver(
        string $driver
    ): PaymentGateway {
        return match ($driver) {
            'sandbox' => app(
                SandboxPaymentGateway::class
            ),

            default => throw new InvalidArgumentException(
                "Unsupported payment gateway [{$driver}]."
            ),
        };
    }
}
