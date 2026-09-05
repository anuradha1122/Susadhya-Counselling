<?php

use App\Console\Commands\PurgePrivacyExports;
use App\Console\Commands\RunRetentionPolicies;
use App\Http\Middleware\AuditSensitiveAction;
use App\Http\Middleware\EnsureAccountIsActive;
use App\Http\Middleware\EnsureUserHasPermission;
use App\Http\Middleware\EnsureUserHasRole;
use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(
    basePath: dirname(__DIR__)
)
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withCommands([
        RunRetentionPolicies::class,
        PurgePrivacyExports::class,
    ])
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            HandleInertiaRequests::class,
        ]);

        $middleware->alias([
            'active' => EnsureAccountIsActive::class,
            'role' => EnsureUserHasRole::class,
            'permission' => EnsureUserHasPermission::class,
            'audit.sensitive' => AuditSensitiveAction::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->create();
