<?php

use App\Models\ClientCase;
use App\Models\ClientProfile;
use App\Models\ClinicalNote;
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

it('versions and locks a clinical note', function (): void {
    $user = User::factory()->create();

    $user->assignRole('counsellor');

    $counsellor =
        CounsellorProfile::factory()->create([
            'user_id' => $user->id,
        ]);

    $client =
        ClientProfile::factory()->create();

    $session =
        CounsellingSession::factory()
            ->completed($user)
            ->create([
                'client_profile_id' => $client->id,

                'counsellor_profile_id' => $counsellor->id,
            ]);

    $case = ClientCase::factory()->create([
        'client_profile_id' => $client->id,
        'counsellor_profile_id' => $counsellor->id,
        'opened_by' => $user->id,
    ]);

    $this
        ->actingAs($user)
        ->post(
            route(
                'counsellor.cases.notes.store',
                $case,
            ),
            [
                'counselling_session_id' => $session->id,
                'title' => 'Clinical progress note',
                'note' => 'Initial clinical documentation.',
                'formulation' => 'Initial formulation.',
                'intervention' => 'Supportive intervention.',
                'risk_assessment' => 'No immediate safety risk.',
                'plan' => 'Review at next session.',
                'risk_level' => 'low',
            ]
        )
        ->assertRedirect();

    $note = ClinicalNote::firstOrFail();

    expect($note->version)->toBe(1);

    expect(
        $note->versions()->count()
    )->toBe(1);

    $this
        ->actingAs($user)
        ->patch(
            route(
                'counsellor.cases.notes.update',
                [
                    $case,
                    $note,
                ],
            ),
            [
                'title' => 'Clinical progress note',
                'note' => 'Updated clinical documentation.',
                'formulation' => 'Updated formulation.',
                'intervention' => 'Supportive intervention.',
                'risk_assessment' => 'No immediate safety risk.',
                'plan' => 'Review at next session.',
                'risk_level' => 'moderate',
                'change_reason' => 'Updated after clinical review.',
            ]
        )
        ->assertRedirect();

    $note->refresh();

    expect($note->version)->toBe(2);

    expect(
        $note->versions()->count()
    )->toBe(2);

    $this
        ->actingAs($user)
        ->patch(
            route(
                'counsellor.cases.notes.sign',
                [
                    $case,
                    $note,
                ],
            )
        )
        ->assertRedirect();

    $note->refresh();

    expect(
        $note->status
    )->toBe(
        ClinicalNote::STATUS_SIGNED
    );

    expect(
        $note->locked_at
    )->not->toBeNull();

    expect(
        $note->signed_at
    )->not->toBeNull();

    expect(
        $note->versions()->count()
    )->toBe(3);

    $this
        ->actingAs($user)
        ->patch(
            route(
                'counsellor.cases.notes.update',
                [
                    $case,
                    $note,
                ],
            ),
            [
                'title' => 'Illegal update',
                'note' => 'Should not change.',
                'formulation' => null,
                'intervention' => null,
                'risk_assessment' => null,
                'plan' => null,
                'risk_level' => 'low',
                'change_reason' => null,
            ]
        )
        ->assertStatus(422);
});
