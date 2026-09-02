<?php

use App\Models\Appointment;
use App\Models\ClientProfile;
use App\Models\CounsellorAvailabilityRule;
use App\Models\CounsellorBlockedSlot;
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

function futureDateForAppointmentBookingModule(int $dayOfWeek): string
{
    $date = now()->startOfDay()->addDay();

    while ((int) $date->dayOfWeek !== $dayOfWeek) {
        $date = $date->addDay();
    }

    return $date->toDateString();
}

function createAppointmentBookingClientProfile(): ClientProfile
{
    $user = User::factory()->create([
        'name' => 'Appointment Booking Client',
        'email' => fake()->unique()->safeEmail(),
        'is_active' => true,
    ]);

    $user->assignRole('client');

    return ClientProfile::factory()->create([
        'user_id' => $user->id,
    ]);
}

function createAppointmentBookingCounsellorProfile(
    array $userOverrides = [],
    array $profileOverrides = []
): CounsellorProfile {
    $user = User::factory()->create(array_merge([
        'name' => 'Appointment Booking Counsellor',
        'email' => fake()->unique()->safeEmail(),
        'is_active' => true,
    ], $userOverrides));

    $user->assignRole('counsellor');

    return CounsellorProfile::factory()->create(array_merge([
        'user_id' => $user->id,
        'status' => 'active',
    ], $profileOverrides));
}

function createAppointmentBookingAvailability(
    CounsellorProfile $counsellorProfile,
    int $dayOfWeek,
    string $mode = CounsellorAvailabilityRule::MODE_BOTH
): CounsellorAvailabilityRule {
    return CounsellorAvailabilityRule::factory()
        ->for($counsellorProfile)
        ->create([
            'day_of_week' => $dayOfWeek,
            'start_time' => '09:00',
            'end_time' => '12:00',
            'mode' => $mode,
            'slot_duration_minutes' => 60,
            'buffer_minutes' => 0,
            'capacity_per_slot' => 1,
            'timezone' => 'Asia/Colombo',
            'is_active' => true,
        ]);
}

function createAppointmentBookingPayload(
    CounsellorProfile $counsellorProfile,
    string $date,
    array $overrides = []
): array {
    return array_merge([
        'counsellor_profile_id' => $counsellorProfile->id,
        'counselling_service_id' => null,
        'appointment_date' => $date,
        'start_time' => '09:00',
        'end_time' => '10:00',
        'mode' => Appointment::MODE_ONLINE,
        'client_notes' => 'I would like support with stress management.',
    ], $overrides);
}

function createExistingBookingAppointment(
    ClientProfile $clientProfile,
    CounsellorProfile $counsellorProfile,
    string $date,
    string $startTime = '09:00',
    string $endTime = '10:00',
    string $status = Appointment::STATUS_PENDING
): Appointment {
    return Appointment::query()->create([
        'client_profile_id' => $clientProfile->id,
        'counsellor_profile_id' => $counsellorProfile->id,
        'appointment_date' => $date,
        'start_time' => $startTime,
        'end_time' => $endTime,
        'timezone' => 'Asia/Colombo',
        'mode' => Appointment::MODE_ONLINE,
        'status' => $status,
    ]);
}

it('allows a client to request an available appointment slot', function (): void {
    $clientProfile = createAppointmentBookingClientProfile();
    $counsellorProfile = createAppointmentBookingCounsellorProfile();
    $date = futureDateForAppointmentBookingModule(CounsellorAvailabilityRule::MONDAY);

    createAppointmentBookingAvailability(
        counsellorProfile: $counsellorProfile,
        dayOfWeek: CounsellorAvailabilityRule::MONDAY
    );

    $response = $this
        ->actingAs($clientProfile->user)
        ->post(route('client.appointments.store'), createAppointmentBookingPayload(
            counsellorProfile: $counsellorProfile,
            date: $date
        ));

    $response->assertRedirect(route('client.counsellors.show', $counsellorProfile->id));

    $this->assertDatabaseHas('appointments', [
        'client_profile_id' => $clientProfile->id,
        'counsellor_profile_id' => $counsellorProfile->id,
        'appointment_date' => $date,
        'start_time' => '09:00',
        'end_time' => '10:00',
        'mode' => Appointment::MODE_ONLINE,
        'status' => Appointment::STATUS_PENDING,
    ]);

    $appointment = Appointment::query()->firstOrFail();

    $this->assertDatabaseHas('appointment_status_histories', [
        'appointment_id' => $appointment->id,
        'from_status' => null,
        'to_status' => Appointment::STATUS_PENDING,
        'reason' => 'Appointment requested by client.',
        'changed_by' => $clientProfile->user_id,
    ]);
});

it('rejects booking when selected slot is outside counsellor availability', function (): void {
    $clientProfile = createAppointmentBookingClientProfile();
    $counsellorProfile = createAppointmentBookingCounsellorProfile();
    $date = futureDateForAppointmentBookingModule(CounsellorAvailabilityRule::MONDAY);

    createAppointmentBookingAvailability(
        counsellorProfile: $counsellorProfile,
        dayOfWeek: CounsellorAvailabilityRule::MONDAY
    );

    $response = $this
        ->actingAs($clientProfile->user)
        ->from(route('client.counsellors.show', $counsellorProfile->id))
        ->post(route('client.appointments.store'), createAppointmentBookingPayload(
            counsellorProfile: $counsellorProfile,
            date: $date,
            overrides: [
                'start_time' => '13:00',
                'end_time' => '14:00',
            ]
        ));

    $response
        ->assertRedirect(route('client.counsellors.show', $counsellorProfile->id))
        ->assertSessionHasErrors('start_time');

    $this->assertDatabaseCount('appointments', 0);
});

it('rejects booking when selected slot overlaps counsellor blocked slot', function (): void {
    $clientProfile = createAppointmentBookingClientProfile();
    $counsellorProfile = createAppointmentBookingCounsellorProfile();
    $date = futureDateForAppointmentBookingModule(CounsellorAvailabilityRule::MONDAY);

    createAppointmentBookingAvailability(
        counsellorProfile: $counsellorProfile,
        dayOfWeek: CounsellorAvailabilityRule::MONDAY
    );

    CounsellorBlockedSlot::factory()
        ->for($counsellorProfile)
        ->create([
            'blocked_date' => $date,
            'start_time' => '09:00',
            'end_time' => '10:00',
            'is_full_day' => false,
        ]);

    $response = $this
        ->actingAs($clientProfile->user)
        ->from(route('client.counsellors.show', $counsellorProfile->id))
        ->post(route('client.appointments.store'), createAppointmentBookingPayload(
            counsellorProfile: $counsellorProfile,
            date: $date
        ));

    $response
        ->assertRedirect(route('client.counsellors.show', $counsellorProfile->id))
        ->assertSessionHasErrors('start_time');

    $this->assertDatabaseCount('appointments', 0);
});

it('rejects counsellor double booking', function (): void {
    $firstClientProfile = createAppointmentBookingClientProfile();
    $secondClientProfile = createAppointmentBookingClientProfile();
    $counsellorProfile = createAppointmentBookingCounsellorProfile();
    $date = futureDateForAppointmentBookingModule(CounsellorAvailabilityRule::MONDAY);

    createAppointmentBookingAvailability(
        counsellorProfile: $counsellorProfile,
        dayOfWeek: CounsellorAvailabilityRule::MONDAY
    );

    createExistingBookingAppointment(
        clientProfile: $firstClientProfile,
        counsellorProfile: $counsellorProfile,
        date: $date,
        startTime: '09:00',
        endTime: '10:00',
        status: Appointment::STATUS_CONFIRMED
    );

    $response = $this
        ->actingAs($secondClientProfile->user)
        ->from(route('client.counsellors.show', $counsellorProfile->id))
        ->post(route('client.appointments.store'), createAppointmentBookingPayload(
            counsellorProfile: $counsellorProfile,
            date: $date
        ));

    $response
        ->assertRedirect(route('client.counsellors.show', $counsellorProfile->id))
        ->assertSessionHasErrors('start_time');

    $this->assertDatabaseCount('appointments', 1);
});

it('rejects client double booking with a different counsellor', function (): void {
    $clientProfile = createAppointmentBookingClientProfile();
    $firstCounsellorProfile = createAppointmentBookingCounsellorProfile();
    $secondCounsellorProfile = createAppointmentBookingCounsellorProfile();
    $date = futureDateForAppointmentBookingModule(CounsellorAvailabilityRule::MONDAY);

    createAppointmentBookingAvailability(
        counsellorProfile: $secondCounsellorProfile,
        dayOfWeek: CounsellorAvailabilityRule::MONDAY
    );

    createExistingBookingAppointment(
        clientProfile: $clientProfile,
        counsellorProfile: $firstCounsellorProfile,
        date: $date,
        startTime: '09:00',
        endTime: '10:00',
        status: Appointment::STATUS_PENDING
    );

    $response = $this
        ->actingAs($clientProfile->user)
        ->from(route('client.counsellors.show', $secondCounsellorProfile->id))
        ->post(route('client.appointments.store'), createAppointmentBookingPayload(
            counsellorProfile: $secondCounsellorProfile,
            date: $date
        ));

    $response
        ->assertRedirect(route('client.counsellors.show', $secondCounsellorProfile->id))
        ->assertSessionHasErrors('start_time');

    $this->assertDatabaseCount('appointments', 1);
});

it('rejects booking for archived counsellor', function (): void {
    $clientProfile = createAppointmentBookingClientProfile();
    $counsellorProfile = createAppointmentBookingCounsellorProfile(
        profileOverrides: [
            'status' => 'archived',
        ]
    );

    $date = futureDateForAppointmentBookingModule(CounsellorAvailabilityRule::MONDAY);

    createAppointmentBookingAvailability(
        counsellorProfile: $counsellorProfile,
        dayOfWeek: CounsellorAvailabilityRule::MONDAY
    );

    $response = $this
        ->actingAs($clientProfile->user)
        ->post(route('client.appointments.store'), createAppointmentBookingPayload(
            counsellorProfile: $counsellorProfile,
            date: $date
        ));

    $response->assertSessionHasErrors('counsellor_profile_id');

    $this->assertDatabaseCount('appointments', 0);
});

it('rejects booking for counsellor with inactive user account', function (): void {
    $clientProfile = createAppointmentBookingClientProfile();
    $counsellorProfile = createAppointmentBookingCounsellorProfile(
        userOverrides: [
            'is_active' => false,
        ]
    );

    $date = futureDateForAppointmentBookingModule(CounsellorAvailabilityRule::MONDAY);

    createAppointmentBookingAvailability(
        counsellorProfile: $counsellorProfile,
        dayOfWeek: CounsellorAvailabilityRule::MONDAY
    );

    $response = $this
        ->actingAs($clientProfile->user)
        ->post(route('client.appointments.store'), createAppointmentBookingPayload(
            counsellorProfile: $counsellorProfile,
            date: $date
        ));

    $response->assertSessionHasErrors('counsellor_profile_id');

    $this->assertDatabaseCount('appointments', 0);
});

it('requires authenticated client role to book appointment', function (): void {
    $counsellorProfile = createAppointmentBookingCounsellorProfile();
    $date = futureDateForAppointmentBookingModule(CounsellorAvailabilityRule::MONDAY);

    createAppointmentBookingAvailability(
        counsellorProfile: $counsellorProfile,
        dayOfWeek: CounsellorAvailabilityRule::MONDAY
    );

    $admin = User::factory()->create([
        'is_active' => true,
    ]);

    $admin->assignRole('admin');

    $this
        ->actingAs($admin)
        ->post(route('client.appointments.store'), createAppointmentBookingPayload(
            counsellorProfile: $counsellorProfile,
            date: $date
        ))
        ->assertForbidden();

    $this->assertDatabaseCount('appointments', 0);
});
