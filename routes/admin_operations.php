<?php

use App\Http\Controllers\Admin\CaseEscalationController;
use App\Http\Controllers\Admin\ContentSnippetController;
use App\Http\Controllers\Admin\OperationalExceptionController;
use App\Http\Controllers\Admin\OperationsDashboardController;
use App\Http\Controllers\Admin\SystemSettingController;
use Illuminate\Support\Facades\Route;

Route::middleware([
    'auth',
    'active',
])
    ->prefix('admin')
    ->name('admin.')
    ->group(function (): void {
        Route::get(
            '/operations',
            OperationsDashboardController::class
        )
            ->middleware('permission:admin.operations.view')
            ->name('operations.index');

        Route::post(
            '/operations/exceptions',
            [
                OperationalExceptionController::class,
                'store',
            ]
        )
            ->middleware('permission:admin.operations.manage')
            ->name('operations.exceptions.store');

        Route::patch(
            '/operations/exceptions/{operationalException}',
            [
                OperationalExceptionController::class,
                'update',
            ]
        )
            ->middleware('permission:admin.operations.manage')
            ->name('operations.exceptions.update');

        Route::get(
            '/case-escalations',
            [
                CaseEscalationController::class,
                'index',
            ]
        )
            ->middleware(
                'permission:admin.case-escalations.manage'
            )
            ->name('case-escalations.index');

        Route::post(
            '/case-escalations',
            [
                CaseEscalationController::class,
                'store',
            ]
        )
            ->middleware(
                'permission:admin.case-escalations.manage'
            )
            ->name('case-escalations.store');

        Route::patch(
            '/case-escalations/{caseEscalation}',
            [
                CaseEscalationController::class,
                'update',
            ]
        )
            ->middleware(
                'permission:admin.case-escalations.manage'
            )
            ->name('case-escalations.update');

        Route::get(
            '/settings',
            [
                SystemSettingController::class,
                'index',
            ]
        )
            ->middleware('permission:settings.view')
            ->name('settings.index');

        Route::patch(
            '/settings',
            [
                SystemSettingController::class,
                'update',
            ]
        )
            ->middleware('permission:settings.update')
            ->name('settings.update');

        Route::get(
            '/content-snippets',
            [
                ContentSnippetController::class,
                'index',
            ]
        )
            ->middleware('permission:admin.content.manage')
            ->name('content-snippets.index');

        Route::post(
            '/content-snippets',
            [
                ContentSnippetController::class,
                'store',
            ]
        )
            ->middleware('permission:admin.content.manage')
            ->name('content-snippets.store');

        Route::patch(
            '/content-snippets/{contentSnippet}',
            [
                ContentSnippetController::class,
                'update',
            ]
        )
            ->middleware('permission:admin.content.manage')
            ->name('content-snippets.update');
    });
