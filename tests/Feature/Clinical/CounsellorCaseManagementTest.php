<?php

use App\Models\ClientCase;
use App\Models\ClientProfile;
use App\Models\CounsellingSession;
use App\Models\CounsellorProfile;
use App\Models\User;
use Database\Seeders\ClinicalRecordPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(
        ClinicalRecordPermissionSeeder::class
    );
});

function createClinicalCaseCounsellorContext(
    bool $withSession = false,
): array {
    $user = User::factory()->create();

    $user->assignRole('counsellor');

    $counsellor =
        CounsellorProfile::factory()->create([
            'user_id' => $user->id,
        ]);

    $client =
        ClientProfile::factory()->create();

    $session = null;

    if ($withSession) {
        $session =
            CounsellingSession::factory()
                ->completed($user)
                ->create([
                    'client_profile_id' => $client->id,

                    'counsellor_profile_id' => $counsellor->id,
                ]);
    }

    return [
        'user' => $user,
        'counsellor' => $counsellor,
        'client' => $client,
        'session' => $session,
    ];
}

it('allows a counsellor to open a clinical case for an existing counselling relationship', function (): void {
    $context =
        createClinicalCaseCounsellorContext(
            withSession: true,
        );

    $response = $this
        ->actingAs($context['user'])
        ->post(
            route('counsellor.cases.store'),
            [
                'client_profile_id' => $context['client']->id,

                'summary' => 'Client reports ongoing work-related stress.',

                'formulation' => 'Stress appears related to workload and coping capacity.',

                'risk_level' => ClientCase::RISK_MODERATE,

                'risk_flag' => true,

                'risk_notes' => 'Monitor stress and sleep difficulties.',
            ]
        );

    $case =
        ClientCase::query()->first();

    expect($case)
        ->not
        ->toBeNull();

    expect(
        $case->client_profile_id
    )->toBe(
        $context['client']->id
    );

    expect(
        $case->counsellor_profile_id
    )->toBe(
        $context['counsellor']->id
    );

    expect(
        $case->opened_by
    )->toBe(
        $context['user']->id
    );

    expect(
        $case->status
    )->toBe(
        ClientCase::STATUS_OPEN
    );

    expect(
        $case->risk_level
    )->toBe(
        ClientCase::RISK_MODERATE
    );

    expect(
        $case->risk_flag
    )->toBeTrue();

    $response->assertRedirect(
        route(
            'counsellor.cases.show',
            $case
        )
    );

    $this->assertDatabaseHas(
        'client_cases',
        [
            'id' => $case->id,

            'client_profile_id' => $context['client']->id,

            'counsellor_profile_id' => $context['counsellor']->id,

            'status' => ClientCase::STATUS_OPEN,

            'risk_level' => ClientCase::RISK_MODERATE,
        ]
    );
});

it('does not allow a counsellor to open a case without an existing counselling relationship', function (): void {
    $user =
        User::factory()->create();

    $user->assignRole('counsellor');

    CounsellorProfile::factory()->create([
        'user_id' => $user->id,
    ]);

    $client =
        ClientProfile::factory()->create();

    $response = $this
        ->actingAs($user)
        ->post(
            route('counsellor.cases.store'),
            [
                'client_profile_id' => $client->id,

                'summary' => 'Attempted clinical case.',

                'formulation' => null,

                'risk_level' => ClientCase::RISK_LOW,

                'risk_flag' => false,

                'risk_notes' => null,
            ]
        );

    $response->assertSessionHasErrors(
        'client_profile_id'
    );

    expect(
        ClientCase::query()->count()
    )->toBe(0);
});

it('prevents a counsellor from reading another counsellors clinical case', function (): void {
    $first =
        createClinicalCaseCounsellorContext();

    $second =
        createClinicalCaseCounsellorContext();

    $case =
        ClientCase::factory()->create([
            'client_profile_id' => $first['client']->id,

            'counsellor_profile_id' => $first['counsellor']->id,

            'opened_by' => $first['user']->id,

            'status' => ClientCase::STATUS_OPEN,
        ]);

    $this
        ->actingAs($second['user'])
        ->get(
            route(
                'counsellor.cases.show',
                $case
            )
        )
        ->assertForbidden();
});

it('allows the assigned counsellor to update case summary and risk information', function (): void {
    $context =
        createClinicalCaseCounsellorContext();

    $case =
        ClientCase::factory()->create([
            'client_profile_id' => $context['client']->id,

            'counsellor_profile_id' => $context['counsellor']->id,

            'opened_by' => $context['user']->id,

            'status' => ClientCase::STATUS_OPEN,

            'risk_level' => ClientCase::RISK_LOW,

            'risk_flag' => false,
        ]);

    $response = $this
        ->actingAs($context['user'])
        ->patch(
            route(
                'counsellor.cases.update',
                $case
            ),
            [
                'status' => ClientCase::STATUS_ON_HOLD,

                'summary' => 'Updated case summary.',

                'formulation' => 'Updated clinical formulation.',

                'risk_level' => ClientCase::RISK_HIGH,

                'risk_flag' => true,

                'risk_notes' => 'More frequent risk monitoring required.',
            ]
        );

    $response->assertRedirect();

    $case->refresh();

    expect(
        $case->status
    )->toBe(
        ClientCase::STATUS_ON_HOLD
    );

    expect(
        $case->summary
    )->toBe(
        'Updated case summary.'
    );

    expect(
        $case->risk_level
    )->toBe(
        ClientCase::RISK_HIGH
    );

    expect(
        $case->risk_flag
    )->toBeTrue();
});

it('allows the assigned counsellor to create a case goal', function (): void {
    $context =
        createClinicalCaseCounsellorContext();

    $case =
        ClientCase::factory()->create([
            'client_profile_id' => $context['client']->id,

            'counsellor_profile_id' => $context['counsellor']->id,

            'opened_by' => $context['user']->id,

            'status' => ClientCase::STATUS_OPEN,
        ]);

    $targetDate =
        now()
            ->addMonth()
            ->toDateString();

    $response = $this
        ->actingAs($context['user'])
        ->post(
            route(
                'counsellor.cases.goals.store',
                $case
            ),
            [
                'description' => 'Develop effective stress management strategies.',

                'target_date' => $targetDate,
            ]
        );

    $response->assertRedirect();

    $this->assertDatabaseHas(
        'case_goals',
        [
            'client_case_id' => $case->id,

            'description' => 'Develop effective stress management strategies.',

            'status' => 'active',

            'created_by' => $context['user']->id,
        ]
    );

    $goal =
        $case
            ->goals()
            ->latest('id')
            ->first();

    expect($goal)
        ->not
        ->toBeNull();

    expect(
        $goal->target_date->toDateString()
    )->toBe(
        $targetDate
    );
});

it('allows the assigned counsellor to create a follow up action', function (): void {
    $context =
        createClinicalCaseCounsellorContext();

    $case =
        ClientCase::factory()->create([
            'client_profile_id' => $context['client']->id,

            'counsellor_profile_id' => $context['counsellor']->id,

            'opened_by' => $context['user']->id,

            'status' => ClientCase::STATUS_OPEN,
        ]);

    $dueAt =
        now()
            ->addWeek()
            ->setSecond(0);

    $response = $this
        ->actingAs($context['user'])
        ->post(
            route(
                'counsellor.cases.follow-ups.store',
                $case
            ),
            [
                'description' => 'Review coping plan at the next session.',

                'due_at' => $dueAt->format(
                    'Y-m-d H:i:s'
                ),
            ]
        );

    $response->assertRedirect();

    $this->assertDatabaseHas(
        'case_follow_ups',
        [
            'client_case_id' => $case->id,

            'description' => 'Review coping plan at the next session.',

            'status' => 'pending',

            'created_by' => $context['user']->id,
        ]
    );
});

it('allows a counsellor to update a goal status and completion information', function (): void {
    $context =
        createClinicalCaseCounsellorContext();

    $case =
        ClientCase::factory()->create([
            'client_profile_id' => $context['client']->id,

            'counsellor_profile_id' => $context['counsellor']->id,

            'opened_by' => $context['user']->id,
        ]);

    $goal =
        $case
            ->goals()
            ->create([
                'description' => 'Improve sleep routine.',

                'target_date' => now()
                    ->addMonth()
                    ->toDateString(),

                'status' => 'active',

                'created_by' => $context['user']->id,
            ]);

    $response = $this
        ->actingAs($context['user'])
        ->patch(
            route(
                'counsellor.cases.goals.update',
                [
                    $case,
                    $goal,
                ]
            ),
            [
                'description' => 'Improve sleep routine.',

                'target_date' => now()
                    ->addMonth()
                    ->toDateString(),

                'status' => 'achieved',

                'outcome_note' => 'Client reports consistent improvement.',
            ]
        );

    $response->assertRedirect();

    $goal->refresh();

    expect(
        $goal->status
    )->toBe('achieved');

    expect(
        $goal->completed_at
    )->not->toBeNull();

    expect(
        $goal->outcome_note
    )->toBe(
        'Client reports consistent improvement.'
    );
});

it('allows a counsellor to complete a follow up action', function (): void {
    $context =
        createClinicalCaseCounsellorContext();

    $case =
        ClientCase::factory()->create([
            'client_profile_id' => $context['client']->id,

            'counsellor_profile_id' => $context['counsellor']->id,

            'opened_by' => $context['user']->id,
        ]);

    $followUp =
        $case
            ->followUps()
            ->create([
                'description' => 'Contact client after one week.',

                'due_at' => now()->addWeek(),

                'status' => 'pending',

                'created_by' => $context['user']->id,
            ]);

    $response = $this
        ->actingAs($context['user'])
        ->patch(
            route(
                'counsellor.cases.follow-ups.update',
                [
                    $case,
                    $followUp,
                ]
            ),
            [
                'description' => 'Contact client after one week.',

                'due_at' => now()
                    ->addWeek()
                    ->format(
                        'Y-m-d H:i:s'
                    ),

                'status' => 'completed',
            ]
        );

    $response->assertRedirect();

    $followUp->refresh();

    expect(
        $followUp->status
    )->toBe('completed');

    expect(
        $followUp->completed_by
    )->toBe(
        $context['user']->id
    );

    expect(
        $followUp->completed_at
    )->not->toBeNull();
});

it('logs access when the assigned counsellor reads a confidential case', function (): void {
    $context =
        createClinicalCaseCounsellorContext();

    $case =
        ClientCase::factory()->create([
            'client_profile_id' => $context['client']->id,

            'counsellor_profile_id' => $context['counsellor']->id,

            'opened_by' => $context['user']->id,
        ]);

    $response = $this
        ->actingAs($context['user'])
        ->get(
            route(
                'counsellor.cases.show',
                $case
            )
        );

    $response->assertOk();

    $this->assertDatabaseHas(
        'clinical_record_access_logs',
        [
            'actor_id' => $context['user']->id,

            'client_case_id' => $case->id,

            'record_type' => 'case',

            'record_id' => $case->id,

            'action' => 'read',
        ]
    );
});

it('closes a clinical case and records who closed it', function (): void {
    $context =
        createClinicalCaseCounsellorContext();

    $case =
        ClientCase::factory()->create([
            'client_profile_id' => $context['client']->id,

            'counsellor_profile_id' => $context['counsellor']->id,

            'opened_by' => $context['user']->id,

            'status' => ClientCase::STATUS_OPEN,
        ]);

    $response = $this
        ->actingAs($context['user'])
        ->patch(
            route(
                'counsellor.cases.close',
                $case
            )
        );

    $response->assertRedirect(
        route(
            'counsellor.cases.index'
        )
    );

    $case->refresh();

    expect(
        $case->status
    )->toBe(
        ClientCase::STATUS_CLOSED
    );

    expect(
        $case->closed_by
    )->toBe(
        $context['user']->id
    );

    expect(
        $case->closed_at
    )->not->toBeNull();
});

it('does not allow changes to a closed clinical case', function (): void {
    $context =
        createClinicalCaseCounsellorContext();

    $case =
        ClientCase::factory()->create([
            'client_profile_id' => $context['client']->id,

            'counsellor_profile_id' => $context['counsellor']->id,

            'opened_by' => $context['user']->id,

            'status' => ClientCase::STATUS_CLOSED,

            'closed_by' => $context['user']->id,

            'closed_at' => now(),
        ]);

    $this
        ->actingAs($context['user'])
        ->post(
            route(
                'counsellor.cases.goals.store',
                $case
            ),
            [
                'description' => 'This goal must not be created.',

                'target_date' => null,
            ]
        )
        ->assertStatus(422);

    $this->assertDatabaseMissing(
        'case_goals',
        [
            'client_case_id' => $case->id,

            'description' => 'This goal must not be created.',
        ]
    );
});

it('does not allow users without clinical record permission to access counsellor cases', function (): void {
    $user =
        User::factory()->create();

    $this
        ->actingAs($user)
        ->get(
            route(
                'counsellor.cases.index'
            )
        )
        ->assertForbidden();
});
