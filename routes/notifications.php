<?php

use App\Http\Controllers\Admin\NotificationDeliveryController;
use App\Http\Controllers\Admin\NotificationTemplateController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\NotificationPreferenceController;
use Illuminate\Support\Facades\Route;

Route::middleware([
    'auth',
    'verified',
])->group(function (): void {
    Route::prefix('notifications')
        ->name('notifications.')
        ->group(function (): void {
            Route::get(
                '/',
                [NotificationController::class, 'index']
            )->name('index');

            Route::get(
                '/summary',
                [NotificationController::class, 'summary']
            )->name('summary');

            Route::patch(
                '/read-all',
                [NotificationController::class, 'markAllAsRead']
            )->name('read-all');

            Route::patch(
                '/{notification}/read',
                [NotificationController::class, 'markAsRead']
            )->name('read');

            Route::get(
                '/preferences/edit',
                [NotificationPreferenceController::class, 'edit']
            )->name('preferences.edit');

            Route::put(
                '/preferences',
                [NotificationPreferenceController::class, 'update']
            )->name('preferences.update');
        });

    Route::prefix('admin/notifications')
        ->name('admin.notifications.')
        ->group(function (): void {
            Route::get(
                '/templates',
                [NotificationTemplateController::class, 'index']
            )
                ->middleware(
                    'permission:notifications.templates.manage'
                )
                ->name('templates.index');

            Route::put(
                '/templates/{template}',
                [NotificationTemplateController::class, 'update']
            )
                ->middleware(
                    'permission:notifications.templates.manage'
                )
                ->name('templates.update');

            Route::get(
                '/deliveries',
                [NotificationDeliveryController::class, 'index']
            )
                ->middleware(
                    'permission:notifications.deliveries.view'
                )
                ->name('deliveries.index');
        });
});
