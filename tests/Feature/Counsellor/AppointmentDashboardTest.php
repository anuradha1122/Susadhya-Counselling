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

function createCounsellorAppointmentDashboardClientProfile(string $name = 'Dashboard Client'): ClientProfile
{
    $user = User::factory()->create([
        'name' => $name,
        'email' => fake()->unique()->safeEmail(),
        'phone' => '+94 77 111 2222',
        'is_active' => true,
    ]);

    $user->assignRole('client');

    return ClientProfile::factory()->create([
        'user_id' => $user->id,
    ]);
}

function createCounsellorAppointmentDashboardCounsellorProfile(string $name = 'Dashboard Counsellor'): CounsellorProfile
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

function createCounsellorDashboardAppointment(
    ClientProfile $clientProfile,
    CounsellorProfile $counsellorProfile,
    array $overrides = []
): Appointment {
    return Appointment::query()->create(array_merge([
        'client_profile_id' => $clientProfile->id,
        'counsellor_profile_id' => $counsellorProfile->id,
        'appointment_date' => now()->addDays(3)->toDateString(),
        'start_time' => '09:00',
        'end_time' => '10:00',
        'timezone' => 'Asia/Colombo',
        'mode' => Appointment::MODE_ONLINE,
        'status' => Appointment::STATUS_PENDING,
        'client_notes' => 'Need support with stress management.',
    ], $overrides));
}

it('allows a counsellor to view their appointment dashboard', function (): void {
    $clientProfile = createCounsellorAppointmentDashboardClientProfile();
    $counsellorProfile = createCounsellorAppointmentDashboardCounsellorProfile();

    createCounsellorDashboardAppointment(
        clientProfile: $clientProfile,
        counsellorProfile: $counsellorProfile,
    );

    $response = $this
        ->actingAs($counsellorProfile->user)
        ->get(route('counsellor.appointments.index'));

    $response->assertOk();

    $appointments = $response->viewData('page')['props']['appointments']['data'];

    expect($appointments)
        ->toHaveCount(1)
        ->and($appointments[0]['client']['name'])->toBe($clientProfile->user->name)
        ->and($appointments[0]['status'])->toBe(Appointment::STATUS_PENDING)
        ->and($appointments[0]['can_be_confirmed'])->toBeTrue();
});

it('shows only appointments belonging to the authenticated counsellor', function (): void {
    $clientProfile = createCounsellorAppointmentDashboardClientProfile();
    $firstCounsellorProfile = createCounsellorAppointmentDashboardCounsellorProfile('First Counsellor');
    $secondCounsellorProfile = createCounsellorAppointmentDashboardCounsellorProfile('Second Counsellor');

    createCounsellorDashboardAppointment(
        clientProfile: $clientProfile,
        counsellorProfile: $firstCounsellorProfile,
        overrides: [
            'client_notes' => 'Visible counsellor appointment.',
        ],
    );

    createCounsellorDashboardAppointment(
        clientProfile: $clientProfile,
        counsellorProfile: $secondCounsellorProfile,
        overrides: [
            'client_notes' => 'Hidden counsellor appointment.',
            'start_time' => '10:00',
            'end_time' => '11:00',
        ],
    );

    $response = $this
        ->actingAs($firstCounsellorProfile->user)
        ->get(route('counsellor.appointments.index'));

    $response->assertOk();

    $appointments = $response->viewData('page')['props']['appointments']['data'];

    expect($appointments)
        ->toHaveCount(1)
        ->and($appointments[0]['client_notes'])->toBe('Visible counsellor appointment.');
});

it('filters counsellor appointments by status', function (): void {
    $clientProfile = createCounsellorAppointmentDashboardClientProfile();
    $counsellorProfile = createCounsellorAppointmentDashboardCounsellorProfile();

    createCounsellorDashboardAppointment(
        clientProfile: $clientProfile,
        counsellorProfile: $counsellorProfile,
        overrides: [
            'status' => Appointment::STATUS_PENDING,
        ],
    );

    createCounsellorDashboardAppointment(
        clientProfile: $clientProfile,
        counsellorProfile: $counsellorProfile,
        overrides: [
            'status' => Appointment::STATUS_CONFIRMED,
            'start_time' => '10:00',
            'end_time' => '11:00',
        ],
    );

    $response = $this
        ->actingAs($counsellorProfile->user)
        ->get(route('counsellor.appointments.index', [
            'status' => Appointment::STATUS_CONFIRMED,
        ]));

    $response->assertOk();

    $appointments = $response->viewData('page')['props']['appointments']['data'];

    expect($appointments)
        ->toHaveCount(1)
        ->and($appointments[0]['status'])->toBe(Appointment::STATUS_CONFIRMED)
        ->and($appointments[0]['can_be_confirmed'])->toBeFalse();
});

it('filters counsellor appointments by upcoming and past periods', function (): void {
    $clientProfile = createCounsellorAppointmentDashboardClientProfile();
    $counsellorProfile = createCounsellorAppointmentDashboardCounsellorProfile();

    createCounsellorDashboardAppointment(
        clientProfile: $clientProfile,
        counsellorProfile: $counsellorProfile,
        overrides: [
            'appointment_date' => now()->addDays(2)->toDateString(),
            'client_notes' => 'Upcoming counsellor appointment.',
        ],
    );

    createCounsellorDashboardAppointment(
        clientProfile: $clientProfile,
        counsellorProfile: $counsellorProfile,
        overrides: [
            'appointment_date' => now()->subDays(2)->toDateString(),
            'client_notes' => 'Past counsellor appointment.',
        ],
    );

    $upcomingResponse = $this
        ->actingAs($counsellorProfile->user)
        ->get(route('counsellor.appointments.index', [
            'period' => 'upcoming',
        ]));

    $upcomingAppointments = $upcomingResponse->viewData('page')['props']['appointments']['data'];

    expect($upcomingAppointments)
        ->toHaveCount(1)
        ->and($upcomingAppointments[0]['client_notes'])->toBe('Upcoming counsellor appointment.');

    $pastResponse = $this
        ->actingAs($counsellorProfile->user)
        ->get(route('counsellor.appointments.index', [
            'period' => 'past',
        ]));

    $pastAppointments = $pastResponse->viewData('page')['props']['appointments']['data'];

    expect($pastAppointments)
        ->toHaveCount(1)
        ->and($pastAppointments[0]['client_notes'])->toBe('Past counsellor appointment.');
});

it('allows counsellor to confirm their pending online appointment', function (): void {
    $clientProfile = createCounsellorAppointmentDashboardClientProfile();
    $counsellorProfile = createCounsellorAppointmentDashboardCounsellorProfile();

    $appointment = createCounsellorDashboardAppointment(
        clientProfile: $clientProfile,
        counsellorProfile: $counsellorProfile,
        overrides: [
            'mode' => Appointment::MODE_ONLINE,
            'status' => Appointment::STATUS_PENDING,
        ],
    );

    $response = $this
        ->actingAs($counsellorProfile->user)
        ->from(route('counsellor.appointments.index'))
        ->patch(route('counsellor.appointments.confirm', $appointment), [
            'meeting_link' => 'https://meet.google.com/test-room',
            'location' => null,
            'counsellor_notes' => 'Please join five minutes early.',
        ]);

    $response->assertRedirect(route('counsellor.appointments.index'));

    $this->assertDatabaseHas('appointments', [
        'id' => $appointment->id,
        'status' => Appointment::STATUS_CONFIRMED,
        'meeting_link' => 'https://meet.google.com/test-room',
        'location' => null,
        'counsellor_notes' => 'Please join five minutes early.',
        'updated_by' => $counsellorProfile->user_id,
    ]);

    $this->assertDatabaseHas('appointment_status_histories', [
        'appointment_id' => $appointment->id,
        'from_status' => Appointment::STATUS_PENDING,
        'to_status' => Appointment::STATUS_CONFIRMED,
        'reason' => 'Appointment confirmed by counsellor.',
        'changed_by' => $counsellorProfile->user_id,
    ]);

    expect($appointment->refresh()->reminder_scheduled_at)->not->toBeNull();
});

it('allows counsellor to confirm their pending in-person appointment', function (): void {
    $clientProfile = createCounsellorAppointmentDashboardClientProfile();
    $counsellorProfile = createCounsellorAppointmentDashboardCounsellorProfile();

    $appointment = createCounsellorDashboardAppointment(
        clientProfile: $clientProfile,
        counsellorProfile: $counsellorProfile,
        overrides: [
            'mode' => Appointment::MODE_IN_PERSON,
            'status' => Appointment::STATUS_PENDING,
        ],
    );

    $response = $this
        ->actingAs($counsellorProfile->user)
        ->patch(route('counsellor.appointments.confirm', $appointment), [
            'meeting_link' => null,
            'location' => 'Room 02, Main Counselling Centre',
            'counsellor_notes' => 'Bring previous reports if available.',
        ]);

    $response->assertRedirect(route('counsellor.appointments.index'));

    $this->assertDatabaseHas('appointments', [
        'id' => $appointment->id,
        'status' => Appointment::STATUS_CONFIRMED,
        'location' => 'Room 02, Main Counselling Centre',
        'counsellor_notes' => 'Bring previous reports if available.',
    ]);
});

it('requires meeting link when confirming online appointment', function (): void {
    $clientProfile = createCounsellorAppointmentDashboardClientProfile();
    $counsellorProfile = createCounsellorAppointmentDashboardCounsellorProfile();

    $appointment = createCounsellorDashboardAppointment(
        clientProfile: $clientProfile,
        counsellorProfile: $counsellorProfile,
        overrides: [
            'mode' => Appointment::MODE_ONLINE,
            'status' => Appointment::STATUS_PENDING,
        ],
    );

    $this
        ->actingAs($counsellorProfile->user)
        ->from(route('counsellor.appointments.index'))
        ->patch(route('counsellor.appointments.confirm', $appointment), [
            'meeting_link' => null,
            'location' => null,
        ])
        ->assertRedirect(route('counsellor.appointments.index'))
        ->assertSessionHasErrors('meeting_link');

    $this->assertDatabaseHas('appointments', [
        'id' => $appointment->id,
        'status' => Appointment::STATUS_PENDING,
    ]);
});

it('requires location when confirming in-person appointment', function (): void {
    $clientProfile = createCounsellorAppointmentDashboardClientProfile();
    $counsellorProfile = createCounsellorAppointmentDashboardCounsellorProfile();

    $appointment = createCounsellorDashboardAppointment(
        clientProfile: $clientProfile,
        counsellorProfile: $counsellorProfile,
        overrides: [
            'mode' => Appointment::MODE_IN_PERSON,
            'status' => Appointment::STATUS_PENDING,
        ],
    );

    $this
        ->actingAs($counsellorProfile->user)
        ->from(route('counsellor.appointments.index'))
        ->patch(route('counsellor.appointments.confirm', $appointment), [
            'meeting_link' => null,
            'location' => null,
        ])
        ->assertRedirect(route('counsellor.appointments.index'))
        ->assertSessionHasErrors('location');

    $this->assertDatabaseHas('appointments', [
        'id' => $appointment->id,
        'status' => Appointment::STATUS_PENDING,
    ]);
});

it('prevents counsellor from confirming another counsellors appointment', function (): void {
    $clientProfile = createCounsellorAppointmentDashboardClientProfile();
    $firstCounsellorProfile = createCounsellorAppointmentDashboardCounsellorProfile('First Counsellor');
    $secondCounsellorProfile = createCounsellorAppointmentDashboardCounsellorProfile('Second Counsellor');

    $appointment = createCounsellorDashboardAppointment(
        clientProfile: $clientProfile,
        counsellorProfile: $secondCounsellorProfile,
        overrides: [
            'status' => Appointment::STATUS_PENDING,
        ],
    );

    $this
        ->actingAs($firstCounsellorProfile->user)
        ->patch(route('counsellor.appointments.confirm', $appointment), [
            'meeting_link' => 'https://meet.google.com/wrong-room',
        ])
        ->assertNotFound();

    $this->assertDatabaseHas('appointments', [
        'id' => $appointment->id,
        'status' => Appointment::STATUS_PENDING,
        'meeting_link' => null,
    ]);
});

it('prevents confirming appointments that are not pending', function (): void {
    $clientProfile = createCounsellorAppointmentDashboardClientProfile();
    $counsellorProfile = createCounsellorAppointmentDashboardCounsellorProfile();

    $appointment = createCounsellorDashboardAppointment(
        clientProfile: $clientProfile,
        counsellorProfile: $counsellorProfile,
        overrides: [
            'status' => Appointment::STATUS_CANCELLED,
        ],
    );

    $this
        ->actingAs($counsellorProfile->user)
        ->from(route('counsellor.appointments.index'))
        ->patch(route('counsellor.appointments.confirm', $appointment), [
            'meeting_link' => 'https://meet.google.com/not-allowed',
        ])
        ->assertRedirect(route('counsellor.appointments.index'))
        ->assertSessionHasErrors('appointment');

    $this->assertDatabaseHas('appointments', [
        'id' => $appointment->id,
        'status' => Appointment::STATUS_CANCELLED,
        'meeting_link' => null,
    ]);
});

it('requires counsellor role to view counsellor appointment dashboard', function (): void {
    $admin = User::factory()->create([
        'is_active' => true,
    ]);

    $admin->assignRole('admin');

    $this
        ->actingAs($admin)
        ->get(route('counsellor.appointments.index'))
        ->assertForbidden();
});
