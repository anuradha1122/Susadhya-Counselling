<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('appointments:send-reminders')->everyFiveMinutes();

Schedule::command(
    'payments:reconcile --limit=100'
)
    ->everyFifteenMinutes()
    ->withoutOverlapping();

Schedule::command(
    'notifications:send-appointment-reminders'
)
    ->everyFifteenMinutes()
    ->withoutOverlapping();
