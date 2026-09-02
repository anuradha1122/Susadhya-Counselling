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

function m11CreateUser(string $role, string $name): User
{
    $user = User::factory()->create([
        'name' => $name,
        'email' => fake()->unique()->safeEmail(),
        'phone' => '+94 77 411 0000',
        'is_active' => true,
    ]);

    $user->assignRole($role);

    return $user;
}

function m11CreateClientProfile(string $name = 'M11 Client'): ClientProfile
{
    return ClientProfile::factory()->create([
        'user_id' => m11CreateUser('client', $name)->id,
    ]);
}

function m11CreateCounsellorProfile(string $name = 'M11 Counsellor'): CounsellorProfile
{
    return CounsellorProfile::factory()->create([
        'user_id' => m11CreateUser('counsellor', $name)->id,
        'professional_title' => 'Clinical Counsellor',
        'city' => 'Colombo',
        'status' => 'active',
    ]);
}

function m11CreateAppointment(
    ClientProfile $clientProfile,
    CounsellorProfile $counsellorProfile,
    array $overrides = []
): Appointment {
    return Appointment::query()->create(array_merge([
        'client_profile_id' => $clientProfile->id,
        'counsellor_profile_id' => $counsellorProfile->id,
        'appointment_date' => CarbonImmutable::now()->toDateString(),
        'start_time' => '09:00',
        'end_time' => '10:00',
        'timezone' => 'Asia/Colombo',
        'mode' => Appointment::MODE_ONLINE,
        'status' => Appointment::STATUS_CONFIRMED,
        'meeting_link' => 'https://meet.google.com/m11-session',
    ], $overrides));
}

function m11CreateSession(
    Appointment $appointment,
    array $overrides = []
): CounsellingSession {
    return CounsellingSession::query()->create(array_merge([
        'appointment_id' => $appointment->id,
        'client_profile_id' => $appointment->client_profile_id,
        'counsellor_profile_id' => $appointment->counsellor_profile_id,
        'status' => CounsellingSession::STATUS_IN_PROGRESS,
        'mode' => $appointment->mode,
        'started_at' => now(),
        'clinical_risk_level' => CounsellingSession::RISK_LOW,
    ], $overrides));
}

it('allows counsellor to view session delivery page', function (): void {
    $clientProfile = m11CreateClientProfile();
    $counsellorProfile = m11CreateCounsellorProfile();

    m11CreateAppointment($clientProfile, $counsellorProfile);

    $response = $this
        ->actingAs($counsellorProfile->user)
        ->get(route('counsellor.sessions.index'));

    $response->assertOk();

    $page = $response->viewData('page');

    expect($page['component'])
        ->toBe('Counsellor/Sessions/Index')
        ->and($page['props']['readyAppointments'])
        ->toHaveCount(1);
});

it('starts a session from a confirmed appointment', function (): void {
    $clientProfile = m11CreateClientProfile();
    $counsellorProfile = m11CreateCounsellorProfile();

    $appointment = m11CreateAppointment($clientProfile, $counsellorProfile);

    $this
        ->actingAs($counsellorProfile->user)
        ->post(route('counsellor.sessions.start', $appointment))
        ->assertRedirect(route('counsellor.sessions.index'));

    $this->assertDatabaseHas('counselling_sessions', [
        'appointment_id' => $appointment->id,
        'client_profile_id' => $clientProfile->id,
        'counsellor_profile_id' => $counsellorProfile->id,
        'status' => CounsellingSession::STATUS_IN_PROGRESS,
        'mode' => Appointment::MODE_ONLINE,
        'created_by' => $counsellorProfile->user_id,
    ]);
});

it('prevents starting a session from pending appointment', function (): void {
    $clientProfile = m11CreateClientProfile();
    $counsellorProfile = m11CreateCounsellorProfile();

    $appointment = m11CreateAppointment($clientProfile, $counsellorProfile, [
        'status' => Appointment::STATUS_PENDING,
    ]);

    $this
        ->actingAs($counsellorProfile->user)
        ->from(route('counsellor.sessions.index'))
        ->post(route('counsellor.sessions.start', $appointment))
        ->assertRedirect(route('counsellor.sessions.index'))
        ->assertSessionHasErrors('appointment');

    $this->assertDatabaseMissing('counselling_sessions', [
        'appointment_id' => $appointment->id,
    ]);
});

it('prevents counsellor from starting another counsellors appointment', function (): void {
    $clientProfile = m11CreateClientProfile();
    $firstCounsellorProfile = m11CreateCounsellorProfile('First M11 Counsellor');
    $secondCounsellorProfile = m11CreateCounsellorProfile('Second M11 Counsellor');

    $appointment = m11CreateAppointment($clientProfile, $secondCounsellorProfile);

    $this
        ->actingAs($firstCounsellorProfile->user)
        ->post(route('counsellor.sessions.start', $appointment))
        ->assertNotFound();
});

it('allows counsellor to add a session note', function (): void {
    $clientProfile = m11CreateClientProfile();
    $counsellorProfile = m11CreateCounsellorProfile();
    $appointment = m11CreateAppointment($clientProfile, $counsellorProfile);
    $session = m11CreateSession($appointment);

    $this
        ->actingAs($counsellorProfile->user)
        ->post(route('counsellor.sessions.notes.store', $session), [
            'note_type' => SessionNote::TYPE_PROGRESS,
            'visibility' => SessionNote::VISIBILITY_PRIVATE,
            'content' => 'Client participated well in the session.',
        ])
        ->assertRedirect(route('counsellor.sessions.index'));

    $this->assertDatabaseHas('session_notes', [
        'counselling_session_id' => $session->id,
        'author_id' => $counsellorProfile->user_id,
        'note_type' => SessionNote::TYPE_PROGRESS,
        'visibility' => SessionNote::VISIBILITY_PRIVATE,
        'content' => 'Client participated well in the session.',
    ]);
});

it('prevents adding notes to another counsellors session', function (): void {
    $clientProfile = m11CreateClientProfile();
    $firstCounsellorProfile = m11CreateCounsellorProfile('First Note Counsellor');
    $secondCounsellorProfile = m11CreateCounsellorProfile('Second Note Counsellor');

    $appointment = m11CreateAppointment($clientProfile, $secondCounsellorProfile);
    $session = m11CreateSession($appointment);

    $this
        ->actingAs($firstCounsellorProfile->user)
        ->post(route('counsellor.sessions.notes.store', $session), [
            'note_type' => SessionNote::TYPE_PROGRESS,
            'visibility' => SessionNote::VISIBILITY_PRIVATE,
            'content' => 'Trying to write where I should not.',
        ])
        ->assertNotFound();
});

it('allows counsellor to complete session and completes appointment', function (): void {
    $clientProfile = m11CreateClientProfile();
    $counsellorProfile = m11CreateCounsellorProfile();
    $appointment = m11CreateAppointment($clientProfile, $counsellorProfile);
    $session = m11CreateSession($appointment);

    $this
        ->actingAs($counsellorProfile->user)
        ->patch(route('counsellor.sessions.complete', $session), [
            'presenting_summary' => 'Client presented with work stress.',
            'intervention_summary' => 'Used supportive counselling and breathing exercise.',
            'outcome_summary' => 'Client reported reduced distress.',
            'client_visible_summary' => 'We discussed stress management strategies.',
            'homework' => 'Practice daily breathing exercise.',
            'private_notes' => 'Monitor stress pattern.',
            'clinical_risk_level' => CounsellingSession::RISK_LOW,
            'follow_up_recommended' => true,
            'follow_up_notes' => 'Book another session next week.',
            'next_session_recommended_at' => now()->addWeek()->toDateString(),
        ])
        ->assertRedirect(route('counsellor.sessions.index'));

    $this->assertDatabaseHas('counselling_sessions', [
        'id' => $session->id,
        'status' => CounsellingSession::STATUS_COMPLETED,
        'intervention_summary' => 'Used supportive counselling and breathing exercise.',
        'outcome_summary' => 'Client reported reduced distress.',
        'client_visible_summary' => 'We discussed stress management strategies.',
        'follow_up_recommended' => true,
        'clinical_risk_level' => CounsellingSession::RISK_LOW,
    ]);

    $this->assertDatabaseHas('appointments', [
        'id' => $appointment->id,
        'status' => Appointment::STATUS_COMPLETED,
        'counsellor_notes' => 'Client reported reduced distress.',
    ]);

    $this->assertDatabaseHas('appointment_status_histories', [
        'appointment_id' => $appointment->id,
        'from_status' => Appointment::STATUS_CONFIRMED,
        'to_status' => Appointment::STATUS_COMPLETED,
        'reason' => 'Appointment completed through counselling session delivery.',
        'changed_by' => $counsellorProfile->user_id,
    ]);

    expect($session->refresh()->completed_at)->not->toBeNull();
});

it('prevents completing another counsellors session', function (): void {
    $clientProfile = m11CreateClientProfile();
    $firstCounsellorProfile = m11CreateCounsellorProfile('First Complete Counsellor');
    $secondCounsellorProfile = m11CreateCounsellorProfile('Second Complete Counsellor');

    $appointment = m11CreateAppointment($clientProfile, $secondCounsellorProfile);
    $session = m11CreateSession($appointment);

    $this
        ->actingAs($firstCounsellorProfile->user)
        ->patch(route('counsellor.sessions.complete', $session), [
            'intervention_summary' => 'Invalid intervention.',
            'outcome_summary' => 'Invalid outcome.',
            'clinical_risk_level' => CounsellingSession::RISK_LOW,
        ])
        ->assertNotFound();
});
