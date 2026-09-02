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

function createAdminAppointmentOversightAdminUser(): User
{
    $user = User::factory()->create([
        'name' => 'Appointment Admin',
        'email' => fake()->unique()->safeEmail(),
        'is_active' => true,
    ]);

    $user->assignRole('admin');

    return $user;
}

function createAdminAppointmentOversightClientProfile(string $name = 'Oversight Client'): ClientProfile
{
    $user = User::factory()->create([
        'name' => $name,
        'email' => fake()->unique()->safeEmail(),
        'phone' => '+94 77 555 6666',
        'is_active' => true,
    ]);

    $user->assignRole('client');

    return ClientProfile::factory()->create([
        'user_id' => $user->id,
    ]);
}

function createAdminAppointmentOversightCounsellorProfile(string $name = 'Oversight Counsellor'): CounsellorProfile
{
    $user = User::factory()->create([
        'name' => $name,
        'email' => fake()->unique()->safeEmail(),
        'phone' => '+94 77 777 8888',
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

function createAdminAppointmentOversightAppointment(
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
        'client_notes' => 'Admin oversight appointment.',
    ], $overrides));
}

it('allows admin to view appointment oversight page', function (): void {
    $admin = createAdminAppointmentOversightAdminUser();
    $clientProfile = createAdminAppointmentOversightClientProfile();
    $counsellorProfile = createAdminAppointmentOversightCounsellorProfile();

    createAdminAppointmentOversightAppointment(
        clientProfile: $clientProfile,
        counsellorProfile: $counsellorProfile
    );

    $response = $this
        ->actingAs($admin)
        ->get(route('admin.appointments.index'));

    $response->assertOk();

    $appointments = $response->viewData('page')['props']['appointments']['data'];

    expect($appointments)
        ->toHaveCount(1)
        ->and($appointments[0]['client']['name'])->toBe($clientProfile->user->name)
        ->and($appointments[0]['counsellor']['name'])->toBe($counsellorProfile->user->name)
        ->and($appointments[0]['status'])->toBe(Appointment::STATUS_PENDING);
});

it('shows appointments across different clients and counsellors', function (): void {
    $admin = createAdminAppointmentOversightAdminUser();

    $firstClientProfile = createAdminAppointmentOversightClientProfile('First Admin Client');
    $secondClientProfile = createAdminAppointmentOversightClientProfile('Second Admin Client');

    $firstCounsellorProfile = createAdminAppointmentOversightCounsellorProfile('First Admin Counsellor');
    $secondCounsellorProfile = createAdminAppointmentOversightCounsellorProfile('Second Admin Counsellor');

    createAdminAppointmentOversightAppointment(
        clientProfile: $firstClientProfile,
        counsellorProfile: $firstCounsellorProfile
    );

    createAdminAppointmentOversightAppointment(
        clientProfile: $secondClientProfile,
        counsellorProfile: $secondCounsellorProfile,
        overrides: [
            'start_time' => '10:00',
            'end_time' => '11:00',
        ]
    );

    $response = $this
        ->actingAs($admin)
        ->get(route('admin.appointments.index'));

    $appointments = $response->viewData('page')['props']['appointments']['data'];

    expect($appointments)->toHaveCount(2);
});

it('filters admin appointments by status', function (): void {
    $admin = createAdminAppointmentOversightAdminUser();
    $clientProfile = createAdminAppointmentOversightClientProfile();
    $counsellorProfile = createAdminAppointmentOversightCounsellorProfile();

    createAdminAppointmentOversightAppointment(
        clientProfile: $clientProfile,
        counsellorProfile: $counsellorProfile,
        overrides: [
            'status' => Appointment::STATUS_PENDING,
        ]
    );

    createAdminAppointmentOversightAppointment(
        clientProfile: $clientProfile,
        counsellorProfile: $counsellorProfile,
        overrides: [
            'status' => Appointment::STATUS_CONFIRMED,
            'start_time' => '10:00',
            'end_time' => '11:00',
        ]
    );

    $response = $this
        ->actingAs($admin)
        ->get(route('admin.appointments.index', [
            'status' => Appointment::STATUS_CONFIRMED,
        ]));

    $appointments = $response->viewData('page')['props']['appointments']['data'];

    expect($appointments)
        ->toHaveCount(1)
        ->and($appointments[0]['status'])->toBe(Appointment::STATUS_CONFIRMED);
});

it('filters admin appointments by mode', function (): void {
    $admin = createAdminAppointmentOversightAdminUser();
    $clientProfile = createAdminAppointmentOversightClientProfile();
    $counsellorProfile = createAdminAppointmentOversightCounsellorProfile();

    createAdminAppointmentOversightAppointment(
        clientProfile: $clientProfile,
        counsellorProfile: $counsellorProfile,
        overrides: [
            'mode' => Appointment::MODE_ONLINE,
        ]
    );

    createAdminAppointmentOversightAppointment(
        clientProfile: $clientProfile,
        counsellorProfile: $counsellorProfile,
        overrides: [
            'mode' => Appointment::MODE_IN_PERSON,
            'start_time' => '10:00',
            'end_time' => '11:00',
        ]
    );

    $response = $this
        ->actingAs($admin)
        ->get(route('admin.appointments.index', [
            'mode' => Appointment::MODE_IN_PERSON,
        ]));

    $appointments = $response->viewData('page')['props']['appointments']['data'];

    expect($appointments)
        ->toHaveCount(1)
        ->and($appointments[0]['mode'])->toBe(Appointment::MODE_IN_PERSON);
});

it('filters admin appointments by client or counsellor search', function (): void {
    $admin = createAdminAppointmentOversightAdminUser();

    $searchClientProfile = createAdminAppointmentOversightClientProfile('Searchable Client');
    $otherClientProfile = createAdminAppointmentOversightClientProfile('Hidden Client');

    $searchCounsellorProfile = createAdminAppointmentOversightCounsellorProfile('Searchable Counsellor');
    $otherCounsellorProfile = createAdminAppointmentOversightCounsellorProfile('Hidden Counsellor');

    createAdminAppointmentOversightAppointment(
        clientProfile: $searchClientProfile,
        counsellorProfile: $searchCounsellorProfile
    );

    createAdminAppointmentOversightAppointment(
        clientProfile: $otherClientProfile,
        counsellorProfile: $otherCounsellorProfile,
        overrides: [
            'start_time' => '10:00',
            'end_time' => '11:00',
        ]
    );

    $response = $this
        ->actingAs($admin)
        ->get(route('admin.appointments.index', [
            'search' => 'Searchable Client',
        ]));

    $appointments = $response->viewData('page')['props']['appointments']['data'];

    expect($appointments)
        ->toHaveCount(1)
        ->and($appointments[0]['client']['name'])->toBe('Searchable Client');
});

it('allows admin to confirm an online appointment with meeting link', function (): void {
    $admin = createAdminAppointmentOversightAdminUser();
    $clientProfile = createAdminAppointmentOversightClientProfile();
    $counsellorProfile = createAdminAppointmentOversightCounsellorProfile();

    $appointment = createAdminAppointmentOversightAppointment(
        clientProfile: $clientProfile,
        counsellorProfile: $counsellorProfile,
        overrides: [
            'mode' => Appointment::MODE_ONLINE,
            'status' => Appointment::STATUS_PENDING,
        ]
    );

    $response = $this
        ->actingAs($admin)
        ->from(route('admin.appointments.index'))
        ->patch(route('admin.appointments.update-status', $appointment), [
            'status' => Appointment::STATUS_CONFIRMED,
            'meeting_link' => 'https://meet.google.com/admin-room',
            'location' => null,
            'admin_notes' => 'Confirmed by admin due phone request.',
        ]);

    $response->assertRedirect(route('admin.appointments.index'));

    $this->assertDatabaseHas('appointments', [
        'id' => $appointment->id,
        'status' => Appointment::STATUS_CONFIRMED,
        'meeting_link' => 'https://meet.google.com/admin-room',
        'admin_notes' => 'Confirmed by admin due phone request.',
        'updated_by' => $admin->id,
    ]);

    $this->assertDatabaseHas('appointment_status_histories', [
        'appointment_id' => $appointment->id,
        'from_status' => Appointment::STATUS_PENDING,
        'to_status' => Appointment::STATUS_CONFIRMED,
        'reason' => 'Appointment status updated by admin.',
        'changed_by' => $admin->id,
    ]);

    expect($appointment->refresh()->reminder_scheduled_at)->not->toBeNull();
});

it('allows admin to confirm an in-person appointment with location', function (): void {
    $admin = createAdminAppointmentOversightAdminUser();
    $clientProfile = createAdminAppointmentOversightClientProfile();
    $counsellorProfile = createAdminAppointmentOversightCounsellorProfile();

    $appointment = createAdminAppointmentOversightAppointment(
        clientProfile: $clientProfile,
        counsellorProfile: $counsellorProfile,
        overrides: [
            'mode' => Appointment::MODE_IN_PERSON,
            'status' => Appointment::STATUS_PENDING,
        ]
    );

    $response = $this
        ->actingAs($admin)
        ->patch(route('admin.appointments.update-status', $appointment), [
            'status' => Appointment::STATUS_CONFIRMED,
            'meeting_link' => null,
            'location' => 'Room 03, Main Centre',
            'admin_notes' => 'Confirmed by admin.',
        ]);

    $response->assertRedirect(route('admin.appointments.index'));

    $this->assertDatabaseHas('appointments', [
        'id' => $appointment->id,
        'status' => Appointment::STATUS_CONFIRMED,
        'location' => 'Room 03, Main Centre',
        'admin_notes' => 'Confirmed by admin.',
    ]);
});

it('requires meeting link when admin confirms online appointment', function (): void {
    $admin = createAdminAppointmentOversightAdminUser();
    $clientProfile = createAdminAppointmentOversightClientProfile();
    $counsellorProfile = createAdminAppointmentOversightCounsellorProfile();

    $appointment = createAdminAppointmentOversightAppointment(
        clientProfile: $clientProfile,
        counsellorProfile: $counsellorProfile,
        overrides: [
            'mode' => Appointment::MODE_ONLINE,
            'status' => Appointment::STATUS_PENDING,
        ]
    );

    $this
        ->actingAs($admin)
        ->from(route('admin.appointments.index'))
        ->patch(route('admin.appointments.update-status', $appointment), [
            'status' => Appointment::STATUS_CONFIRMED,
            'meeting_link' => null,
            'location' => null,
        ])
        ->assertRedirect(route('admin.appointments.index'))
        ->assertSessionHasErrors('meeting_link');

    $this->assertDatabaseHas('appointments', [
        'id' => $appointment->id,
        'status' => Appointment::STATUS_PENDING,
        'meeting_link' => null,
    ]);
});

it('allows admin to cancel an appointment with cancellation reason', function (): void {
    $admin = createAdminAppointmentOversightAdminUser();
    $clientProfile = createAdminAppointmentOversightClientProfile();
    $counsellorProfile = createAdminAppointmentOversightCounsellorProfile();

    $appointment = createAdminAppointmentOversightAppointment(
        clientProfile: $clientProfile,
        counsellorProfile: $counsellorProfile,
        overrides: [
            'status' => Appointment::STATUS_CONFIRMED,
            'meeting_link' => 'https://meet.google.com/existing-room',
        ]
    );

    $response = $this
        ->actingAs($admin)
        ->from(route('admin.appointments.index'))
        ->patch(route('admin.appointments.update-status', $appointment), [
            'status' => Appointment::STATUS_CANCELLED,
            'cancellation_reason' => 'Cancelled after admin verification.',
            'admin_notes' => 'Client contacted office.',
        ]);

    $response->assertRedirect(route('admin.appointments.index'));

    $this->assertDatabaseHas('appointments', [
        'id' => $appointment->id,
        'status' => Appointment::STATUS_CANCELLED,
        'cancellation_reason' => 'Cancelled after admin verification.',
        'admin_notes' => 'Client contacted office.',
        'cancelled_by' => $admin->id,
        'updated_by' => $admin->id,
    ]);

    $this->assertDatabaseHas('appointment_status_histories', [
        'appointment_id' => $appointment->id,
        'from_status' => Appointment::STATUS_CONFIRMED,
        'to_status' => Appointment::STATUS_CANCELLED,
        'changed_by' => $admin->id,
    ]);

    expect($appointment->refresh()->cancelled_at)->not->toBeNull();
});

it('requires admin role to view appointment oversight page', function (): void {
    $clientProfile = createAdminAppointmentOversightClientProfile();

    $this
        ->actingAs($clientProfile->user)
        ->get(route('admin.appointments.index'))
        ->assertForbidden();
});
