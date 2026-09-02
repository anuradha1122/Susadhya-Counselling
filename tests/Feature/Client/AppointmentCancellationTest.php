<?php

use App\Models\Appointment;
use App\Models\ClientProfile;
use App\Models\CounsellorProfile;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    app(PermissionRegistrar::class)->forgetCachedPermissions();

    $this->seed(RolePermissionSeeder::class);
});

function createAppointmentCancellationClientProfile(string $name = 'Cancellation Client'): ClientProfile
{
    $user = User::factory()->create([
        'name' => $name,
        'email' => fake()->unique()->safeEmail(),
        'is_active' => true,
    ]);

    $user->assignRole('client');

    return ClientProfile::factory()->create([
        'user_id' => $user->id,
    ]);
}

function createAppointmentCancellationCounsellorProfile(): CounsellorProfile
{
    $user = User::factory()->create([
        'name' => 'Cancellation Counsellor',
        'email' => fake()->unique()->safeEmail(),
        'is_active' => true,
    ]);

    $user->assignRole('counsellor');

    return CounsellorProfile::factory()->create([
        'user_id' => $user->id,
        'status' => 'active',
    ]);
}

function createAppointmentCancellationAppointment(
    ClientProfile $clientProfile,
    CounsellorProfile $counsellorProfile,
    string $status = Appointment::STATUS_PENDING
): Appointment {
    return Appointment::query()->create([
        'client_profile_id' => $clientProfile->id,
        'counsellor_profile_id' => $counsellorProfile->id,
        'appointment_date' => now()->addDays(3)->toDateString(),
        'start_time' => '09:00',
        'end_time' => '10:00',
        'timezone' => 'Asia/Colombo',
        'mode' => Appointment::MODE_ONLINE,
        'status' => $status,
        'client_notes' => 'Appointment cancellation test.',
    ]);
}

it('allows a client to cancel their pending appointment', function (): void {
    $clientProfile = createAppointmentCancellationClientProfile();
    $counsellorProfile = createAppointmentCancellationCounsellorProfile();

    $appointment = createAppointmentCancellationAppointment(
        clientProfile: $clientProfile,
        counsellorProfile: $counsellorProfile,
        status: Appointment::STATUS_PENDING
    );

    $response = $this
        ->actingAs($clientProfile->user)
        ->from(route('client.appointments.index'))
        ->patch(route('client.appointments.cancel', $appointment), [
            'cancellation_reason' => 'Personal schedule change.',
        ]);

    $response->assertRedirect(route('client.appointments.index'));

    $this->assertDatabaseHas('appointments', [
        'id' => $appointment->id,
        'status' => Appointment::STATUS_CANCELLED,
        'cancellation_reason' => 'Personal schedule change.',
        'cancelled_by' => $clientProfile->user_id,
    ]);

    $this->assertDatabaseHas('appointment_status_histories', [
        'appointment_id' => $appointment->id,
        'from_status' => Appointment::STATUS_PENDING,
        'to_status' => Appointment::STATUS_CANCELLED,
        'reason' => 'Appointment cancelled by client.',
        'changed_by' => $clientProfile->user_id,
    ]);
});

it('allows a client to cancel their confirmed appointment', function (): void {
    $clientProfile = createAppointmentCancellationClientProfile();
    $counsellorProfile = createAppointmentCancellationCounsellorProfile();

    $appointment = createAppointmentCancellationAppointment(
        clientProfile: $clientProfile,
        counsellorProfile: $counsellorProfile,
        status: Appointment::STATUS_CONFIRMED
    );

    $this
        ->actingAs($clientProfile->user)
        ->patch(route('client.appointments.cancel', $appointment), [
            'cancellation_reason' => null,
        ])
        ->assertRedirect(route('client.appointments.index'));

    $this->assertDatabaseHas('appointments', [
        'id' => $appointment->id,
        'status' => Appointment::STATUS_CANCELLED,
        'cancellation_reason' => 'Cancelled by client.',
        'cancelled_by' => $clientProfile->user_id,
    ]);

    $this->assertDatabaseHas('appointment_status_histories', [
        'appointment_id' => $appointment->id,
        'from_status' => Appointment::STATUS_CONFIRMED,
        'to_status' => Appointment::STATUS_CANCELLED,
    ]);
});

it('prevents a client from cancelling another clients appointment', function (): void {
    $firstClientProfile = createAppointmentCancellationClientProfile('First Cancellation Client');
    $secondClientProfile = createAppointmentCancellationClientProfile('Second Cancellation Client');
    $counsellorProfile = createAppointmentCancellationCounsellorProfile();

    $appointment = createAppointmentCancellationAppointment(
        clientProfile: $secondClientProfile,
        counsellorProfile: $counsellorProfile,
        status: Appointment::STATUS_PENDING
    );

    $this
        ->actingAs($firstClientProfile->user)
        ->patch(route('client.appointments.cancel', $appointment), [
            'cancellation_reason' => 'Trying to cancel another client appointment.',
        ])
        ->assertNotFound();

    $this->assertDatabaseHas('appointments', [
        'id' => $appointment->id,
        'status' => Appointment::STATUS_PENDING,
        'cancellation_reason' => null,
        'cancelled_by' => null,
    ]);
});

it('prevents a client from cancelling a completed appointment', function (): void {
    $clientProfile = createAppointmentCancellationClientProfile();
    $counsellorProfile = createAppointmentCancellationCounsellorProfile();

    $appointment = createAppointmentCancellationAppointment(
        clientProfile: $clientProfile,
        counsellorProfile: $counsellorProfile,
        status: Appointment::STATUS_COMPLETED
    );

    $this
        ->actingAs($clientProfile->user)
        ->from(route('client.appointments.index'))
        ->patch(route('client.appointments.cancel', $appointment), [
            'cancellation_reason' => 'Trying to cancel completed appointment.',
        ])
        ->assertRedirect(route('client.appointments.index'))
        ->assertSessionHasErrors('appointment');

    $this->assertDatabaseHas('appointments', [
        'id' => $appointment->id,
        'status' => Appointment::STATUS_COMPLETED,
        'cancellation_reason' => null,
        'cancelled_by' => null,
    ]);
});

it('prevents a client from cancelling an already cancelled appointment', function (): void {
    $clientProfile = createAppointmentCancellationClientProfile();
    $counsellorProfile = createAppointmentCancellationCounsellorProfile();

    $appointment = createAppointmentCancellationAppointment(
        clientProfile: $clientProfile,
        counsellorProfile: $counsellorProfile,
        status: Appointment::STATUS_CANCELLED
    );

    $this
        ->actingAs($clientProfile->user)
        ->from(route('client.appointments.index'))
        ->patch(route('client.appointments.cancel', $appointment), [
            'cancellation_reason' => 'Trying again.',
        ])
        ->assertRedirect(route('client.appointments.index'))
        ->assertSessionHasErrors('appointment');

    $this->assertDatabaseHas('appointments', [
        'id' => $appointment->id,
        'status' => Appointment::STATUS_CANCELLED,
        'cancellation_reason' => null,
        'cancelled_by' => null,
    ]);
});

it('requires authenticated client role to cancel appointment', function (): void {
    $clientProfile = createAppointmentCancellationClientProfile();
    $counsellorProfile = createAppointmentCancellationCounsellorProfile();

    $appointment = createAppointmentCancellationAppointment(
        clientProfile: $clientProfile,
        counsellorProfile: $counsellorProfile,
        status: Appointment::STATUS_PENDING
    );

    $admin = User::factory()->create([
        'is_active' => true,
    ]);

    $admin->assignRole('admin');

    $this
        ->actingAs($admin)
        ->patch(route('client.appointments.cancel', $appointment), [
            'cancellation_reason' => 'Admin trying client cancel route.',
        ])
        ->assertForbidden();

    $this->assertDatabaseHas('appointments', [
        'id' => $appointment->id,
        'status' => Appointment::STATUS_PENDING,
    ]);
});
