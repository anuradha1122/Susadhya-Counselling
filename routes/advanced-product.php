<?php

use App\Http\Controllers\Admin\AdvancedProduct\AdminAiSummaryController;
use App\Http\Controllers\Admin\AdvancedProduct\ClientSubscriptionController;
use App\Http\Controllers\Admin\AdvancedProduct\ContentTranslationController;
use App\Http\Controllers\Admin\AdvancedProduct\GroupCounsellingProgramController;
use App\Http\Controllers\Admin\AdvancedProduct\ServicePackageController;
use App\Http\Controllers\Client\GroupCounsellingController;
use App\Http\Controllers\Client\PackageSubscriptionController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::prefix('admin/advanced-products')
        ->name('admin.advanced-products.')
        ->middleware('system.admin')
        ->group(function () {
            Route::get('/', [GroupCounsellingProgramController::class, 'index'])
                ->name('index')
                ->middleware('permission:advanced-products.view');

            Route::resource('group-counselling', GroupCounsellingProgramController::class)
                ->parameters(['group-counselling' => 'program'])
                ->except(['show'])
                ->middleware('permission:advanced-products.manage');

            Route::resource('packages', ServicePackageController::class)
                ->parameters(['packages' => 'package'])
                ->except(['show'])
                ->middleware('permission:advanced-products.manage');

            Route::get('subscriptions', [ClientSubscriptionController::class, 'index'])
                ->name('subscriptions.index')
                ->middleware('permission:advanced-products.view');

            Route::patch('subscriptions/{subscription:uuid}', [ClientSubscriptionController::class, 'update'])
                ->name('subscriptions.update')
                ->middleware('permission:advanced-products.manage');

            Route::get('ai-summaries', [AdminAiSummaryController::class, 'index'])
                ->name('ai-summaries.index')
                ->middleware('permission:advanced-products.ai-summaries.view');

            Route::post('ai-summaries', [AdminAiSummaryController::class, 'store'])
                ->name('ai-summaries.store')
                ->middleware('permission:advanced-products.ai-summaries.manage');

            Route::patch('ai-summaries/{summary:uuid}/review', [AdminAiSummaryController::class, 'review'])
                ->name('ai-summaries.review')
                ->middleware('permission:advanced-products.ai-summaries.manage');

            Route::resource('translations', ContentTranslationController::class)
                ->parameters(['translations' => 'translation'])
                ->except(['show'])
                ->middleware('permission:advanced-products.translations.manage');
        });

    Route::prefix('client/advanced-products')
        ->name('client.advanced-products.')
        ->group(function () {
            Route::get('groups', [GroupCounsellingController::class, 'index'])
                ->name('groups.index')
                ->middleware('permission:group-counselling.client.view');

            Route::post('groups/{program:uuid}/enroll', [GroupCounsellingController::class, 'enroll'])
                ->name('groups.enroll')
                ->middleware('permission:group-counselling.client.enroll');

            Route::get('packages', [PackageSubscriptionController::class, 'index'])
                ->name('packages.index')
                ->middleware('permission:packages.client.view');

            Route::post('packages/{package:uuid}/subscribe', [PackageSubscriptionController::class, 'subscribe'])
                ->name('packages.subscribe')
                ->middleware('permission:packages.client.subscribe');
        });
});
