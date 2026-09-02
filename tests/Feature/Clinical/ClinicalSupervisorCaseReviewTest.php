<?php

use App\Models\ClientCase;
use App\Models\User;
use Database\Seeders\ClinicalRecordPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(
        ClinicalRecordPermissionSeeder::class
    );
});

it('allows clinical supervisor review and logs access', function (): void {
    $supervisor =
        User::factory()->create();

    $supervisor->assignRole(
        'clinical_supervisor'
    );

    $case =
        ClientCase::factory()->create();

    $this
        ->actingAs($supervisor)
        ->get(
            route(
                'clinical-supervisor.cases.show',
                $case,
            )
        )
        ->assertOk();

    $this->assertDatabaseHas(
        'clinical_record_access_logs',
        [
            'actor_id' => $supervisor->id,
            'client_case_id' => $case->id,
            'action' => 'supervisor_read',
        ]
    );
});

it('blocks ordinary users from clinical supervision', function (): void {
    $user =
        User::factory()->create();

    $case =
        ClientCase::factory()->create();

    $this
        ->actingAs($user)
        ->get(
            route(
                'clinical-supervisor.cases.show',
                $case,
            )
        )
        ->assertForbidden();
});
