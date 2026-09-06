<?php

use App\Http\Controllers\Admin\SessionFeedbackController as AdminSessionFeedbackController;
use App\Http\Controllers\Admin\SupportTicketController as AdminSupportTicketController;
use App\Http\Controllers\Admin\SupportTicketReplyController as AdminSupportTicketReplyController;
use App\Http\Controllers\Client\SessionFeedbackController as ClientSessionFeedbackController;
use App\Http\Controllers\Client\SupportTicketController as ClientSupportTicketController;
use App\Http\Controllers\Client\SupportTicketReplyController as ClientSupportTicketReplyController;
use App\Http\Controllers\PublicSite\SupportContactController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Contact Submission
|--------------------------------------------------------------------------
|
| GET /contact remains owned by M19 PublicSite\ContactController.
| M20 only adds the POST workflow.
|
*/

Route::post(
    '/contact',
    [
        SupportContactController::class,
        'store',
    ]
)
    ->middleware(
        'throttle:10,1'
    )
    ->name(
        'public.contact.store'
    );

/*
|--------------------------------------------------------------------------
| Client Support
|--------------------------------------------------------------------------
*/

Route::middleware([
    'auth',
    'active',
    'permission:support.client.manage',
])
    ->prefix(
        'client/support'
    )
    ->name(
        'client.support.'
    )
    ->group(
        function (): void {
            Route::get(
                '/',
                [
                    ClientSupportTicketController::class,
                    'index',
                ]
            )->name('index');

            Route::get(
                '/create',
                [
                    ClientSupportTicketController::class,
                    'create',
                ]
            )->name('create');

            Route::post(
                '/',
                [
                    ClientSupportTicketController::class,
                    'store',
                ]
            )->name('store');

            Route::get(
                '/feedback',
                [
                    ClientSessionFeedbackController::class,
                    'create',
                ]
            )->name(
                'feedback.create'
            );

            Route::post(
                '/feedback/{appointment:uuid}',
                [
                    ClientSessionFeedbackController::class,
                    'store',
                ]
            )->name(
                'feedback.store'
            );

            Route::get(
                '/{ticket:uuid}',
                [
                    ClientSupportTicketController::class,
                    'show',
                ]
            )->name('show');

            Route::post(
                '/{ticket:uuid}/replies',
                [
                    ClientSupportTicketReplyController::class,
                    'store',
                ]
            )->name(
                'replies.store'
            );
        }
    );

/*
|--------------------------------------------------------------------------
| Administrative Support
|--------------------------------------------------------------------------
*/

Route::middleware([
    'auth',
    'active',
    'permission:dashboard.admin.view',
])
    ->prefix(
        'admin/support'
    )
    ->name(
        'admin.support.'
    )
    ->group(
        function (): void {
            Route::middleware(
                'permission:support.admin.view'
            )->group(
                function (): void {
                    Route::get(
                        '/',
                        [
                            AdminSupportTicketController::class,
                            'index',
                        ]
                    )->name(
                        'index'
                    );

                    Route::get(
                        '/feedback',
                        [
                            AdminSessionFeedbackController::class,
                            'index',
                        ]
                    )->name(
                        'feedback.index'
                    );

                    Route::get(
                        '/{ticket:uuid}',
                        [
                            AdminSupportTicketController::class,
                            'show',
                        ]
                    )->name(
                        'show'
                    );
                }
            );

            Route::middleware(
                'permission:support.admin.manage'
            )->group(
                function (): void {
                    Route::patch(
                        '/{ticket:uuid}',
                        [
                            AdminSupportTicketController::class,
                            'update',
                        ]
                    )->name(
                        'update'
                    );

                    Route::post(
                        '/{ticket:uuid}/replies',
                        [
                            AdminSupportTicketReplyController::class,
                            'store',
                        ]
                    )->name(
                        'replies.store'
                    );
                }
            );
        }
    );
