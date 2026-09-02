<?php

use App\Models\Appointment;
use App\Models\ClientProfile;
use App\Models\CounsellorProfile;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Carbon::setTestNow(Carbon::parse('2026-09-02 09:00:00'));

    app(PermissionRegistrar::class)->forgetCachedPermissions();

    $this->seed(RolePermissionSeeder::class);
});

afterEach(function (): void {
    Carbon::setTestNow();
});

function createAppointmentDashboardRouteUser(string $role, string $name): User
{
    $user = User::factory()->create([
        'name' => $name,
        'email' => fake()->unique()->safeEmail(),
        'phone' => '+94 77 900 1000',
        'is_active' => true,
    ]);

    $user->assignRole($role);

    return $user;
}

function createAppointmentDashboardRouteClientProfile(string $name = 'Route Client'): ClientProfile
{
    return ClientProfile::factory()->create([
        'user_id' => createAppointmentDashboardRouteUser('client', $name)->id,
    ]);
}

function createAppointmentDashboardRouteCounsellorProfile(string $name = 'Route Counsellor'): CounsellorProfile
{
    return CounsellorProfile::factory()->create([
        'user_id' => createAppointmentDashboardRouteUser('counsellor', $name)->id,
        'professional_title' => 'Clinical Counsellor',
        'city' => 'Colombo',
        'status' => 'active',
    ]);
}

function createAppointmentDashboardRouteAppointment(
    ClientProfile $clientProfile,
    CounsellorProfile $counsellorProfile,
    array $overrides = []
): Appointment {
    return Appointment::query()->create(array_merge([
        'client_profile_id' => $clientProfile->id,
        'counsellor_profile_id' => $counsellorProfile->id,
        'appointment_date' => now()->addDays(2)->toDateString(),
        'start_time' => '09:00',
        'end_time' => '10:00',
        'timezone' => 'Asia/Colombo',
        'mode' => Appointment::MODE_ONLINE,
        'status' => Appointment::STATUS_PENDING,
    ], $overrides));
}

it('shares appointment metrics with admin dashboard', function (): void {
    $admin = createAppointmentDashboardRouteUser('admin', 'Dashboard Admin');
    $clientProfile = createAppointmentDashboardRouteClientProfile();
    $counsellorProfile = createAppointmentDashboardRouteCounsellorProfile();

    createAppointmentDashboardRouteAppointment($clientProfile, $counsellorProfile);

    $response = $this
        ->actingAs($admin)
        ->get(route('admin.dashboard'));

    $response->assertOk();

    $metrics = $response->viewData('page')['props']['appointmentMetrics'];

    expect($metrics)
        ->total->toBe(1)
        ->pending->toBe(1);
});

it('shares appointment metrics with client dashboard scoped to client', function (): void {
    $clientProfile = createAppointmentDashboardRouteClientProfile();
    $otherClientProfile = createAppointmentDashboardRouteClientProfile('Other Dashboard Client');
    $counsellorProfile = createAppointmentDashboardRouteCounsellorProfile();

    createAppointmentDashboardRouteAppointment($clientProfile, $counsellorProfile);

    createAppointmentDashboardRouteAppointment($otherClientProfile, $counsellorProfile, [
        'start_time' => '10:00',
        'end_time' => '11:00',
    ]);

    $response = $this
        ->actingAs($clientProfile->user)
        ->get(route('client.dashboard'));

    $response->assertOk();

    $metrics = $response->viewData('page')['props']['appointmentMetrics'];

    expect($metrics)
        ->total->toBe(1)
        ->pending->toBe(1);
});

it('shares appointment metrics with counsellor dashboard scoped to counsellor', function (): void {
    $clientProfile = createAppointmentDashboardRouteClientProfile();
    $counsellorProfile = createAppointmentDashboardRouteCounsellorProfile();
    $otherCounsellorProfile = createAppointmentDashboardRouteCounsellorProfile('Other Dashboard Counsellor');

    createAppointmentDashboardRouteAppointment($clientProfile, $counsellorProfile);

    createAppointmentDashboardRouteAppointment($clientProfile, $otherCounsellorProfile, [
        'start_time' => '10:00',
        'end_time' => '11:00',
    ]);

    $response = $this
        ->actingAs($counsellorProfile->user)
        ->get(route('counsellor.dashboard'));

    $response->assertOk();

    $metrics = $response->viewData('page')['props']['appointmentMetrics'];

    expect($metrics)
        ->total->toBe(1)
        ->pending->toBe(1);
});
