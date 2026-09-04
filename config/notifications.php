<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Email notifications
    |--------------------------------------------------------------------------
    */

    'email' => [
        'enabled' => env(
            'NOTIFICATIONS_EMAIL_ENABLED',
            true
        ),
    ],

    /*
    |--------------------------------------------------------------------------
    | SMS notifications
    |--------------------------------------------------------------------------
    |
    | SMS is intentionally provider-neutral for M15. A real implementation
    | can replace the SmsGateway binding without changing notification logic.
    |
    */

    'sms' => [
        'enabled' => env(
            'NOTIFICATIONS_SMS_ENABLED',
            false
        ),

        'driver' => env(
            'NOTIFICATIONS_SMS_DRIVER',
            'null'
        ),
    ],

    /*
    |--------------------------------------------------------------------------
    | Appointment reminders
    |--------------------------------------------------------------------------
    */

    'reminders' => [
        'appointment_minutes_before' => (int) env(
            'APPOINTMENT_REMINDER_MINUTES',
            1440
        ),

        'window_minutes' => (int) env(
            'APPOINTMENT_REMINDER_WINDOW_MINUTES',
            15
        ),
    ],

];
