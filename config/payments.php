<?php

return [
    'currency' => env(
        'PAYMENT_CURRENCY',
        'LKR'
    ),

    /*
    |--------------------------------------------------------------------------
    | Gateway driver
    |--------------------------------------------------------------------------
    |
    | "sandbox" is provided by M14 for development and automated testing.
    | Do not use the sandbox driver in production.
    |
    | Manual payments do not use this driver. They are recorded separately
    | by authorised Finance Admin users.
    |
    */
    'gateway' => env(
        'PAYMENT_GATEWAY_DRIVER',
        'sandbox'
    ),

    'sandbox' => [
        'webhook_secret' => env(
            'PAYMENT_SANDBOX_WEBHOOK_SECRET',
            'susadhya-local-sandbox-secret'
        ),
    ],
];
