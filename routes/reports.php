<?php

use App\Http\Controllers\Admin\ReportController as AdminReportController;
use App\Http\Controllers\Finance\ReportController as FinanceReportController;
use Illuminate\Support\Facades\Route;

Route::middleware([
    'auth',
    'active',
])
    ->group(function (): void {
        Route::prefix('admin')
            ->name('admin.')
            ->group(function (): void {
                Route::get(
                    '/reports',
                    [
                        AdminReportController::class,
                        'index',
                    ]
                )
                    ->middleware(
                        'permission:reports.operational.view'
                    )
                    ->name('reports.index');

                Route::get(
                    '/reports/csv',
                    [
                        AdminReportController::class,
                        'csv',
                    ]
                )
                    ->middleware(
                        'permission:reports.operational.export'
                    )
                    ->name('reports.csv');

                Route::get(
                    '/reports/pdf',
                    [
                        AdminReportController::class,
                        'pdf',
                    ]
                )
                    ->middleware(
                        'permission:reports.operational.export'
                    )
                    ->name('reports.pdf');
            });

        Route::prefix('finance')
            ->name('finance.')
            ->group(function (): void {
                Route::get(
                    '/reports',
                    [
                        FinanceReportController::class,
                        'index',
                    ]
                )
                    ->middleware(
                        'permission:reports.finance.view'
                    )
                    ->name('reports.index');

                Route::get(
                    '/reports/csv',
                    [
                        FinanceReportController::class,
                        'csv',
                    ]
                )
                    ->middleware(
                        'permission:reports.finance.export'
                    )
                    ->name('reports.csv');

                Route::get(
                    '/reports/pdf',
                    [
                        FinanceReportController::class,
                        'pdf',
                    ]
                )
                    ->middleware(
                        'permission:reports.finance.export'
                    )
                    ->name('reports.pdf');
            });
    });
