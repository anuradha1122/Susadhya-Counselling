<?php

namespace App\Notifications\Contracts;

use App\Notifications\Data\SmsGatewayResult;

interface SmsGateway
{
    public function send(
        string $recipient,
        string $message
    ): SmsGatewayResult;
}
