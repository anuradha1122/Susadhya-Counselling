<?php

use App\Models\Appointment;
use App\Models\ClientProfile;
use App\Models\CounsellorAvailabilityRule;
use App\Models\CounsellorProfile;
use App\Models\User;
use Carbon\CarbonImmutable;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    app(PermissionRegistrar::class)->forgetCachedPermissions();

    $this->seed(RolePermissionSeeder::class);
});

function futureDateForAppointmentReschedulingModule(?int $dayOfWeek = null): string
{
    $date = CarbonImmutable::now()->addDay();

    if ($dayOfWeek === null) {
        return $date->toDateString();
    }

    while ((int) $date->dayOfWeek !== $dayOfWeek) {
        $date = $date->addDay();
    }

    return $date->toDateString();
}

function createAppointmentReschedulingClientProfile(string $name = 'Reschedule Client'): ClientProfile
{
    $user = User::factory()->create([
        'name' => $name,
        'email' => fake()->unique()->safeEmail(),
        'phone' => '+94 77 111 3333',
        'is_active' => true,
    ]);

    $user->assignRole('client');

    return ClientProfile::factory()->create([
        'user_id' => $user->id,
    ]);
}

function createAppointmentReschedulingCounsellorProfile(string $name = 'Reschedule Counsellor'): CounsellorProfile
{
    $user = User::factory()->create([
        'name' => $name,
        'email' => fake()->unique()->safeEmail(),
        'phone' => '+94 77 222 4444',
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

function createAppointmentReschedulingAvailability(
    CounsellorProfile $counsellorProfile,
    string $date
): CounsellorAvailabilityRule {
    return CounsellorAvailabilityRule::factory()
        ->for($counsellorProfile)
        ->create([
            'day_of_week' => CarbonImmutable::parse($date)->dayOfWeek,
            'start_time' => '09:00',
            'end_time' => '12:00',
            'mode' => CounsellorAvailabilityRule::MODE_BOTH,
            'slot_duration_minutes' => 60,
            'buffer_minutes' => 0,
            'capacity_per_slot' => 1,
            'timezone' => 'Asia/Colombo',
            'effective_from' => CarbonImmutable::parse($date)->subDay()->toDateString(),
            'effective_until' => CarbonImmutable::parse($date)->addMonth()->toDateString(),
            'is_active' => true,
        ]);
}

function createAppointmentReschedulingAppointment(
    ClientProfile $clientProfile,
    CounsellorProfile $counsellorProfile,
    array $overrides = []
): Appointment {
    return Appointment::query()->create(array_merge([
        'client_profile_id' => $clientProfile->id,
        'counsellor_profile_id' => $counsellorProfile->id,
        'appointment_date' => futureDateForAppointmentReschedulingModule(),
        'start_time' => '09:00',
        'end_time' => '10:00',
        'timezone' => 'Asia/Colombo',
        'mode' => Appointment::MODE_ONLINE,
        'status' => Appointment::STATUS_PENDING,
        'client_notes' => 'Original appointment note.',
    ], $overrides));
}

it('allows a client to reschedule their pending appointment', function (): void {
    $date = futureDateForAppointmentReschedulingModule();
    $clientProfile = createAppointmentReschedulingClientProfile();
    $counsellorProfile = createAppointmentReschedulingCounsellorProfile();

    createAppointmentReschedulingAvailability($counsellorProfile, $date);

    $appointment = createAppointmentReschedulingAppointment(
        clientProfile: $clientProfile,
        counsellorProfile: $counsellorProfile,
        overrides: [
            'appointment_date' => $date,
            'status' => Appointment::STATUS_PENDING,
        ]
    );

    $response = $this
        ->actingAs($clientProfile->user)
        ->from(route('client.appointments.index'))
        ->patch(route('client.appointments.reschedule', $appointment), [
            'appointment_date' => $date,
            'start_time' => '10:00',
            'end_time' => '11:00',
            'mode' => Appointment::MODE_ONLINE,
            'client_notes' => 'Please move this appointment.',
        ]);

    $response->assertRedirect(route('client.appointments.index'));

    $this->assertDatabaseHas('appointments', [
        'id' => $appointment->id,
        'status' => Appointment::STATUS_RESCHEDULED,
        'updated_by' => $clientProfile->user_id,
    ]);

    $this->assertDatabaseHas('appointments', [
        'client_profile_id' => $clientProfile->id,
        'counsellor_profile_id' => $counsellorProfile->id,
        'rescheduled_from_appointment_id' => $appointment->id,
        'appointment_date' => $date.' 00:00:00',
        'start_time' => '10:00',
        'end_time' => '11:00',
        'mode' => Appointment::MODE_ONLINE,
        'status' => Appointment::STATUS_PENDING,
        'client_notes' => 'Please move this appointment.',
    ]);

    $this->assertDatabaseHas('appointment_status_histories', [
        'appointment_id' => $appointment->id,
        'from_status' => Appointment::STATUS_PENDING,
        'to_status' => Appointment::STATUS_RESCHEDULED,
        'reason' => 'Appointment rescheduled by client.',
        'changed_by' => $clientProfile->user_id,
    ]);

    $newAppointment = Appointment::query()
        ->where('rescheduled_from_appointment_id', $appointment->id)
        ->firstOrFail();

    $this->assertDatabaseHas('appointment_status_histories', [
        'appointment_id' => $newAppointment->id,
        'from_status' => null,
        'to_status' => Appointment::STATUS_PENDING,
        'reason' => 'Rescheduled appointment requested by client.',
        'changed_by' => $clientProfile->user_id,
    ]);
});

it('allows a client to reschedule their confirmed appointment', function (): void {
    $date = futureDateForAppointmentReschedulingModule();
    $clientProfile = createAppointmentReschedulingClientProfile();
    $counsellorProfile = createAppointmentReschedulingCounsellorProfile();

    createAppointmentReschedulingAvailability($counsellorProfile, $date);

    $appointment = createAppointmentReschedulingAppointment(
        clientProfile: $clientProfile,
        counsellorProfile: $counsellorProfile,
        overrides: [
            'appointment_date' => $date,
            'status' => Appointment::STATUS_CONFIRMED,
            'meeting_link' => 'https://meet.google.com/original-room',
        ]
    );

    $this
        ->actingAs($clientProfile->user)
        ->patch(route('client.appointments.reschedule', $appointment), [
            'appointment_date' => $date,
            'start_time' => '11:00',
            'end_time' => '12:00',
            'mode' => Appointment::MODE_ONLINE,
            'client_notes' => null,
        ])
        ->assertRedirect(route('client.appointments.index'));

    $this->assertDatabaseHas('appointments', [
        'id' => $appointment->id,
        'status' => Appointment::STATUS_RESCHEDULED,
    ]);

    $this->assertDatabaseHas('appointments', [
        'rescheduled_from_appointment_id' => $appointment->id,
        'status' => Appointment::STATUS_PENDING,
        'start_time' => '11:00',
        'end_time' => '12:00',
        'client_notes' => 'Original appointment note.',
    ]);
});

it('loads available slots for appointment rescheduling while ignoring the current appointment', function (): void {
    $date = futureDateForAppointmentReschedulingModule();
    $clientProfile = createAppointmentReschedulingClientProfile();
    $counsellorProfile = createAppointmentReschedulingCounsellorProfile();

    createAppointmentReschedulingAvailability($counsellorProfile, $date);

    $appointment = createAppointmentReschedulingAppointment(
        clientProfile: $clientProfile,
        counsellorProfile: $counsellorProfile,
        overrides: [
            'appointment_date' => $date,
            'start_time' => '09:00',
            'end_time' => '10:00',
            'status' => Appointment::STATUS_PENDING,
        ]
    );

    $response = $this
        ->actingAs($clientProfile->user)
        ->getJson(route('client.appointments.reschedule-slots', [
            'appointment' => $appointment,
            'appointment_date' => $date,
            'mode' => Appointment::MODE_ONLINE,
        ]));

    $response->assertOk();

    $slots = $response->json('slots');

    expect($slots)
        ->toHaveCount(3)
        ->and(collect($slots)->pluck('start_time')->all())
        ->toContain('09:00', '10:00', '11:00');
});

it('prevents a client from rescheduling another clients appointment', function (): void {
    $date = futureDateForAppointmentReschedulingModule();

    $firstClientProfile = createAppointmentReschedulingClientProfile('First Reschedule Client');
    $secondClientProfile = createAppointmentReschedulingClientProfile('Second Reschedule Client');
    $counsellorProfile = createAppointmentReschedulingCounsellorProfile();

    createAppointmentReschedulingAvailability($counsellorProfile, $date);

    $appointment = createAppointmentReschedulingAppointment(
        clientProfile: $secondClientProfile,
        counsellorProfile: $counsellorProfile,
        overrides: [
            'appointment_date' => $date,
        ]
    );

    $this
        ->actingAs($firstClientProfile->user)
        ->patch(route('client.appointments.reschedule', $appointment), [
            'appointment_date' => $date,
            'start_time' => '10:00',
            'end_time' => '11:00',
            'mode' => Appointment::MODE_ONLINE,
        ])
        ->assertNotFound();

    $this->assertDatabaseHas('appointments', [
        'id' => $appointment->id,
        'status' => Appointment::STATUS_PENDING,
    ]);

    $this->assertDatabaseMissing('appointments', [
        'rescheduled_from_appointment_id' => $appointment->id,
    ]);
});

it('prevents rescheduling completed appointments', function (): void {
    $date = futureDateForAppointmentReschedulingModule();
    $clientProfile = createAppointmentReschedulingClientProfile();
    $counsellorProfile = createAppointmentReschedulingCounsellorProfile();

    createAppointmentReschedulingAvailability($counsellorProfile, $date);

    $appointment = createAppointmentReschedulingAppointment(
        clientProfile: $clientProfile,
        counsellorProfile: $counsellorProfile,
        overrides: [
            'appointment_date' => $date,
            'status' => Appointment::STATUS_COMPLETED,
        ]
    );

    $this
        ->actingAs($clientProfile->user)
        ->from(route('client.appointments.index'))
        ->patch(route('client.appointments.reschedule', $appointment), [
            'appointment_date' => $date,
            'start_time' => '10:00',
            'end_time' => '11:00',
            'mode' => Appointment::MODE_ONLINE,
        ])
        ->assertRedirect(route('client.appointments.index'))
        ->assertSessionHasErrors('appointment');

    $this->assertDatabaseHas('appointments', [
        'id' => $appointment->id,
        'status' => Appointment::STATUS_COMPLETED,
    ]);
});

it('prevents rescheduling to a slot outside counsellor availability', function (): void {
    $date = futureDateForAppointmentReschedulingModule();
    $clientProfile = createAppointmentReschedulingClientProfile();
    $counsellorProfile = createAppointmentReschedulingCounsellorProfile();

    createAppointmentReschedulingAvailability($counsellorProfile, $date);

    $appointment = createAppointmentReschedulingAppointment(
        clientProfile: $clientProfile,
        counsellorProfile: $counsellorProfile,
        overrides: [
            'appointment_date' => $date,
        ]
    );

    $this
        ->actingAs($clientProfile->user)
        ->from(route('client.appointments.index'))
        ->patch(route('client.appointments.reschedule', $appointment), [
            'appointment_date' => $date,
            'start_time' => '14:00',
            'end_time' => '15:00',
            'mode' => Appointment::MODE_ONLINE,
        ])
        ->assertRedirect(route('client.appointments.index'))
        ->assertSessionHasErrors('start_time');

    $this->assertDatabaseHas('appointments', [
        'id' => $appointment->id,
        'status' => Appointment::STATUS_PENDING,
    ]);

    $this->assertDatabaseMissing('appointments', [
        'rescheduled_from_appointment_id' => $appointment->id,
    ]);
});

it('prevents rescheduling to a counsellor double-booked slot', function (): void {
    $date = futureDateForAppointmentReschedulingModule();

    $clientProfile = createAppointmentReschedulingClientProfile();
    $otherClientProfile = createAppointmentReschedulingClientProfile('Other Client');
    $counsellorProfile = createAppointmentReschedulingCounsellorProfile();

    createAppointmentReschedulingAvailability($counsellorProfile, $date);

    $appointment = createAppointmentReschedulingAppointment(
        clientProfile: $clientProfile,
        counsellorProfile: $counsellorProfile,
        overrides: [
            'appointment_date' => $date,
            'start_time' => '09:00',
            'end_time' => '10:00',
        ]
    );

    createAppointmentReschedulingAppointment(
        clientProfile: $otherClientProfile,
        counsellorProfile: $counsellorProfile,
        overrides: [
            'appointment_date' => $date,
            'start_time' => '10:00',
            'end_time' => '11:00',
            'status' => Appointment::STATUS_CONFIRMED,
        ]
    );

    $this
        ->actingAs($clientProfile->user)
        ->from(route('client.appointments.index'))
        ->patch(route('client.appointments.reschedule', $appointment), [
            'appointment_date' => $date,
            'start_time' => '10:00',
            'end_time' => '11:00',
            'mode' => Appointment::MODE_ONLINE,
        ])
        ->assertRedirect(route('client.appointments.index'))
        ->assertSessionHasErrors('start_time');

    $this->assertDatabaseHas('appointments', [
        'id' => $appointment->id,
        'status' => Appointment::STATUS_PENDING,
    ]);
});

it('prevents rescheduling to a client double-booked slot', function (): void {
    $date = futureDateForAppointmentReschedulingModule();

    $clientProfile = createAppointmentReschedulingClientProfile();
    $firstCounsellorProfile = createAppointmentReschedulingCounsellorProfile('First Reschedule Counsellor');
    $secondCounsellorProfile = createAppointmentReschedulingCounsellorProfile('Second Reschedule Counsellor');

    createAppointmentReschedulingAvailability($firstCounsellorProfile, $date);
    createAppointmentReschedulingAvailability($secondCounsellorProfile, $date);

    $appointment = createAppointmentReschedulingAppointment(
        clientProfile: $clientProfile,
        counsellorProfile: $firstCounsellorProfile,
        overrides: [
            'appointment_date' => $date,
            'start_time' => '09:00',
            'end_time' => '10:00',
        ]
    );

    createAppointmentReschedulingAppointment(
        clientProfile: $clientProfile,
        counsellorProfile: $secondCounsellorProfile,
        overrides: [
            'appointment_date' => $date,
            'start_time' => '10:00',
            'end_time' => '11:00',
            'status' => Appointment::STATUS_CONFIRMED,
        ]
    );

    $this
        ->actingAs($clientProfile->user)
        ->from(route('client.appointments.index'))
        ->patch(route('client.appointments.reschedule', $appointment), [
            'appointment_date' => $date,
            'start_time' => '10:00',
            'end_time' => '11:00',
            'mode' => Appointment::MODE_ONLINE,
        ])
        ->assertRedirect(route('client.appointments.index'))
        ->assertSessionHasErrors('start_time');

    $this->assertDatabaseHas('appointments', [
        'id' => $appointment->id,
        'status' => Appointment::STATUS_PENDING,
    ]);
});

it('requires authenticated client role to reschedule appointment', function (): void {
    $date = futureDateForAppointmentReschedulingModule();

    $clientProfile = createAppointmentReschedulingClientProfile();
    $counsellorProfile = createAppointmentReschedulingCounsellorProfile();

    createAppointmentReschedulingAvailability($counsellorProfile, $date);

    $appointment = createAppointmentReschedulingAppointment(
        clientProfile: $clientProfile,
        counsellorProfile: $counsellorProfile,
        overrides: [
            'appointment_date' => $date,
        ]
    );

    $admin = User::factory()->create([
        'is_active' => true,
    ]);

    $admin->assignRole('admin');

    $this
        ->actingAs($admin)
        ->patch(route('client.appointments.reschedule', $appointment), [
            'appointment_date' => $date,
            'start_time' => '10:00',
            'end_time' => '11:00',
            'mode' => Appointment::MODE_ONLINE,
        ])
        ->assertForbidden();

    $this->assertDatabaseHas('appointments', [
        'id' => $appointment->id,
        'status' => Appointment::STATUS_PENDING,
    ]);
});
