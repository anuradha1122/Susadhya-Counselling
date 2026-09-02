<?php

use App\Models\ClientCase;
use App\Models\ClientProfile;
use App\Models\CounsellorProfile;
use App\Models\SecureDocument;
use App\Models\User;
use Database\Seeders\ClinicalRecordPermissionSeeder;
use Database\Seeders\DocumentPermissionSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed([
        RolePermissionSeeder::class,
        ClinicalRecordPermissionSeeder::class,
        DocumentPermissionSeeder::class,
    ]);

    Storage::fake('local');

    config()->set(
        'documents.disk',
        'local'
    );

    config()->set(
        'documents.allow_unscanned_downloads',
        true
    );
});

function makeSupervisorDocumentContext(): array
{
    $supervisor =
        User::factory()->create([
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

    $supervisor->assignRole(
        'clinical_supervisor'
    );

    $counsellorUser =
        User::factory()->create([
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

    $counsellorUser->assignRole(
        'counsellor'
    );

    $counsellor =
        CounsellorProfile::factory()->create([
            'user_id' => $counsellorUser->id,
        ]);

    $clientUser =
        User::factory()->create([
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

    $clientUser->assignRole(
        'client'
    );

    $clientProfile =
        ClientProfile::factory()->create([
            'user_id' => $clientUser->id,
        ]);

    $case =
        ClientCase::factory()->create([
            'client_profile_id' => $clientProfile->id,

            'counsellor_profile_id' => $counsellor->id,

            'opened_by' => $counsellorUser->id,

            'status' => ClientCase::STATUS_OPEN,
        ]);

    return [
        'supervisor' => $supervisor,

        'counsellorUser' => $counsellorUser,

        'counsellor' => $counsellor,

        'clientUser' => $clientUser,

        'clientProfile' => $clientProfile,

        'case' => $case,
    ];
}

it('allows a clinical supervisor to view clinical document index', function (): void {
    $context =
        makeSupervisorDocumentContext();

    SecureDocument::factory()->create([
        'client_profile_id' => $context['clientProfile']->id,

        'client_case_id' => $context['case']->id,

        'uploaded_by' => $context['counsellorUser']->id,

        'category' => SecureDocument::CATEGORY_CLINICAL,

        'access_scope' => SecureDocument::SCOPE_CLINICAL,

        'scan_status' => SecureDocument::SCAN_CLEAN,
    ]);

    $this
        ->actingAs(
            $context['supervisor']
        )
        ->get(
            route(
                'clinical-supervisor.documents.index'
            )
        )
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component(
                    'ClinicalSupervisor/Documents/Index'
                )
                ->has(
                    'documents.data',
                    1
                )
        );
});

it('allows a clinical supervisor to download a clinical document and logs access', function (): void {
    $context =
        makeSupervisorDocumentContext();

    $document =
        SecureDocument::factory()->create([
            'client_profile_id' => $context['clientProfile']->id,

            'client_case_id' => $context['case']->id,

            'uploaded_by' => $context['counsellorUser']->id,

            'category' => SecureDocument::CATEGORY_CLINICAL,

            'access_scope' => SecureDocument::SCOPE_CLINICAL,

            'scan_status' => SecureDocument::SCAN_CLEAN,

            'disk' => 'local',

            'path' => 'documents/private/tests/supervisor-review.pdf',

            'original_name' => 'supervisor-review.pdf',
        ]);

    Storage::disk('local')->put(
        $document->path,
        'supervisor document content'
    );

    $response = $this
        ->actingAs(
            $context['supervisor']
        )
        ->get(
            route(
                'clinical-supervisor.documents.download',
                $document
            )
        );

    $response
        ->assertOk()
        ->assertDownload(
            'supervisor-review.pdf'
        );

    $this->assertDatabaseHas(
        'document_access_logs',
        [
            'actor_id' => $context['supervisor']->id,

            'secure_document_id' => $document->id,

            'document_uuid' => $document->uuid,

            'action' => 'supervisor_download',
        ]
    );
});

it('blocks an ordinary user from clinical supervisor documents', function (): void {
    $user =
        User::factory()->create([
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

    $this
        ->actingAs($user)
        ->get(
            route(
                'clinical-supervisor.documents.index'
            )
        )
        ->assertForbidden();
});

it('does not expose document upload or delete routes to clinical supervisors', function (): void {
    expect(
        Route::has(
            'clinical-supervisor.documents.store'
        )
    )->toBeFalse();

    expect(
        Route::has(
            'clinical-supervisor.documents.destroy'
        )
    )->toBeFalse();
});

it('prevents a clinical supervisor from downloading admin only documents', function (): void {
    $context =
        makeSupervisorDocumentContext();

    $document =
        SecureDocument::factory()->create([
            'client_profile_id' => $context['clientProfile']->id,

            'uploaded_by' => $context['counsellorUser']->id,

            'category' => SecureDocument::CATEGORY_ADMINISTRATIVE,

            'access_scope' => SecureDocument::SCOPE_ADMIN,

            'scan_status' => SecureDocument::SCAN_CLEAN,

            'disk' => 'local',

            'path' => 'documents/private/tests/admin-only.pdf',
        ]);

    Storage::disk('local')->put(
        $document->path,
        'admin only content'
    );

    $this
        ->actingAs(
            $context['supervisor']
        )
        ->get(
            route(
                'clinical-supervisor.documents.download',
                $document
            )
        )
        ->assertForbidden();
});

it('blocks quarantined clinical documents from supervisor download', function (): void {
    $context =
        makeSupervisorDocumentContext();

    $document =
        SecureDocument::factory()->create([
            'client_profile_id' => $context['clientProfile']->id,

            'client_case_id' => $context['case']->id,

            'uploaded_by' => $context['counsellorUser']->id,

            'category' => SecureDocument::CATEGORY_CLINICAL,

            'access_scope' => SecureDocument::SCOPE_CLINICAL,

            'scan_status' => SecureDocument::SCAN_QUARANTINED,

            'quarantined_at' => now(),
        ]);

    $this
        ->actingAs(
            $context['supervisor']
        )
        ->get(
            route(
                'clinical-supervisor.documents.download',
                $document
            )
        )
        ->assertStatus(423);
});
