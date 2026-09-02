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

function createAppointmentListClientProfile(string $name = 'Appointment List Client'): ClientProfile
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

function createAppointmentListCounsellorProfile(string $name = 'Appointment List Counsellor'): CounsellorProfile
{
    $user = User::factory()->create([
        'name' => $name,
        'email' => fake()->unique()->safeEmail(),
        'phone' => '+94 77 000 1111',
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

function createAppointmentListAppointment(
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
        'client_notes' => 'Client appointment note.',
    ], $overrides));
}

it('allows a client to view their appointment list page', function (): void {
    $clientProfile = createAppointmentListClientProfile();
    $counsellorProfile = createAppointmentListCounsellorProfile();

    createAppointmentListAppointment(
        clientProfile: $clientProfile,
        counsellorProfile: $counsellorProfile,
    );

    $response = $this
        ->actingAs($clientProfile->user)
        ->get(route('client.appointments.index'));

    $response->assertOk();

    $appointments = $response->viewData('page')['props']['appointments']['data'];

    expect($appointments)
        ->toHaveCount(1)
        ->and($appointments[0]['counsellor']['name'])->toBe($counsellorProfile->user->name)
        ->and($appointments[0]['status'])->toBe(Appointment::STATUS_PENDING);
});

it('shows only appointments belonging to the authenticated client', function (): void {
    $firstClientProfile = createAppointmentListClientProfile('First Client');
    $secondClientProfile = createAppointmentListClientProfile('Second Client');
    $counsellorProfile = createAppointmentListCounsellorProfile();

    createAppointmentListAppointment(
        clientProfile: $firstClientProfile,
        counsellorProfile: $counsellorProfile,
        overrides: [
            'client_notes' => 'Visible appointment.',
        ],
    );

    createAppointmentListAppointment(
        clientProfile: $secondClientProfile,
        counsellorProfile: $counsellorProfile,
        overrides: [
            'client_notes' => 'Hidden appointment.',
        ],
    );

    $response = $this
        ->actingAs($firstClientProfile->user)
        ->get(route('client.appointments.index'));

    $response->assertOk();

    $appointments = $response->viewData('page')['props']['appointments']['data'];

    expect($appointments)
        ->toHaveCount(1)
        ->and($appointments[0]['client_notes'])->toBe('Visible appointment.');
});

it('filters client appointments by status', function (): void {
    $clientProfile = createAppointmentListClientProfile();
    $counsellorProfile = createAppointmentListCounsellorProfile();

    createAppointmentListAppointment(
        clientProfile: $clientProfile,
        counsellorProfile: $counsellorProfile,
        overrides: [
            'status' => Appointment::STATUS_PENDING,
        ],
    );

    createAppointmentListAppointment(
        clientProfile: $clientProfile,
        counsellorProfile: $counsellorProfile,
        overrides: [
            'status' => Appointment::STATUS_CONFIRMED,
            'start_time' => '10:00',
            'end_time' => '11:00',
        ],
    );

    $response = $this
        ->actingAs($clientProfile->user)
        ->get(route('client.appointments.index', [
            'status' => Appointment::STATUS_CONFIRMED,
        ]));

    $response->assertOk();

    $appointments = $response->viewData('page')['props']['appointments']['data'];

    expect($appointments)
        ->toHaveCount(1)
        ->and($appointments[0]['status'])->toBe(Appointment::STATUS_CONFIRMED);
});

it('filters client appointments by upcoming period', function (): void {
    $clientProfile = createAppointmentListClientProfile();
    $counsellorProfile = createAppointmentListCounsellorProfile();

    createAppointmentListAppointment(
        clientProfile: $clientProfile,
        counsellorProfile: $counsellorProfile,
        overrides: [
            'appointment_date' => now()->addDays(2)->toDateString(),
            'client_notes' => 'Upcoming appointment.',
        ],
    );

    createAppointmentListAppointment(
        clientProfile: $clientProfile,
        counsellorProfile: $counsellorProfile,
        overrides: [
            'appointment_date' => now()->subDays(2)->toDateString(),
            'client_notes' => 'Past appointment.',
        ],
    );

    $response = $this
        ->actingAs($clientProfile->user)
        ->get(route('client.appointments.index', [
            'period' => 'upcoming',
        ]));

    $response->assertOk();

    $appointments = $response->viewData('page')['props']['appointments']['data'];

    expect($appointments)
        ->toHaveCount(1)
        ->and($appointments[0]['client_notes'])->toBe('Upcoming appointment.');
});

it('filters client appointments by past period', function (): void {
    $clientProfile = createAppointmentListClientProfile();
    $counsellorProfile = createAppointmentListCounsellorProfile();

    createAppointmentListAppointment(
        clientProfile: $clientProfile,
        counsellorProfile: $counsellorProfile,
        overrides: [
            'appointment_date' => now()->addDays(2)->toDateString(),
            'client_notes' => 'Upcoming appointment.',
        ],
    );

    createAppointmentListAppointment(
        clientProfile: $clientProfile,
        counsellorProfile: $counsellorProfile,
        overrides: [
            'appointment_date' => now()->subDays(2)->toDateString(),
            'client_notes' => 'Past appointment.',
        ],
    );

    $response = $this
        ->actingAs($clientProfile->user)
        ->get(route('client.appointments.index', [
            'period' => 'past',
        ]));

    $response->assertOk();

    $appointments = $response->viewData('page')['props']['appointments']['data'];

    expect($appointments)
        ->toHaveCount(1)
        ->and($appointments[0]['client_notes'])->toBe('Past appointment.');
});

it('shows all client appointments when period is all', function (): void {
    $clientProfile = createAppointmentListClientProfile();
    $counsellorProfile = createAppointmentListCounsellorProfile();

    createAppointmentListAppointment(
        clientProfile: $clientProfile,
        counsellorProfile: $counsellorProfile,
        overrides: [
            'appointment_date' => now()->addDays(2)->toDateString(),
        ],
    );

    createAppointmentListAppointment(
        clientProfile: $clientProfile,
        counsellorProfile: $counsellorProfile,
        overrides: [
            'appointment_date' => now()->subDays(2)->toDateString(),
        ],
    );

    $response = $this
        ->actingAs($clientProfile->user)
        ->get(route('client.appointments.index', [
            'period' => 'all',
        ]));

    $response->assertOk();

    $appointments = $response->viewData('page')['props']['appointments']['data'];

    expect($appointments)->toHaveCount(2);
});

it('requires authenticated client role to view appointment list', function (): void {
    $admin = User::factory()->create([
        'is_active' => true,
    ]);

    $admin->assignRole('admin');

    $this
        ->actingAs($admin)
        ->get(route('client.appointments.index'))
        ->assertForbidden();
});
