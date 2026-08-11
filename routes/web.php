<?php

use App\Http\Controllers\Admin\CounsellingServiceController;
use App\Http\Controllers\Admin\CounsellorController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\ServiceCategoryController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Counsellor\DashboardController as CounsellorDashboardController;
use App\Http\Controllers\DashboardRedirectController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return inertia('Welcome');
})->name('home');

Route::middleware(['auth', 'active'])->group(function () {
    Route::get('/dashboard', DashboardRedirectController::class)
        ->name('dashboard');

    Route::prefix('admin')
        ->name('admin.')
        ->middleware('permission:dashboard.admin.view')
        ->group(function () {
            Route::get('/dashboard', AdminDashboardController::class)
                ->name('dashboard');

            Route::resource('users', UserController::class)
                ->except('show');

            Route::patch('/users/{user}/status', [
                UserController::class,
                'updateStatus',
            ])->name('users.status');

            Route::resource('roles', RoleController::class)
                ->except('show');

            Route::resource(
                'counsellors',
                CounsellorController::class
            );

            Route::patch(
                'counsellors/{counsellor}/restore',
                [CounsellorController::class, 'restore']
            )->name('counsellors.restore');

            Route::resource(
                'service-categories',
                ServiceCategoryController::class
            );

            Route::patch(
                'service-categories/{service_category}/restore',
                [ServiceCategoryController::class, 'restore']
            )->name('service-categories.restore');

            Route::resource(
                'counselling-services',
                CounsellingServiceController::class
            );

            Route::patch(
                'counselling-services/{counselling_service}/restore',
                [CounsellingServiceController::class, 'restore']
            )->name('counselling-services.restore');

        });

    Route::prefix('counsellor')
        ->name('counsellor.')
        ->middleware('role:counsellor')
        ->group(function () {
            Route::get(
                '/dashboard',
                CounsellorDashboardController::class
            )->name('dashboard');
        });

    Route::get('/profile', [
        ProfileController::class,
        'edit',
    ])->name('profile.edit');

    Route::patch('/profile', [
        ProfileController::class,
        'update',
    ])->name('profile.update');

    Route::delete('/profile', [
        ProfileController::class,
        'destroy',
    ])->name('profile.destroy');
});

require __DIR__.'/auth.php';
