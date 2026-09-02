<?php

use App\Models\Appointment;
use App\Models\ClientProfile;
use App\Models\CounsellorProfile;
use App\Models\User;
use App\Services\Appointments\AppointmentDashboardMetricService;
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

function createAppointmentDashboardMetricUser(string $role, string $name): User
{
    $user = User::factory()->create([
        'name' => $name,
        'email' => fake()->unique()->safeEmail(),
        'phone' => '+94 77 800 1000',
        'is_active' => true,
    ]);

    $user->assignRole($role);

    return $user;
}

function createAppointmentDashboardMetricClientProfile(string $name = 'Metric Client'): ClientProfile
{
    return ClientProfile::factory()->create([
        'user_id' => createAppointmentDashboardMetricUser('client', $name)->id,
    ]);
}

function createAppointmentDashboardMetricCounsellorProfile(string $name = 'Metric Counsellor'): CounsellorProfile
{
    return CounsellorProfile::factory()->create([
        'user_id' => createAppointmentDashboardMetricUser('counsellor', $name)->id,
        'professional_title' => 'Clinical Counsellor',
        'city' => 'Colombo',
        'status' => 'active',
    ]);
}

function createAppointmentDashboardMetricAppointment(
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

it('calculates admin appointment metrics across all appointments', function (): void {
    $clientProfile = createAppointmentDashboardMetricClientProfile();
    $counsellorProfile = createAppointmentDashboardMetricCounsellorProfile();

    createAppointmentDashboardMetricAppointment($clientProfile, $counsellorProfile, [
        'status' => Appointment::STATUS_PENDING,
        'appointment_date' => now()->addDay()->toDateString(),
    ]);

    createAppointmentDashboardMetricAppointment($clientProfile, $counsellorProfile, [
        'status' => Appointment::STATUS_CONFIRMED,
        'appointment_date' => now()->toDateString(),
        'start_time' => '10:00',
        'end_time' => '11:00',
        'reminder_scheduled_at' => now()->subMinute(),
        'reminder_sent_at' => null,
    ]);

    createAppointmentDashboardMetricAppointment($clientProfile, $counsellorProfile, [
        'status' => Appointment::STATUS_COMPLETED,
        'appointment_date' => now()->subDays(2)->toDateString(),
        'start_time' => '11:00',
        'end_time' => '12:00',
    ]);

    createAppointmentDashboardMetricAppointment($clientProfile, $counsellorProfile, [
        'status' => Appointment::STATUS_CANCELLED,
        'appointment_date' => now()->subDays(3)->toDateString(),
        'start_time' => '12:00',
        'end_time' => '13:00',
    ]);

    $metrics = app(AppointmentDashboardMetricService::class)->admin();

    expect($metrics)
        ->total->toBe(4)
        ->pending->toBe(1)
        ->confirmed->toBe(1)
        ->completed->toBe(1)
        ->cancelled->toBe(1)
        ->today->toBe(1)
        ->upcoming->toBe(2)
        ->upcoming_next_7_days->toBe(2)
        ->past->toBe(2)
        ->due_reminders->toBe(1);
});

it('calculates client appointment metrics only for selected client', function (): void {
    $firstClientProfile = createAppointmentDashboardMetricClientProfile('First Metric Client');
    $secondClientProfile = createAppointmentDashboardMetricClientProfile('Second Metric Client');
    $counsellorProfile = createAppointmentDashboardMetricCounsellorProfile();

    createAppointmentDashboardMetricAppointment($firstClientProfile, $counsellorProfile, [
        'status' => Appointment::STATUS_PENDING,
    ]);

    createAppointmentDashboardMetricAppointment($secondClientProfile, $counsellorProfile, [
        'status' => Appointment::STATUS_CONFIRMED,
        'start_time' => '10:00',
        'end_time' => '11:00',
    ]);

    $metrics = app(AppointmentDashboardMetricService::class)
        ->forClient($firstClientProfile);

    expect($metrics)
        ->total->toBe(1)
        ->pending->toBe(1)
        ->confirmed->toBe(0);
});

it('calculates counsellor appointment metrics only for selected counsellor', function (): void {
    $clientProfile = createAppointmentDashboardMetricClientProfile();
    $firstCounsellorProfile = createAppointmentDashboardMetricCounsellorProfile('First Metric Counsellor');
    $secondCounsellorProfile = createAppointmentDashboardMetricCounsellorProfile('Second Metric Counsellor');

    createAppointmentDashboardMetricAppointment($clientProfile, $firstCounsellorProfile, [
        'status' => Appointment::STATUS_CONFIRMED,
    ]);

    createAppointmentDashboardMetricAppointment($clientProfile, $secondCounsellorProfile, [
        'status' => Appointment::STATUS_PENDING,
        'start_time' => '10:00',
        'end_time' => '11:00',
    ]);

    $metrics = app(AppointmentDashboardMetricService::class)
        ->forCounsellor($firstCounsellorProfile);

    expect($metrics)
        ->total->toBe(1)
        ->confirmed->toBe(1)
        ->pending->toBe(0);
});
