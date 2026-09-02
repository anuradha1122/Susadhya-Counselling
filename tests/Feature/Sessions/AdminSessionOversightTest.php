<?php

use App\Models\Appointment;
use App\Models\ClientProfile;
use App\Models\CounsellingSession;
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

function m11AdminSessionUser(string $role, string $name): User
{
    $user = User::factory()->create([
        'name' => $name,
        'email' => fake()->unique()->safeEmail(),
        'phone' => '+94 77 633 0000',
        'is_active' => true,
    ]);

    $user->assignRole($role);

    return $user;
}

function m11AdminSessionClientProfile(string $name = 'M11 Admin Client'): ClientProfile
{
    return ClientProfile::factory()->create([
        'user_id' => m11AdminSessionUser('client', $name)->id,
    ]);
}

function m11AdminSessionCounsellorProfile(string $name = 'M11 Admin Counsellor'): CounsellorProfile
{
    return CounsellorProfile::factory()->create([
        'user_id' => m11AdminSessionUser('counsellor', $name)->id,
        'professional_title' => 'Clinical Counsellor',
        'city' => 'Colombo',
        'status' => 'active',
    ]);
}

function m11AdminSessionAppointment(
    ClientProfile $clientProfile,
    CounsellorProfile $counsellorProfile
): Appointment {
    return Appointment::query()->create([
        'client_profile_id' => $clientProfile->id,
        'counsellor_profile_id' => $counsellorProfile->id,
        'appointment_date' => CarbonImmutable::now()->subDay()->toDateString(),
        'start_time' => fake()->randomElement(['09:00', '10:00', '11:00']),
        'end_time' => fake()->randomElement(['10:00', '11:00', '12:00']),
        'timezone' => 'Asia/Colombo',
        'mode' => Appointment::MODE_ONLINE,
        'status' => Appointment::STATUS_COMPLETED,
    ]);
}

function m11AdminSessionRecord(
    ClientProfile $clientProfile,
    CounsellorProfile $counsellorProfile,
    array $overrides = []
): CounsellingSession {
    $appointment = m11AdminSessionAppointment($clientProfile, $counsellorProfile);

    return CounsellingSession::query()->create(array_merge([
        'appointment_id' => $appointment->id,
        'client_profile_id' => $clientProfile->id,
        'counsellor_profile_id' => $counsellorProfile->id,
        'status' => CounsellingSession::STATUS_COMPLETED,
        'mode' => Appointment::MODE_ONLINE,
        'started_at' => now()->subHour(),
        'ended_at' => now(),
        'completed_at' => now(),
        'presenting_summary' => 'Admin session presenting summary.',
        'intervention_summary' => 'Admin session intervention summary.',
        'outcome_summary' => 'Admin session outcome summary.',
        'clinical_risk_level' => CounsellingSession::RISK_LOW,
        'follow_up_recommended' => false,
    ], $overrides));
}

it('allows admin to view session oversight page', function (): void {
    $admin = m11AdminSessionUser('admin', 'M11 Session Admin');
    $clientProfile = m11AdminSessionClientProfile();
    $counsellorProfile = m11AdminSessionCounsellorProfile();

    m11AdminSessionRecord($clientProfile, $counsellorProfile);

    $response = $this
        ->actingAs($admin)
        ->get(route('admin.sessions.index'));

    $response->assertOk();

    $page = $response->viewData('page');
    $sessions = $page['props']['sessions']['data'];

    expect($page['component'])
        ->toBe('Admin/Sessions/Index')
        ->and($sessions)
        ->toHaveCount(1)
        ->and($sessions[0]['client']['name'])
        ->toBe($clientProfile->user->name);
});

it('filters sessions by risk level', function (): void {
    $admin = m11AdminSessionUser('admin', 'M11 Risk Admin');

    $lowClientProfile = m11AdminSessionClientProfile('Low Risk Session Client');
    $urgentClientProfile = m11AdminSessionClientProfile('Urgent Risk Session Client');
    $counsellorProfile = m11AdminSessionCounsellorProfile();

    m11AdminSessionRecord($lowClientProfile, $counsellorProfile, [
        'clinical_risk_level' => CounsellingSession::RISK_LOW,
    ]);

    m11AdminSessionRecord($urgentClientProfile, $counsellorProfile, [
        'clinical_risk_level' => CounsellingSession::RISK_URGENT,
    ]);

    $response = $this
        ->actingAs($admin)
        ->get(route('admin.sessions.index', [
            'risk_level' => CounsellingSession::RISK_URGENT,
        ]));

    $sessions = $response->viewData('page')['props']['sessions']['data'];

    expect($sessions)
        ->toHaveCount(1)
        ->and($sessions[0]['clinical_risk_level'])
        ->toBe(CounsellingSession::RISK_URGENT)
        ->and($sessions[0]['client']['name'])
        ->toBe('Urgent Risk Session Client');
});

it('filters sessions by client search', function (): void {
    $admin = m11AdminSessionUser('admin', 'M11 Search Admin');

    $searchClientProfile = m11AdminSessionClientProfile('Search Session Client');
    $hiddenClientProfile = m11AdminSessionClientProfile('Hidden Session Client');
    $counsellorProfile = m11AdminSessionCounsellorProfile();

    m11AdminSessionRecord($searchClientProfile, $counsellorProfile);
    m11AdminSessionRecord($hiddenClientProfile, $counsellorProfile);

    $response = $this
        ->actingAs($admin)
        ->get(route('admin.sessions.index', [
            'search' => 'Search Session Client',
        ]));

    $sessions = $response->viewData('page')['props']['sessions']['data'];

    expect($sessions)
        ->toHaveCount(1)
        ->and($sessions[0]['client']['name'])
        ->toBe('Search Session Client');
});

it('allows admin to update session review details', function (): void {
    $admin = m11AdminSessionUser('admin', 'M11 Review Admin');
    $clientProfile = m11AdminSessionClientProfile();
    $counsellorProfile = m11AdminSessionCounsellorProfile();

    $session = m11AdminSessionRecord($clientProfile, $counsellorProfile);

    $this
        ->actingAs($admin)
        ->from(route('admin.sessions.index'))
        ->patch(route('admin.sessions.review', $session), [
            'clinical_risk_level' => CounsellingSession::RISK_HIGH,
            'follow_up_recommended' => true,
            'follow_up_notes' => 'Schedule follow-up within one week.',
            'admin_notes' => 'Reviewed by admin.',
        ])
        ->assertRedirect(route('admin.sessions.index'));

    $this->assertDatabaseHas('counselling_sessions', [
        'id' => $session->id,
        'clinical_risk_level' => CounsellingSession::RISK_HIGH,
        'follow_up_recommended' => true,
        'follow_up_notes' => 'Schedule follow-up within one week.',
        'admin_notes' => 'Reviewed by admin.',
        'updated_by' => $admin->id,
    ]);
});

it('requires admin role to access session oversight', function (): void {
    $clientProfile = m11AdminSessionClientProfile();

    $this
        ->actingAs($clientProfile->user)
        ->get(route('admin.sessions.index'))
        ->assertForbidden();
});
