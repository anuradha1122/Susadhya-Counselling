<?php

use App\Http\Controllers\Admin\ClientController as AdminClientController;
use App\Http\Controllers\Admin\CounsellingServiceController;
use App\Http\Controllers\Admin\CounsellorController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\ServiceCategoryController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Client\DashboardController as ClientDashboardController;
use App\Http\Controllers\Client\EmergencyContactController as ClientEmergencyContactController;
use App\Http\Controllers\Client\PreferenceController as ClientPreferenceController;
use App\Http\Controllers\Client\PrivacySettingsController as ClientPrivacySettingsController;
use App\Http\Controllers\Client\ProfileController as ClientProfileController;
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
                'clients',
                AdminClientController::class
            )->only([
                'index',
                'show',
                'destroy',
            ]);

            Route::patch('/clients/{client}/status', [
                AdminClientController::class,
                'updateStatus',
            ])->name('clients.status');

            Route::patch('/clients/{client}/restore', [
                AdminClientController::class,
                'restore',
            ])->name('clients.restore');

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

    Route::prefix('client')
        ->name('client.')
        ->middleware('role:client')
        ->group(function () {
            Route::get(
                '/dashboard',
                ClientDashboardController::class
            )->name('dashboard');

            Route::get('/profile', [
                ClientProfileController::class,
                'show',
            ])->name('profile.show');

            Route::get('/profile/edit', [
                ClientProfileController::class,
                'edit',
            ])->name('profile.edit');

            Route::patch('/profile', [
                ClientProfileController::class,
                'update',
            ])->name('profile.update');

            Route::resource(
                'emergency-contacts',
                ClientEmergencyContactController::class
            )->except([
                'show',
            ]);

            Route::get('/preferences', [
                ClientPreferenceController::class,
                'show',
            ])->name('preferences.show');

            Route::get('/preferences/edit', [
                ClientPreferenceController::class,
                'edit',
            ])->name('preferences.edit');

            Route::patch('/preferences', [
                ClientPreferenceController::class,
                'update',
            ])->name('preferences.update');

            Route::get('/privacy', [
                ClientPrivacySettingsController::class,
                'show',
            ])->name('privacy.show');

            Route::get('/privacy/edit', [
                ClientPrivacySettingsController::class,
                'edit',
            ])->name('privacy.edit');

            Route::patch('/privacy', [
                ClientPrivacySettingsController::class,
                'update',
            ])->name('privacy.update');
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
