<?php

namespace App\Providers;

use App\Models\Appointment;
use App\Models\Payment;
use App\Models\Refund;
use App\Notifications\Contracts\SmsGateway;
use App\Observers\AppointmentNotificationObserver;
use App\Observers\PaymentNotificationObserver;
use App\Observers\RefundNotificationObserver;
use App\Services\Notifications\NullSmsGateway;
use Illuminate\Support\ServiceProvider;

class NotificationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(
            SmsGateway::class,
            fn () => new NullSmsGateway
        );
    }

    public function boot(): void
    {
        Appointment::observe(
            AppointmentNotificationObserver::class
        );

        Payment::observe(
            PaymentNotificationObserver::class
        );

        Refund::observe(
            RefundNotificationObserver::class
        );
    }
}
