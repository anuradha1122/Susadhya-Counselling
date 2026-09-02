<?php

use App\Models\ClientIntake;
use App\Models\ClientProfile;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    app(PermissionRegistrar::class)->forgetCachedPermissions();

    $this->seed(RolePermissionSeeder::class);
});

function createM10ClientProfile(string $name = 'M10 Client'): ClientProfile
{
    $user = User::factory()->create([
        'name' => $name,
        'email' => fake()->unique()->safeEmail(),
        'phone' => '+94 77 100 2000',
        'is_active' => true,
    ]);

    $user->assignRole('client');

    return ClientProfile::factory()->create([
        'user_id' => $user->id,
    ]);
}

function m10ScreeningAnswers(int $defaultScore = 1, int $selfHarmScore = 0): array
{
    return collect(ClientIntake::screeningQuestions())
        ->mapWithKeys(fn (array $question): array => [
            $question['key'] => [
                'answer_score' => $question['key'] === 'self_harm_thoughts'
                    ? $selfHarmScore
                    : $defaultScore,
                'answer_notes' => null,
            ],
        ])
        ->all();
}

function m10ValidIntakePayload(array $overrides = []): array
{
    return array_merge([
        'presenting_concerns' => 'I need support with stress and anxiety.',
        'current_symptoms' => 'Sleep issues and worry.',
        'counselling_goals' => 'I want to manage stress better.',
        'preferred_session_mode' => 'either',
        'previous_counselling' => false,
        'previous_counselling_notes' => null,
        'medication_notes' => null,
        'emergency_contact_name' => 'Emergency Person',
        'emergency_contact_phone' => '+94 77 111 2222',
        'emergency_contact_relationship' => 'Brother',
        'consent_terms_accepted' => true,
        'consent_privacy_accepted' => true,
        'consent_telehealth_accepted' => true,
        'consent_data_processing_accepted' => true,
        'screening_answers' => m10ScreeningAnswers(),
    ], $overrides);
}

it('allows a client to view intake page and creates draft intake', function (): void {
    $clientProfile = createM10ClientProfile();

    $response = $this
        ->actingAs($clientProfile->user)
        ->get(route('client.intake.edit'));

    $response->assertOk();

    $this->assertDatabaseHas('client_intakes', [
        'client_profile_id' => $clientProfile->id,
        'status' => ClientIntake::STATUS_DRAFT,
    ]);

    $page = $response->viewData('page');

    expect($page['component'])
        ->toBe('Client/Intake/Edit')
        ->and($page['props']['screeningQuestions'])
        ->toHaveCount(7);
});

it('allows a client to save intake draft', function (): void {
    $clientProfile = createM10ClientProfile();

    $response = $this
        ->actingAs($clientProfile->user)
        ->patch(route('client.intake.update'), [
            'presenting_concerns' => 'Draft concern.',
            'current_symptoms' => 'Draft symptoms.',
            'counselling_goals' => 'Draft goals.',
            'preferred_session_mode' => 'online',
            'previous_counselling' => true,
            'previous_counselling_notes' => 'Had counselling previously.',
            'medication_notes' => 'No medication.',
            'emergency_contact_name' => 'Emergency Contact',
            'emergency_contact_phone' => '+94 77 123 4567',
            'emergency_contact_relationship' => 'Sister',
            'consent_terms_accepted' => false,
            'consent_privacy_accepted' => false,
            'consent_telehealth_accepted' => false,
            'consent_data_processing_accepted' => false,
            'screening_answers' => m10ScreeningAnswers(1, 0),
        ]);

    $response->assertRedirect(route('client.intake.edit'));

    $this->assertDatabaseHas('client_intakes', [
        'client_profile_id' => $clientProfile->id,
        'status' => ClientIntake::STATUS_DRAFT,
        'presenting_concerns' => 'Draft concern.',
        'preferred_session_mode' => 'online',
        'previous_counselling' => true,
    ]);

    $intake = ClientIntake::query()
        ->where('client_profile_id', $clientProfile->id)
        ->firstOrFail();

    expect($intake->screeningAnswers)->toHaveCount(7);
});

it('allows a client to submit completed intake with consent and screening', function (): void {
    $clientProfile = createM10ClientProfile();

    $response = $this
        ->actingAs($clientProfile->user)
        ->post(route('client.intake.submit'), m10ValidIntakePayload([
            'screening_answers' => m10ScreeningAnswers(defaultScore: 1, selfHarmScore: 0),
        ]));

    $response->assertRedirect(route('client.intake.edit'));

    $this->assertDatabaseHas('client_intakes', [
        'client_profile_id' => $clientProfile->id,
        'status' => ClientIntake::STATUS_SUBMITTED,
        'risk_level' => ClientIntake::RISK_LOW,
        'consent_terms_accepted' => true,
        'consent_privacy_accepted' => true,
        'consent_telehealth_accepted' => true,
        'consent_data_processing_accepted' => true,
    ]);

    $intake = ClientIntake::query()
        ->where('client_profile_id', $clientProfile->id)
        ->firstOrFail();

    expect($intake->submitted_at)->not->toBeNull()
        ->and($intake->consent_given_at)->not->toBeNull()
        ->and($intake->screeningAnswers)->toHaveCount(7);
});

it('calculates urgent risk when self-harm screening score is high', function (): void {
    $clientProfile = createM10ClientProfile();

    $this
        ->actingAs($clientProfile->user)
        ->post(route('client.intake.submit'), m10ValidIntakePayload([
            'screening_answers' => m10ScreeningAnswers(defaultScore: 1, selfHarmScore: 2),
        ]))
        ->assertRedirect(route('client.intake.edit'));

    $this->assertDatabaseHas('client_intakes', [
        'client_profile_id' => $clientProfile->id,
        'status' => ClientIntake::STATUS_SUBMITTED,
        'risk_level' => ClientIntake::RISK_URGENT,
    ]);
});

it('requires consent before submitting intake', function (): void {
    $clientProfile = createM10ClientProfile();

    $this
        ->actingAs($clientProfile->user)
        ->from(route('client.intake.edit'))
        ->post(route('client.intake.submit'), m10ValidIntakePayload([
            'consent_terms_accepted' => false,
        ]))
        ->assertRedirect(route('client.intake.edit'))
        ->assertSessionHasErrors('consent_terms_accepted');

    $this->assertDatabaseMissing('client_intakes', [
        'client_profile_id' => $clientProfile->id,
        'status' => ClientIntake::STATUS_SUBMITTED,
    ]);
});

it('requires all screening answers before submitting intake', function (): void {
    $clientProfile = createM10ClientProfile();

    $answers = m10ScreeningAnswers();
    unset($answers['self_harm_thoughts']);

    $this
        ->actingAs($clientProfile->user)
        ->from(route('client.intake.edit'))
        ->post(route('client.intake.submit'), m10ValidIntakePayload([
            'screening_answers' => $answers,
        ]))
        ->assertRedirect(route('client.intake.edit'))
        ->assertSessionHasErrors('screening_answers.self_harm_thoughts.answer_score');
});

it('prevents client from editing submitted intake', function (): void {
    $clientProfile = createM10ClientProfile();

    ClientIntake::factory()
        ->for($clientProfile)
        ->submitted()
        ->create([
            'presenting_concerns' => 'Submitted concern.',
        ]);

    $this
        ->actingAs($clientProfile->user)
        ->from(route('client.intake.edit'))
        ->patch(route('client.intake.update'), [
            'presenting_concerns' => 'Trying to edit submitted intake.',
        ])
        ->assertRedirect(route('client.intake.edit'))
        ->assertSessionHasErrors('intake');

    $this->assertDatabaseHas('client_intakes', [
        'client_profile_id' => $clientProfile->id,
        'presenting_concerns' => 'Submitted concern.',
    ]);
});

it('requires authenticated client role to access intake page', function (): void {
    $admin = User::factory()->create([
        'is_active' => true,
    ]);

    $admin->assignRole('admin');

    $this
        ->actingAs($admin)
        ->get(route('client.intake.edit'))
        ->assertForbidden();
});
