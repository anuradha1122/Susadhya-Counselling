<?php

namespace App\Services\Notifications;

use App\Notifications\Contracts\SmsGateway;
use App\Notifications\Data\SmsGatewayResult;

class NullSmsGateway implements SmsGateway
{
    public function send(
        string $recipient,
        string $message
    ): SmsGatewayResult {
        return SmsGatewayResult::unavailable(
            'No SMS provider has been configured.'
        );
    }
}
