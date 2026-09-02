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

function createCounsellorOutcomeClientProfile(string $name = 'Outcome Client'): ClientProfile
{
    $user = User::factory()->create([
        'name' => $name,
        'email' => fake()->unique()->safeEmail(),
        'phone' => '+94 77 444 5555',
        'is_active' => true,
    ]);

    $user->assignRole('client');

    return ClientProfile::factory()->create([
        'user_id' => $user->id,
    ]);
}

function createCounsellorOutcomeCounsellorProfile(string $name = 'Outcome Counsellor'): CounsellorProfile
{
    $user = User::factory()->create([
        'name' => $name,
        'email' => fake()->unique()->safeEmail(),
        'is_active' => true,
    ]);

    $user->assignRole('counsellor');

    return CounsellorProfile::factory()->create([
        'user_id' => $user->id,
        'professional_title' => 'Clinical Counsellor',
        'city' => 'Colombo',
        'status' => 'active',
    ]);
}

function createCounsellorOutcomeAppointment(
    ClientProfile $clientProfile,
    CounsellorProfile $counsellorProfile,
    string $status = Appointment::STATUS_CONFIRMED,
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
        'status' => $status,
        'meeting_link' => 'https://meet.google.com/outcome-room',
        'client_notes' => 'Outcome workflow test.',
    ], $overrides));
}

it('allows counsellor to mark their confirmed appointment as completed', function (): void {
    $clientProfile = createCounsellorOutcomeClientProfile();
    $counsellorProfile = createCounsellorOutcomeCounsellorProfile();

    $appointment = createCounsellorOutcomeAppointment(
        clientProfile: $clientProfile,
        counsellorProfile: $counsellorProfile,
        status: Appointment::STATUS_CONFIRMED
    );

    $response = $this
        ->actingAs($counsellorProfile->user)
        ->from(route('counsellor.appointments.index'))
        ->patch(route('counsellor.appointments.complete', $appointment), [
            'counsellor_notes' => 'Session completed successfully.',
        ]);

    $response->assertRedirect(route('counsellor.appointments.index'));

    $this->assertDatabaseHas('appointments', [
        'id' => $appointment->id,
        'status' => Appointment::STATUS_COMPLETED,
        'counsellor_notes' => 'Session completed successfully.',
        'updated_by' => $counsellorProfile->user_id,
    ]);

    $this->assertDatabaseHas('appointment_status_histories', [
        'appointment_id' => $appointment->id,
        'from_status' => Appointment::STATUS_CONFIRMED,
        'to_status' => Appointment::STATUS_COMPLETED,
        'reason' => 'Appointment marked as completed by counsellor.',
        'changed_by' => $counsellorProfile->user_id,
    ]);
});

it('allows counsellor to mark their confirmed appointment as no-show', function (): void {
    $clientProfile = createCounsellorOutcomeClientProfile();
    $counsellorProfile = createCounsellorOutcomeCounsellorProfile();

    $appointment = createCounsellorOutcomeAppointment(
        clientProfile: $clientProfile,
        counsellorProfile: $counsellorProfile,
        status: Appointment::STATUS_CONFIRMED
    );

    $response = $this
        ->actingAs($counsellorProfile->user)
        ->from(route('counsellor.appointments.index'))
        ->patch(route('counsellor.appointments.no-show', $appointment), [
            'counsellor_notes' => 'Client did not attend the session.',
        ]);

    $response->assertRedirect(route('counsellor.appointments.index'));

    $this->assertDatabaseHas('appointments', [
        'id' => $appointment->id,
        'status' => Appointment::STATUS_NO_SHOW,
        'counsellor_notes' => 'Client did not attend the session.',
        'updated_by' => $counsellorProfile->user_id,
    ]);

    $this->assertDatabaseHas('appointment_status_histories', [
        'appointment_id' => $appointment->id,
        'from_status' => Appointment::STATUS_CONFIRMED,
        'to_status' => Appointment::STATUS_NO_SHOW,
        'reason' => 'Appointment marked as no-show by counsellor.',
        'changed_by' => $counsellorProfile->user_id,
    ]);
});

it('prevents counsellor from completing pending appointment', function (): void {
    $clientProfile = createCounsellorOutcomeClientProfile();
    $counsellorProfile = createCounsellorOutcomeCounsellorProfile();

    $appointment = createCounsellorOutcomeAppointment(
        clientProfile: $clientProfile,
        counsellorProfile: $counsellorProfile,
        status: Appointment::STATUS_PENDING
    );

    $this
        ->actingAs($counsellorProfile->user)
        ->from(route('counsellor.appointments.index'))
        ->patch(route('counsellor.appointments.complete', $appointment), [
            'counsellor_notes' => 'Trying to complete pending appointment.',
        ])
        ->assertRedirect(route('counsellor.appointments.index'))
        ->assertSessionHasErrors('appointment');

    $this->assertDatabaseHas('appointments', [
        'id' => $appointment->id,
        'status' => Appointment::STATUS_PENDING,
    ]);
});

it('prevents counsellor from marking pending appointment as no-show', function (): void {
    $clientProfile = createCounsellorOutcomeClientProfile();
    $counsellorProfile = createCounsellorOutcomeCounsellorProfile();

    $appointment = createCounsellorOutcomeAppointment(
        clientProfile: $clientProfile,
        counsellorProfile: $counsellorProfile,
        status: Appointment::STATUS_PENDING
    );

    $this
        ->actingAs($counsellorProfile->user)
        ->from(route('counsellor.appointments.index'))
        ->patch(route('counsellor.appointments.no-show', $appointment), [
            'counsellor_notes' => 'Trying to mark pending appointment no-show.',
        ])
        ->assertRedirect(route('counsellor.appointments.index'))
        ->assertSessionHasErrors('appointment');

    $this->assertDatabaseHas('appointments', [
        'id' => $appointment->id,
        'status' => Appointment::STATUS_PENDING,
    ]);
});

it('prevents counsellor from closing another counsellors appointment', function (): void {
    $clientProfile = createCounsellorOutcomeClientProfile();
    $firstCounsellorProfile = createCounsellorOutcomeCounsellorProfile('First Outcome Counsellor');
    $secondCounsellorProfile = createCounsellorOutcomeCounsellorProfile('Second Outcome Counsellor');

    $appointment = createCounsellorOutcomeAppointment(
        clientProfile: $clientProfile,
        counsellorProfile: $secondCounsellorProfile,
        status: Appointment::STATUS_CONFIRMED
    );

    $this
        ->actingAs($firstCounsellorProfile->user)
        ->patch(route('counsellor.appointments.complete', $appointment), [
            'counsellor_notes' => 'Trying to close another counsellor appointment.',
        ])
        ->assertNotFound();

    $this->assertDatabaseHas('appointments', [
        'id' => $appointment->id,
        'status' => Appointment::STATUS_CONFIRMED,
    ]);
});

it('prevents completing appointment that is already terminal', function (): void {
    $clientProfile = createCounsellorOutcomeClientProfile();
    $counsellorProfile = createCounsellorOutcomeCounsellorProfile();

    $appointment = createCounsellorOutcomeAppointment(
        clientProfile: $clientProfile,
        counsellorProfile: $counsellorProfile,
        status: Appointment::STATUS_CANCELLED
    );

    $this
        ->actingAs($counsellorProfile->user)
        ->from(route('counsellor.appointments.index'))
        ->patch(route('counsellor.appointments.complete', $appointment), [
            'counsellor_notes' => 'Trying to close cancelled appointment.',
        ])
        ->assertRedirect(route('counsellor.appointments.index'))
        ->assertSessionHasErrors('appointment');

    $this->assertDatabaseHas('appointments', [
        'id' => $appointment->id,
        'status' => Appointment::STATUS_CANCELLED,
    ]);
});

it('requires counsellor role to close appointment outcome', function (): void {
    $clientProfile = createCounsellorOutcomeClientProfile();
    $counsellorProfile = createCounsellorOutcomeCounsellorProfile();

    $appointment = createCounsellorOutcomeAppointment(
        clientProfile: $clientProfile,
        counsellorProfile: $counsellorProfile,
        status: Appointment::STATUS_CONFIRMED
    );

    $admin = User::factory()->create([
        'is_active' => true,
    ]);

    $admin->assignRole('admin');

    $this
        ->actingAs($admin)
        ->patch(route('counsellor.appointments.complete', $appointment), [
            'counsellor_notes' => 'Admin trying counsellor route.',
        ])
        ->assertForbidden();

    $this->assertDatabaseHas('appointments', [
        'id' => $appointment->id,
        'status' => Appointment::STATUS_CONFIRMED,
    ]);
});
