<?php

use App\Http\Controllers\Client\PrivacyRequestController as ClientPrivacyRequestController;
use App\Http\Controllers\Compliance\AuditEventController;
use App\Http\Controllers\Compliance\ComplianceDashboardController;
use App\Http\Controllers\Compliance\ConsentHistoryController;
use App\Http\Controllers\Compliance\DataBreachController;
use App\Http\Controllers\Compliance\PrivacyRequestController;
use App\Http\Controllers\Compliance\RetentionPolicyController;
use Illuminate\Support\Facades\Route;

Route::middleware([
    'auth',
    'active',
])
    ->prefix('compliance')
    ->name('compliance.')
    ->group(function (): void {
        Route::get(
            '/dashboard',
            ComplianceDashboardController::class
        )
            ->middleware(
                'can:compliance.audit.view'
            )
            ->name('dashboard');

        Route::get(
            '/audit-events',
            [
                AuditEventController::class,
                'index',
            ]
        )
            ->middleware([
                'can:compliance.audit.view',
                'audit.sensitive:audit,audit_events.index,read',
            ])
            ->name(
                'audit-events.index'
            );

        Route::get(
            '/privacy-requests',
            [
                PrivacyRequestController::class,
                'index',
            ]
        )
            ->middleware(
                'can:compliance.privacy.view'
            )
            ->name(
                'privacy-requests.index'
            );

        Route::get(
            '/privacy-requests/{privacyRequest}',
            [
                PrivacyRequestController::class,
                'show',
            ]
        )
            ->middleware([
                'can:compliance.privacy.view',
                'audit.sensitive:privacy,privacy_request.view,read',
            ])
            ->name(
                'privacy-requests.show'
            );

        Route::post(
            '/privacy-requests/{privacyRequest}/verify-identity',
            [
                PrivacyRequestController::class,
                'verifyIdentity',
            ]
        )
            ->middleware(
                'can:compliance.privacy.manage'
            )
            ->name(
                'privacy-requests.verify-identity'
            );

        Route::post(
            '/privacy-requests/{privacyRequest}/begin-review',
            [
                PrivacyRequestController::class,
                'beginReview',
            ]
        )
            ->middleware(
                'can:compliance.privacy.manage'
            )
            ->name(
                'privacy-requests.begin-review'
            );

        Route::patch(
            '/privacy-requests/{privacyRequest}/decision',
            [
                PrivacyRequestController::class,
                'decide',
            ]
        )
            ->middleware(
                'can:compliance.privacy.manage'
            )
            ->name(
                'privacy-requests.decision'
            );

        Route::post(
            '/privacy-requests/{privacyRequest}/prepare-export',
            [
                PrivacyRequestController::class,
                'prepareExport',
            ]
        )
            ->middleware(
                'can:compliance.privacy.manage'
            )
            ->name(
                'privacy-requests.prepare-export'
            );

        Route::get(
            '/privacy-requests/{privacyRequest}/download-export',
            [
                PrivacyRequestController::class,
                'downloadExport',
            ]
        )
            ->middleware(
                'can:compliance.privacy.view'
            )
            ->name(
                'privacy-requests.download-export'
            );

        Route::patch(
            '/privacy-requests/{privacyRequest}/complete',
            [
                PrivacyRequestController::class,
                'complete',
            ]
        )
            ->middleware(
                'can:compliance.privacy.manage'
            )
            ->name(
                'privacy-requests.complete'
            );

        Route::get(
            '/retention',
            [
                RetentionPolicyController::class,
                'index',
            ]
        )
            ->middleware(
                'can:compliance.retention.view'
            )
            ->name('retention.index');

        Route::patch(
            '/retention/{retentionPolicy}',
            [
                RetentionPolicyController::class,
                'update',
            ]
        )
            ->middleware(
                'can:compliance.retention.manage'
            )
            ->name('retention.update');

        Route::post(
            '/retention/{retentionPolicy}/dry-run',
            [
                RetentionPolicyController::class,
                'dryRun',
            ]
        )
            ->middleware(
                'can:compliance.retention.manage'
            )
            ->name('retention.dry-run');

        Route::post(
            '/retention/{retentionPolicy}/execute',
            [
                RetentionPolicyController::class,
                'execute',
            ]
        )
            ->middleware(
                'can:compliance.retention.manage'
            )
            ->name('retention.execute');

        Route::get(
            '/consents',
            [
                ConsentHistoryController::class,
                'index',
            ]
        )
            ->middleware([
                'can:compliance.consents.view',
                'audit.sensitive:consent,consent_history.view,read',
            ])
            ->name('consents.index');

        Route::get(
            '/breaches',
            [
                DataBreachController::class,
                'index',
            ]
        )
            ->middleware(
                'can:compliance.breaches.view'
            )
            ->name('breaches.index');

        Route::post(
            '/breaches',
            [
                DataBreachController::class,
                'store',
            ]
        )
            ->middleware(
                'can:compliance.breaches.manage'
            )
            ->name('breaches.store');

        Route::get(
            '/breaches/{breach}',
            [
                DataBreachController::class,
                'show',
            ]
        )
            ->middleware([
                'can:compliance.breaches.view',
                'audit.sensitive:breach,breach.view,read',
            ])
            ->name('breaches.show');

        Route::patch(
            '/breaches/{breach}',
            [
                DataBreachController::class,
                'update',
            ]
        )
            ->middleware(
                'can:compliance.breaches.manage'
            )
            ->name('breaches.update');
    });

Route::middleware([
    'auth',
    'active',
    'can:privacy.requests.submit',
])
    ->prefix(
        'client/privacy-requests'
    )
    ->name(
        'client.privacy-requests.'
    )
    ->group(function (): void {
        Route::get(
            '/',
            [
                ClientPrivacyRequestController::class,
                'index',
            ]
        )->name('index');

        Route::post(
            '/',
            [
                ClientPrivacyRequestController::class,
                'store',
            ]
        )->name('store');

        Route::patch(
            '/{privacyRequest}/cancel',
            [
                ClientPrivacyRequestController::class,
                'cancel',
            ]
        )->name('cancel');

        Route::get(
            '/{privacyRequest}/download',
            [
                ClientPrivacyRequestController::class,
                'downloadExport',
            ]
        )->name('download');
    });
