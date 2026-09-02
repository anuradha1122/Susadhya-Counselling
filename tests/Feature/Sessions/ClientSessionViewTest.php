<?php

use App\Models\Appointment;
use App\Models\ClientProfile;
use App\Models\CounsellingSession;
use App\Models\CounsellorProfile;
use App\Models\SessionNote;
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

function m11ClientViewCreateUser(string $role, string $name): User
{
    $user = User::factory()->create([
        'name' => $name,
        'email' => fake()->unique()->safeEmail(),
        'phone' => '+94 77 522 0000',
        'is_active' => true,
    ]);

    $user->assignRole($role);

    return $user;
}

function m11ClientViewClientProfile(string $name = 'M11 View Client'): ClientProfile
{
    return ClientProfile::factory()->create([
        'user_id' => m11ClientViewCreateUser('client', $name)->id,
    ]);
}

function m11ClientViewCounsellorProfile(): CounsellorProfile
{
    return CounsellorProfile::factory()->create([
        'user_id' => m11ClientViewCreateUser('counsellor', 'M11 View Counsellor')->id,
        'professional_title' => 'Clinical Counsellor',
        'city' => 'Colombo',
        'status' => 'active',
    ]);
}

function m11ClientViewAppointment(
    ClientProfile $clientProfile,
    CounsellorProfile $counsellorProfile,
    array $overrides = []
): Appointment {
    return Appointment::query()->create(array_merge([
        'client_profile_id' => $clientProfile->id,
        'counsellor_profile_id' => $counsellorProfile->id,
        'appointment_date' => CarbonImmutable::now()->subDay()->toDateString(),
        'start_time' => '09:00',
        'end_time' => '10:00',
        'timezone' => 'Asia/Colombo',
        'mode' => Appointment::MODE_ONLINE,
        'status' => Appointment::STATUS_COMPLETED,
    ], $overrides));
}

function m11ClientViewSession(
    Appointment $appointment,
    array $overrides = []
): CounsellingSession {
    return CounsellingSession::query()->create(array_merge([
        'appointment_id' => $appointment->id,
        'client_profile_id' => $appointment->client_profile_id,
        'counsellor_profile_id' => $appointment->counsellor_profile_id,
        'status' => CounsellingSession::STATUS_COMPLETED,
        'mode' => $appointment->mode,
        'started_at' => now()->subHour(),
        'ended_at' => now(),
        'completed_at' => now(),
        'client_visible_summary' => 'Client-visible summary for session.',
        'homework' => 'Practice grounding exercise.',
        'clinical_risk_level' => CounsellingSession::RISK_LOW,
    ], $overrides));
}

it('allows client to view own completed session summaries', function (): void {
    $clientProfile = m11ClientViewClientProfile();
    $counsellorProfile = m11ClientViewCounsellorProfile();
    $appointment = m11ClientViewAppointment($clientProfile, $counsellorProfile);
    $session = m11ClientViewSession($appointment);

    SessionNote::factory()->create([
        'counselling_session_id' => $session->id,
        'author_id' => $counsellorProfile->user_id,
        'visibility' => SessionNote::VISIBILITY_CLIENT,
        'content' => 'Shared client note.',
    ]);

    $response = $this
        ->actingAs($clientProfile->user)
        ->get(route('client.sessions.index'));

    $response->assertOk();

    $page = $response->viewData('page');
    $sessions = $page['props']['sessions']['data'];

    expect($page['component'])
        ->toBe('Client/Sessions/Index')
        ->and($sessions)
        ->toHaveCount(1)
        ->and($sessions[0]['client_visible_summary'])
        ->toBe('Client-visible summary for session.')
        ->and($sessions[0]['notes'])
        ->toHaveCount(1);
});

it('does not show in-progress sessions to client', function (): void {
    $clientProfile = m11ClientViewClientProfile();
    $counsellorProfile = m11ClientViewCounsellorProfile();
    $appointment = m11ClientViewAppointment($clientProfile, $counsellorProfile, [
        'status' => Appointment::STATUS_CONFIRMED,
    ]);

    m11ClientViewSession($appointment, [
        'status' => CounsellingSession::STATUS_IN_PROGRESS,
        'completed_at' => null,
    ]);

    $response = $this
        ->actingAs($clientProfile->user)
        ->get(route('client.sessions.index'));

    $sessions = $response->viewData('page')['props']['sessions']['data'];

    expect($sessions)->toHaveCount(0);
});

it('does not show another clients completed session', function (): void {
    $firstClientProfile = m11ClientViewClientProfile('First Session Client');
    $secondClientProfile = m11ClientViewClientProfile('Second Session Client');
    $counsellorProfile = m11ClientViewCounsellorProfile();

    $appointment = m11ClientViewAppointment($secondClientProfile, $counsellorProfile);
    m11ClientViewSession($appointment);

    $response = $this
        ->actingAs($firstClientProfile->user)
        ->get(route('client.sessions.index'));

    $sessions = $response->viewData('page')['props']['sessions']['data'];

    expect($sessions)->toHaveCount(0);
});

it('requires client role to view client sessions', function (): void {
    $admin = m11ClientViewCreateUser('admin', 'Session Admin User');

    $this
        ->actingAs($admin)
        ->get(route('client.sessions.index'))
        ->assertForbidden();
});
