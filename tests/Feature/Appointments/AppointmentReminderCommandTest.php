<?php

use App\Models\Appointment;
use App\Models\ClientProfile;
use App\Models\CounsellorProfile;
use App\Models\User;
use App\Notifications\AppointmentReminderNotification;
use Carbon\CarbonImmutable;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    app(PermissionRegistrar::class)->forgetCachedPermissions();

    $this->seed(RolePermissionSeeder::class);
});

function createAppointmentReminderClientProfile(string $name = 'Reminder Client'): ClientProfile
{
    $user = User::factory()->create([
        'name' => $name,
        'email' => fake()->unique()->safeEmail(),
        'phone' => '+94 77 700 1111',
        'is_active' => true,
    ]);

    $user->assignRole('client');

    return ClientProfile::factory()->create([
        'user_id' => $user->id,
    ]);
}

function createAppointmentReminderCounsellorProfile(
    string $name = 'Reminder Counsellor',
    array $userOverrides = []
): CounsellorProfile {
    $user = User::factory()->create(array_merge([
        'name' => $name,
        'email' => fake()->unique()->safeEmail(),
        'phone' => '+94 77 700 2222',
        'is_active' => true,
    ], $userOverrides));

    $user->assignRole('counsellor');

    return CounsellorProfile::factory()->create([
        'user_id' => $user->id,
        'professional_title' => 'Clinical Counsellor',
        'city' => 'Colombo',
        'status' => 'active',
    ]);
}

function createAppointmentReminderAppointment(
    ClientProfile $clientProfile,
    CounsellorProfile $counsellorProfile,
    array $overrides = []
): Appointment {
    return Appointment::query()->create(array_merge([
        'client_profile_id' => $clientProfile->id,
        'counsellor_profile_id' => $counsellorProfile->id,
        'appointment_date' => CarbonImmutable::now()->addDays(2)->toDateString(),
        'start_time' => '09:00',
        'end_time' => '10:00',
        'timezone' => 'Asia/Colombo',
        'mode' => Appointment::MODE_ONLINE,
        'status' => Appointment::STATUS_CONFIRMED,
        'meeting_link' => 'https://meet.google.com/reminder-room',
        'reminder_scheduled_at' => now()->subMinute(),
        'reminder_sent_at' => null,
    ], $overrides));
}

it('sends due reminder notifications to active client and counsellor', function (): void {
    Notification::fake();

    $clientProfile = createAppointmentReminderClientProfile();
    $counsellorProfile = createAppointmentReminderCounsellorProfile();

    $appointment = createAppointmentReminderAppointment(
        clientProfile: $clientProfile,
        counsellorProfile: $counsellorProfile
    );

    $this
        ->artisan('appointments:send-reminders')
        ->assertExitCode(0);

    Notification::assertSentTo(
        $clientProfile->user,
        AppointmentReminderNotification::class
    );

    Notification::assertSentTo(
        $counsellorProfile->user,
        AppointmentReminderNotification::class
    );

    expect($appointment->refresh()->reminder_sent_at)->not->toBeNull();
});

it('does not send reminder for pending appointments', function (): void {
    Notification::fake();

    $clientProfile = createAppointmentReminderClientProfile();
    $counsellorProfile = createAppointmentReminderCounsellorProfile();

    $appointment = createAppointmentReminderAppointment(
        clientProfile: $clientProfile,
        counsellorProfile: $counsellorProfile,
        overrides: [
            'status' => Appointment::STATUS_PENDING,
        ]
    );

    $this
        ->artisan('appointments:send-reminders')
        ->assertExitCode(0);

    Notification::assertNotSentTo(
        $clientProfile->user,
        AppointmentReminderNotification::class
    );

    Notification::assertNotSentTo(
        $counsellorProfile->user,
        AppointmentReminderNotification::class
    );

    expect($appointment->refresh()->reminder_sent_at)->toBeNull();
});

it('does not send reminder before scheduled reminder time', function (): void {
    Notification::fake();

    $clientProfile = createAppointmentReminderClientProfile();
    $counsellorProfile = createAppointmentReminderCounsellorProfile();

    $appointment = createAppointmentReminderAppointment(
        clientProfile: $clientProfile,
        counsellorProfile: $counsellorProfile,
        overrides: [
            'reminder_scheduled_at' => now()->addHour(),
        ]
    );

    $this
        ->artisan('appointments:send-reminders')
        ->assertExitCode(0);

    Notification::assertNotSentTo(
        $clientProfile->user,
        AppointmentReminderNotification::class
    );

    Notification::assertNotSentTo(
        $counsellorProfile->user,
        AppointmentReminderNotification::class
    );

    expect($appointment->refresh()->reminder_sent_at)->toBeNull();
});

it('does not resend already sent reminders', function (): void {
    Notification::fake();

    $clientProfile = createAppointmentReminderClientProfile();
    $counsellorProfile = createAppointmentReminderCounsellorProfile();

    $appointment = createAppointmentReminderAppointment(
        clientProfile: $clientProfile,
        counsellorProfile: $counsellorProfile,
        overrides: [
            'reminder_sent_at' => now()->subMinutes(10),
        ]
    );

    $this
        ->artisan('appointments:send-reminders')
        ->assertExitCode(0);

    Notification::assertNotSentTo(
        $clientProfile->user,
        AppointmentReminderNotification::class
    );

    Notification::assertNotSentTo(
        $counsellorProfile->user,
        AppointmentReminderNotification::class
    );

    expect($appointment->refresh()->reminder_sent_at)->not->toBeNull();
});

it('supports dry run without sending or marking reminders as sent', function (): void {
    Notification::fake();

    $clientProfile = createAppointmentReminderClientProfile();
    $counsellorProfile = createAppointmentReminderCounsellorProfile();

    $appointment = createAppointmentReminderAppointment(
        clientProfile: $clientProfile,
        counsellorProfile: $counsellorProfile
    );

    $this
        ->artisan('appointments:send-reminders --dry-run')
        ->assertExitCode(0);

    Notification::assertNothingSent();

    expect($appointment->refresh()->reminder_sent_at)->toBeNull();
});

it('skips inactive reminder recipients but still sends to active recipient', function (): void {
    Notification::fake();

    $clientProfile = createAppointmentReminderClientProfile();
    $counsellorProfile = createAppointmentReminderCounsellorProfile(
        name: 'Inactive Reminder Counsellor',
        userOverrides: [
            'is_active' => false,
        ]
    );

    $appointment = createAppointmentReminderAppointment(
        clientProfile: $clientProfile,
        counsellorProfile: $counsellorProfile
    );

    $this
        ->artisan('appointments:send-reminders')
        ->assertExitCode(0);

    Notification::assertSentTo(
        $clientProfile->user,
        AppointmentReminderNotification::class
    );

    Notification::assertNotSentTo(
        $counsellorProfile->user,
        AppointmentReminderNotification::class
    );

    expect($appointment->refresh()->reminder_sent_at)->not->toBeNull();
});
