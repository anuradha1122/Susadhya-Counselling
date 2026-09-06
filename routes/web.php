<?php

use App\Http\Controllers\Admin\AppointmentController as AdminAppointmentController;
use App\Http\Controllers\Admin\AvailabilityController as AdminAvailabilityController;
use App\Http\Controllers\Admin\ClientController as AdminClientController;
use App\Http\Controllers\Admin\CounsellingServiceController;
use App\Http\Controllers\Admin\CounsellorController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\DocumentController as AdminDocumentController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\ServiceCategoryController;
use App\Http\Controllers\Admin\SessionController as AdminSessionController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Client\AppointmentController as ClientAppointmentController;
use App\Http\Controllers\Client\AppointmentSlotController as ClientAppointmentSlotController;
use App\Http\Controllers\Client\CounsellorDiscoveryController;
use App\Http\Controllers\Client\DashboardController as ClientDashboardController;
use App\Http\Controllers\Client\DocumentController as ClientDocumentController;
use App\Http\Controllers\Client\EmergencyContactController as ClientEmergencyContactController;
use App\Http\Controllers\Client\IntakeController as ClientIntakeController;
use App\Http\Controllers\Client\PaymentController as ClientPaymentController;
use App\Http\Controllers\Client\PreferenceController as ClientPreferenceController;
use App\Http\Controllers\Client\PrivacySettingsController as ClientPrivacySettingsController;
use App\Http\Controllers\Client\ProfileController as ClientProfileController;
use App\Http\Controllers\Client\RefundController as ClientRefundController;
use App\Http\Controllers\Client\SessionController as ClientSessionController;
use App\Http\Controllers\ClinicalSupervisor\CaseController as ClinicalSupervisorCaseController;
use App\Http\Controllers\ClinicalSupervisor\DocumentController as ClinicalSupervisorDocumentController;
use App\Http\Controllers\Counsellor\AppointmentController as CounsellorAppointmentController;
use App\Http\Controllers\Counsellor\AvailabilityBreakController as CounsellorAvailabilityBreakController;
use App\Http\Controllers\Counsellor\AvailabilityController as CounsellorAvailabilityController;
use App\Http\Controllers\Counsellor\BlockedSlotController as CounsellorBlockedSlotController;
use App\Http\Controllers\Counsellor\CaseController as CounsellorCaseController;
use App\Http\Controllers\Counsellor\DashboardController as CounsellorDashboardController;
use App\Http\Controllers\Counsellor\DocumentController as CounsellorDocumentController;
use App\Http\Controllers\Counsellor\LeaveDayController as CounsellorLeaveDayController;
use App\Http\Controllers\Counsellor\SessionController as CounsellorSessionController;
use App\Http\Controllers\DashboardRedirectController;
use App\Http\Controllers\Finance\PaymentController as FinancePaymentController;
use App\Http\Controllers\Finance\RefundController as FinanceRefundController;
use App\Http\Controllers\IntakeReviewController;
use App\Http\Controllers\PaymentWebhookController;
use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return inertia('Welcome');
})->name('home');

/*
|--------------------------------------------------------------------------
| Payment Provider Webhooks
|--------------------------------------------------------------------------
|
| Provider callbacks cannot use normal browser CSRF protection because
| requests originate from the external payment provider.
|
| Each provider adapter is still responsible for validating the provider
| signature before any payment state is changed.
|
*/

Route::post(
    '/webhooks/payments/{provider}',
    PaymentWebhookController::class
)
    ->withoutMiddleware([
        ValidateCsrfToken::class,
    ])
    ->middleware('throttle:120,1')
    ->name('payments.webhook');

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/

Route::middleware([
    'auth',
    'active',
])->group(function (): void {
    /*
    |--------------------------------------------------------------------------
    | Dashboard Redirect
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/dashboard',
        DashboardRedirectController::class
    )->name('dashboard');

    /*
    |--------------------------------------------------------------------------
    | Admin Routes
    |--------------------------------------------------------------------------
    */

    Route::prefix('admin')
        ->name('admin.')
        ->middleware('permission:dashboard.admin.view')
        ->group(function (): void {
            Route::get(
                '/dashboard',
                AdminDashboardController::class
            )->name('dashboard');

            /*
            |--------------------------------------------------------------------------
            | Users
            |--------------------------------------------------------------------------
            */

            Route::resource(
                'users',
                UserController::class
            )->except('show');

            Route::patch(
                '/users/{user}/status',
                [
                    UserController::class,
                    'updateStatus',
                ]
            )->name('users.status');

            /*
            |--------------------------------------------------------------------------
            | Roles
            |--------------------------------------------------------------------------
            */

            Route::resource(
                'roles',
                RoleController::class
            )->except('show');

            /*
            |--------------------------------------------------------------------------
            | Clients
            |--------------------------------------------------------------------------
            */

            Route::resource(
                'clients',
                AdminClientController::class
            )->only([
                'index',
                'show',
                'destroy',
            ]);

            Route::patch(
                '/clients/{client}/status',
                [
                    AdminClientController::class,
                    'updateStatus',
                ]
            )->name('clients.status');

            Route::patch(
                '/clients/{client}/restore',
                [
                    AdminClientController::class,
                    'restore',
                ]
            )->name('clients.restore');

            /*
            |--------------------------------------------------------------------------
            | Counsellors
            |--------------------------------------------------------------------------
            */

            Route::resource(
                'counsellors',
                CounsellorController::class
            );

            Route::patch(
                'counsellors/{counsellor}/restore',
                [
                    CounsellorController::class,
                    'restore',
                ]
            )->name('counsellors.restore');

            /*
            |--------------------------------------------------------------------------
            | Service Categories
            |--------------------------------------------------------------------------
            */

            Route::resource(
                'service-categories',
                ServiceCategoryController::class
            );

            Route::patch(
                'service-categories/{service_category}/restore',
                [
                    ServiceCategoryController::class,
                    'restore',
                ]
            )->name(
                'service-categories.restore'
            );

            /*
            |--------------------------------------------------------------------------
            | Counselling Services
            |--------------------------------------------------------------------------
            */

            Route::resource(
                'counselling-services',
                CounsellingServiceController::class
            );

            Route::patch(
                'counselling-services/{counselling_service}/restore',
                [
                    CounsellingServiceController::class,
                    'restore',
                ]
            )->name(
                'counselling-services.restore'
            );

            /*
            |--------------------------------------------------------------------------
            | Availability Oversight
            |--------------------------------------------------------------------------
            */

            Route::get(
                '/availability',
                [
                    AdminAvailabilityController::class,
                    'index',
                ]
            )->name('availability.index');

            /*
            |--------------------------------------------------------------------------
            | Appointment Oversight
            |--------------------------------------------------------------------------
            */

            Route::get(
                '/appointments',
                [
                    AdminAppointmentController::class,
                    'index',
                ]
            )->name('appointments.index');

            Route::patch(
                '/appointments/{appointment}/status',
                [
                    AdminAppointmentController::class,
                    'updateStatus',
                ]
            )->name(
                'appointments.update-status'
            );

            /*
            |--------------------------------------------------------------------------
            | Intake Oversight
            |--------------------------------------------------------------------------
            */

            Route::get(
                '/intakes',
                [
                    IntakeReviewController::class,
                    'adminIndex',
                ]
            )->name('intakes.index');

            Route::patch(
                '/intakes/{intake}/review',
                [
                    IntakeReviewController::class,
                    'adminReview',
                ]
            )->name('intakes.review');

            /*
            |--------------------------------------------------------------------------
            | Session Oversight
            |--------------------------------------------------------------------------
            */

            Route::get(
                '/sessions',
                [
                    AdminSessionController::class,
                    'index',
                ]
            )->name('sessions.index');

            Route::patch(
                '/sessions/{session}/review',
                [
                    AdminSessionController::class,
                    'updateReview',
                ]
            )->name('sessions.review');

            /*
            |--------------------------------------------------------------------------
            | Administrative Documents
            |--------------------------------------------------------------------------
            */

            Route::middleware(
                'permission:documents.admin.manage'
            )->group(function (): void {
                Route::get(
                    '/documents',
                    [
                        AdminDocumentController::class,
                        'index',
                    ]
                )->name(
                    'documents.index'
                );

                Route::post(
                    '/documents',
                    [
                        AdminDocumentController::class,
                        'store',
                    ]
                )->name(
                    'documents.store'
                );

                Route::get(
                    '/documents/{document}/download',
                    [
                        AdminDocumentController::class,
                        'download',
                    ]
                )->name(
                    'documents.download'
                );

                Route::delete(
                    '/documents/{document}',
                    [
                        AdminDocumentController::class,
                        'destroy',
                    ]
                )->name(
                    'documents.destroy'
                );
            });
        });

    /*
    |--------------------------------------------------------------------------
    | Counsellor Routes
    |--------------------------------------------------------------------------
    */

    Route::prefix('counsellor')
        ->name('counsellor.')
        ->middleware('role:counsellor')
        ->group(function (): void {
            Route::get(
                '/dashboard',
                CounsellorDashboardController::class
            )->name('dashboard');

            /*
            |--------------------------------------------------------------------------
            | Availability
            |--------------------------------------------------------------------------
            */

            Route::get(
                '/availability',
                [
                    CounsellorAvailabilityController::class,
                    'index',
                ]
            )->name('availability.index');

            Route::post(
                '/availability/rules',
                [
                    CounsellorAvailabilityController::class,
                    'store',
                ]
            )->name(
                'availability.rules.store'
            );

            Route::patch(
                '/availability/rules/{availabilityRule}',
                [
                    CounsellorAvailabilityController::class,
                    'update',
                ]
            )->name(
                'availability.rules.update'
            );

            Route::delete(
                '/availability/rules/{availabilityRule}',
                [
                    CounsellorAvailabilityController::class,
                    'destroy',
                ]
            )->name(
                'availability.rules.destroy'
            );

            Route::post(
                '/availability/rules/{availabilityRule}/breaks',
                [
                    CounsellorAvailabilityBreakController::class,
                    'store',
                ]
            )->name(
                'availability.breaks.store'
            );

            Route::patch(
                '/availability/breaks/{availabilityBreak}',
                [
                    CounsellorAvailabilityBreakController::class,
                    'update',
                ]
            )->name(
                'availability.breaks.update'
            );

            Route::delete(
                '/availability/breaks/{availabilityBreak}',
                [
                    CounsellorAvailabilityBreakController::class,
                    'destroy',
                ]
            )->name(
                'availability.breaks.destroy'
            );

            Route::post(
                '/availability/blocked-slots',
                [
                    CounsellorBlockedSlotController::class,
                    'store',
                ]
            )->name(
                'availability.blocked-slots.store'
            );

            Route::patch(
                '/availability/blocked-slots/{blockedSlot}',
                [
                    CounsellorBlockedSlotController::class,
                    'update',
                ]
            )->name(
                'availability.blocked-slots.update'
            );

            Route::delete(
                '/availability/blocked-slots/{blockedSlot}',
                [
                    CounsellorBlockedSlotController::class,
                    'destroy',
                ]
            )->name(
                'availability.blocked-slots.destroy'
            );

            Route::post(
                '/availability/leave-days',
                [
                    CounsellorLeaveDayController::class,
                    'store',
                ]
            )->name(
                'availability.leave-days.store'
            );

            Route::patch(
                '/availability/leave-days/{leaveDay}',
                [
                    CounsellorLeaveDayController::class,
                    'update',
                ]
            )->name(
                'availability.leave-days.update'
            );

            Route::delete(
                '/availability/leave-days/{leaveDay}',
                [
                    CounsellorLeaveDayController::class,
                    'destroy',
                ]
            )->name(
                'availability.leave-days.destroy'
            );

            /*
            |--------------------------------------------------------------------------
            | Appointments
            |--------------------------------------------------------------------------
            */

            Route::get(
                '/appointments',
                [
                    CounsellorAppointmentController::class,
                    'index',
                ]
            )->name('appointments.index');

            Route::patch(
                '/appointments/{appointment}/confirm',
                [
                    CounsellorAppointmentController::class,
                    'confirm',
                ]
            )->name(
                'appointments.confirm'
            );

            Route::patch(
                '/appointments/{appointment}/complete',
                [
                    CounsellorAppointmentController::class,
                    'complete',
                ]
            )->name(
                'appointments.complete'
            );

            Route::patch(
                '/appointments/{appointment}/no-show',
                [
                    CounsellorAppointmentController::class,
                    'noShow',
                ]
            )->name(
                'appointments.no-show'
            );

            /*
            |--------------------------------------------------------------------------
            | Intake Review
            |--------------------------------------------------------------------------
            */

            Route::get(
                '/intakes',
                [
                    IntakeReviewController::class,
                    'counsellorIndex',
                ]
            )->name('intakes.index');

            Route::patch(
                '/intakes/{intake}/review',
                [
                    IntakeReviewController::class,
                    'counsellorReview',
                ]
            )->name('intakes.review');

            /*
            |--------------------------------------------------------------------------
            | Session Delivery
            |--------------------------------------------------------------------------
            */

            Route::get(
                '/sessions',
                [
                    CounsellorSessionController::class,
                    'index',
                ]
            )->name('sessions.index');

            Route::post(
                '/sessions/appointments/{appointment}/start',
                [
                    CounsellorSessionController::class,
                    'start',
                ]
            )->name(
                'sessions.start'
            );

            Route::post(
                '/sessions/{session}/notes',
                [
                    CounsellorSessionController::class,
                    'storeNote',
                ]
            )->name(
                'sessions.notes.store'
            );

            Route::patch(
                '/sessions/{session}/complete',
                [
                    CounsellorSessionController::class,
                    'complete',
                ]
            )->name(
                'sessions.complete'
            );

            /*
            |--------------------------------------------------------------------------
            | Clinical Cases
            |--------------------------------------------------------------------------
            */

            Route::middleware(
                'permission:clinical.records.manage'
            )->group(function (): void {
                Route::get(
                    '/cases',
                    [
                        CounsellorCaseController::class,
                        'index',
                    ]
                )->name('cases.index');

                Route::post(
                    '/cases',
                    [
                        CounsellorCaseController::class,
                        'store',
                    ]
                )->name('cases.store');

                Route::get(
                    '/cases/{case}',
                    [
                        CounsellorCaseController::class,
                        'show',
                    ]
                )->name('cases.show');

                Route::patch(
                    '/cases/{case}',
                    [
                        CounsellorCaseController::class,
                        'update',
                    ]
                )->name('cases.update');

                Route::patch(
                    '/cases/{case}/close',
                    [
                        CounsellorCaseController::class,
                        'close',
                    ]
                )->name('cases.close');

                Route::post(
                    '/cases/{case}/goals',
                    [
                        CounsellorCaseController::class,
                        'storeGoal',
                    ]
                )->name(
                    'cases.goals.store'
                );

                Route::patch(
                    '/cases/{case}/goals/{goal}',
                    [
                        CounsellorCaseController::class,
                        'updateGoal',
                    ]
                )->name(
                    'cases.goals.update'
                );

                Route::post(
                    '/cases/{case}/follow-ups',
                    [
                        CounsellorCaseController::class,
                        'storeFollowUp',
                    ]
                )->name(
                    'cases.follow-ups.store'
                );

                Route::patch(
                    '/cases/{case}/follow-ups/{followUp}',
                    [
                        CounsellorCaseController::class,
                        'updateFollowUp',
                    ]
                )->name(
                    'cases.follow-ups.update'
                );

                Route::post(
                    '/cases/{case}/notes',
                    [
                        CounsellorCaseController::class,
                        'storeNote',
                    ]
                )->name(
                    'cases.notes.store'
                );

                Route::patch(
                    '/cases/{case}/notes/{note}',
                    [
                        CounsellorCaseController::class,
                        'updateNote',
                    ]
                )->name(
                    'cases.notes.update'
                );

                Route::patch(
                    '/cases/{case}/notes/{note}/sign',
                    [
                        CounsellorCaseController::class,
                        'signNote',
                    ]
                )->name(
                    'cases.notes.sign'
                );

                Route::get(
                    '/cases/{case}/notes/{note}/versions',
                    [
                        CounsellorCaseController::class,
                        'noteVersions',
                    ]
                )->name(
                    'cases.notes.versions'
                );
            });

            /*
            |--------------------------------------------------------------------------
            | Counsellor Documents
            |--------------------------------------------------------------------------
            */

            Route::middleware(
                'permission:documents.case.manage'
            )->group(function (): void {
                Route::get(
                    '/documents',
                    [
                        CounsellorDocumentController::class,
                        'index',
                    ]
                )->name(
                    'documents.index'
                );

                Route::post(
                    '/cases/{case}/documents',
                    [
                        CounsellorDocumentController::class,
                        'store',
                    ]
                )->name(
                    'documents.store'
                );

                Route::get(
                    '/documents/{document}/download',
                    [
                        CounsellorDocumentController::class,
                        'download',
                    ]
                )->name(
                    'documents.download'
                );

                Route::delete(
                    '/documents/{document}',
                    [
                        CounsellorDocumentController::class,
                        'destroy',
                    ]
                )->name(
                    'documents.destroy'
                );
            });
        });

    /*
    |--------------------------------------------------------------------------
    | Client Routes
    |--------------------------------------------------------------------------
    */

    Route::prefix('client')
        ->name('client.')
        ->middleware('role:client')
        ->group(function (): void {
            Route::get(
                '/dashboard',
                ClientDashboardController::class
            )->name('dashboard');

            /*
            |--------------------------------------------------------------------------
            | Client Profile
            |--------------------------------------------------------------------------
            */

            Route::get(
                '/profile',
                [
                    ClientProfileController::class,
                    'show',
                ]
            )->name('profile.show');

            Route::get(
                '/profile/edit',
                [
                    ClientProfileController::class,
                    'edit',
                ]
            )->name('profile.edit');

            Route::patch(
                '/profile',
                [
                    ClientProfileController::class,
                    'update',
                ]
            )->name('profile.update');

            /*
            |--------------------------------------------------------------------------
            | Emergency Contacts
            |--------------------------------------------------------------------------
            */

            Route::resource(
                'emergency-contacts',
                ClientEmergencyContactController::class
            )->except([
                'show',
            ]);

            /*
            |--------------------------------------------------------------------------
            | Preferences
            |--------------------------------------------------------------------------
            */

            Route::get(
                '/preferences',
                [
                    ClientPreferenceController::class,
                    'show',
                ]
            )->name(
                'preferences.show'
            );

            Route::get(
                '/preferences/edit',
                [
                    ClientPreferenceController::class,
                    'edit',
                ]
            )->name(
                'preferences.edit'
            );

            Route::patch(
                '/preferences',
                [
                    ClientPreferenceController::class,
                    'update',
                ]
            )->name(
                'preferences.update'
            );

            /*
            |--------------------------------------------------------------------------
            | Privacy
            |--------------------------------------------------------------------------
            */

            Route::get(
                '/privacy',
                [
                    ClientPrivacySettingsController::class,
                    'show',
                ]
            )->name('privacy.show');

            Route::get(
                '/privacy/edit',
                [
                    ClientPrivacySettingsController::class,
                    'edit',
                ]
            )->name('privacy.edit');

            Route::patch(
                '/privacy',
                [
                    ClientPrivacySettingsController::class,
                    'update',
                ]
            )->name('privacy.update');

            /*
            |--------------------------------------------------------------------------
            | Counsellor Discovery
            |--------------------------------------------------------------------------
            */

            Route::get(
                '/counsellors',
                [
                    CounsellorDiscoveryController::class,
                    'index',
                ]
            )->name(
                'counsellors.index'
            );

            Route::get(
                '/counsellors/{counsellor}',
                [
                    CounsellorDiscoveryController::class,
                    'show',
                ]
            )->name(
                'counsellors.show'
            );

            Route::get(
                '/counsellors/{counsellor}/appointment-slots',
                [
                    ClientAppointmentSlotController::class,
                    'index',
                ]
            )->name(
                'counsellors.appointment-slots.index'
            );

            /*
            |--------------------------------------------------------------------------
            | Appointments
            |--------------------------------------------------------------------------
            */

            Route::get(
                '/appointments',
                [
                    ClientAppointmentController::class,
                    'index',
                ]
            )->name('appointments.index');

            Route::post(
                '/appointments',
                [
                    ClientAppointmentController::class,
                    'store',
                ]
            )->name('appointments.store');

            Route::get(
                '/appointments/{appointment}/reschedule-slots',
                [
                    ClientAppointmentController::class,
                    'rescheduleSlots',
                ]
            )->name(
                'appointments.reschedule-slots'
            );

            Route::patch(
                '/appointments/{appointment}/reschedule',
                [
                    ClientAppointmentController::class,
                    'reschedule',
                ]
            )->name(
                'appointments.reschedule'
            );

            Route::patch(
                '/appointments/{appointment}/cancel',
                [
                    ClientAppointmentController::class,
                    'cancel',
                ]
            )->name(
                'appointments.cancel'
            );

            /*
            |--------------------------------------------------------------------------
            | Intake
            |--------------------------------------------------------------------------
            */

            Route::get(
                '/intake',
                [
                    ClientIntakeController::class,
                    'edit',
                ]
            )->name('intake.edit');

            Route::patch(
                '/intake',
                [
                    ClientIntakeController::class,
                    'update',
                ]
            )->name('intake.update');

            Route::post(
                '/intake/submit',
                [
                    ClientIntakeController::class,
                    'submit',
                ]
            )->name('intake.submit');

            /*
            |--------------------------------------------------------------------------
            | Sessions
            |--------------------------------------------------------------------------
            */

            Route::get(
                '/sessions',
                [
                    ClientSessionController::class,
                    'index',
                ]
            )->name('sessions.index');

            /*
            |--------------------------------------------------------------------------
            | Client Documents
            |--------------------------------------------------------------------------
            */

            Route::middleware(
                'permission:documents.client.manage'
            )->group(function (): void {
                Route::get(
                    '/documents',
                    [
                        ClientDocumentController::class,
                        'index',
                    ]
                )->name(
                    'documents.index'
                );

                Route::post(
                    '/documents',
                    [
                        ClientDocumentController::class,
                        'store',
                    ]
                )->name(
                    'documents.store'
                );

                Route::get(
                    '/documents/{document}/download',
                    [
                        ClientDocumentController::class,
                        'download',
                    ]
                )->name(
                    'documents.download'
                );

                Route::delete(
                    '/documents/{document}',
                    [
                        ClientDocumentController::class,
                        'destroy',
                    ]
                )->name(
                    'documents.destroy'
                );
            });

            /*
            |--------------------------------------------------------------------------
            | M14 - Client Payments, Invoices & Refunds
            |--------------------------------------------------------------------------
            */

            Route::middleware(
                'permission:payments.client.manage'
            )->group(function (): void {
                Route::get(
                    '/payments',
                    [
                        ClientPaymentController::class,
                        'index',
                    ]
                )->name(
                    'payments.index'
                );

                Route::post(
                    '/appointments/{appointment}/payments',
                    [
                        ClientPaymentController::class,
                        'store',
                    ]
                )->name(
                    'payments.store'
                );

                Route::get(
                    '/payments/{payment}/checkout',
                    [
                        ClientPaymentController::class,
                        'checkout',
                    ]
                )->name(
                    'payments.checkout'
                );

                Route::post(
                    '/payments/{payment}/sandbox',
                    [
                        ClientPaymentController::class,
                        'completeSandbox',
                    ]
                )->name(
                    'payments.sandbox.complete'
                );

                Route::get(
                    '/payments/{payment}/invoice',
                    [
                        ClientPaymentController::class,
                        'invoice',
                    ]
                )->name(
                    'payments.invoice'
                );

                Route::get(
                    '/payments/{payment}/receipt',
                    [
                        ClientPaymentController::class,
                        'receipt',
                    ]
                )->name(
                    'payments.receipt'
                );

                Route::post(
                    '/payments/{payment}/refunds',
                    [
                        ClientRefundController::class,
                        'store',
                    ]
                )->name(
                    'payments.refunds.store'
                );
            });
        });

    /*
    |--------------------------------------------------------------------------
    | Clinical Supervisor Routes
    |--------------------------------------------------------------------------
    */

    Route::middleware([
        'verified',
        'permission:clinical.records.review',
    ])
        ->prefix(
            'clinical-supervisor'
        )
        ->name(
            'clinical-supervisor.'
        )
        ->group(function (): void {
            Route::get(
                '/cases',
                [
                    ClinicalSupervisorCaseController::class,
                    'index',
                ]
            )->name('cases.index');

            Route::get(
                '/cases/{case}',
                [
                    ClinicalSupervisorCaseController::class,
                    'show',
                ]
            )->name('cases.show');

            Route::get(
                '/cases/{case}/notes/{note}/versions',
                [
                    ClinicalSupervisorCaseController::class,
                    'noteVersions',
                ]
            )->name(
                'cases.notes.versions'
            );

            Route::middleware(
                'permission:documents.case.review'
            )->group(function (): void {
                Route::get(
                    '/documents',
                    [
                        ClinicalSupervisorDocumentController::class,
                        'index',
                    ]
                )->name(
                    'documents.index'
                );

                Route::get(
                    '/documents/{document}/download',
                    [
                        ClinicalSupervisorDocumentController::class,
                        'download',
                    ]
                )->name(
                    'documents.download'
                );
            });
        });

    /*
    |--------------------------------------------------------------------------
    | M14 - Finance Admin
    |--------------------------------------------------------------------------
    |
    | Finance Admin is deliberately separated from the normal admin route
    | group. Holding dashboard.admin.view must never grant access to financial
    | transactions, and holding finance permissions must never grant access to
    | confidential clinical records.
    |
    */

    Route::prefix('finance')
        ->name('finance.')
        ->middleware(
            'permission:payments.finance.view'
        )
        ->group(function (): void {
            /*
            |--------------------------------------------------------------------------
            | Payment Oversight
            |--------------------------------------------------------------------------
            */

            Route::get(
                '/payments',
                [
                    FinancePaymentController::class,
                    'index',
                ]
            )->name(
                'payments.index'
            );

            /*
            |--------------------------------------------------------------------------
            | Manual Payments
            |--------------------------------------------------------------------------
            */

            Route::post(
                '/payments/manual',
                [
                    FinancePaymentController::class,
                    'storeManual',
                ]
            )
                ->middleware(
                    'permission:payments.finance.manage'
                )
                ->name(
                    'payments.manual.store'
                );

            /*
            |--------------------------------------------------------------------------
            | Reconciliation
            |--------------------------------------------------------------------------
            */

            Route::patch(
                '/payments/{payment}/reconcile',
                [
                    FinancePaymentController::class,
                    'reconcile',
                ]
            )
                ->middleware(
                    'permission:payments.reconciliation.manage'
                )
                ->name(
                    'payments.reconcile'
                );

            /*
            |--------------------------------------------------------------------------
            | Financial Documents
            |--------------------------------------------------------------------------
            */

            Route::get(
                '/payments/{payment}/invoice',
                [
                    FinancePaymentController::class,
                    'invoice',
                ]
            )->name(
                'payments.invoice'
            );

            Route::get(
                '/payments/{payment}/receipt',
                [
                    FinancePaymentController::class,
                    'receipt',
                ]
            )->name(
                'payments.receipt'
            );

            /*
            |--------------------------------------------------------------------------
            | Refund Management
            |--------------------------------------------------------------------------
            */

            Route::middleware(
                'permission:payments.refunds.manage'
            )->group(function (): void {
                Route::get(
                    '/refunds',
                    [
                        FinanceRefundController::class,
                        'index',
                    ]
                )->name(
                    'refunds.index'
                );

                Route::patch(
                    '/refunds/{refund}/decision',
                    [
                        FinanceRefundController::class,
                        'decide',
                    ]
                )->name(
                    'refunds.decide'
                );

                Route::patch(
                    '/refunds/{refund}/process',
                    [
                        FinanceRefundController::class,
                        'process',
                    ]
                )->name(
                    'refunds.process'
                );
            });
        });

    /*
    |--------------------------------------------------------------------------
    | Shared User Profile
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/profile',
        [
            ProfileController::class,
            'edit',
        ]
    )->name('profile.edit');

    Route::patch(
        '/profile',
        [
            ProfileController::class,
            'update',
        ]
    )->name('profile.update');

    Route::delete(
        '/profile',
        [
            ProfileController::class,
            'destroy',
        ]
    )->name('profile.destroy');
});

require __DIR__.'/auth.php';
require __DIR__.'/admin_operations.php';
require __DIR__.'/notifications.php';
require __DIR__.'/compliance.php';
require __DIR__.'/reports.php';
require __DIR__.'/cms.php';
require __DIR__.'/public.php';
require __DIR__.'/support.php';
