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
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

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
        'documents.scanner.driver',
        'none'
    );

    config()->set(
        'documents.allow_unscanned_downloads',
        true
    );
});

function makeCounsellorCaseDocumentContext(): array
{
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
        'counsellorUser' => $counsellorUser,

        'counsellor' => $counsellor,

        'clientUser' => $clientUser,

        'clientProfile' => $clientProfile,

        'case' => $case,
    ];
}

it('allows the assigned counsellor to upload a clinical case document', function (): void {
    $context =
        makeCounsellorCaseDocumentContext();

    $file =
        UploadedFile::fake()->create(
            'clinical-report.pdf',
            150,
            'application/pdf'
        );

    $response = $this
        ->actingAs(
            $context['counsellorUser']
        )
        ->post(
            route(
                'counsellor.documents.store',
                $context['case']
            ),
            [
                'title' => 'Clinical Assessment Report',

                'category' => SecureDocument::CATEGORY_CLINICAL,

                'access_scope' => SecureDocument::SCOPE_CLINICAL,

                'file' => $file,
            ]
        );

    $response
        ->assertRedirect()
        ->assertSessionHas(
            'success'
        );

    $document =
        SecureDocument::query()
            ->first();

    expect($document)
        ->not
        ->toBeNull();

    expect(
        $document->client_case_id
    )->toBe(
        $context['case']->id
    );

    expect(
        $document->client_profile_id
    )->toBe(
        $context['clientProfile']->id
    );

    expect(
        $document->uploaded_by
    )->toBe(
        $context['counsellorUser']->id
    );

    expect(
        $document->category
    )->toBe(
        SecureDocument::CATEGORY_CLINICAL
    );

    expect(
        $document->access_scope
    )->toBe(
        SecureDocument::SCOPE_CLINICAL
    );

    Storage::disk('local')
        ->assertExists(
            $document->path
        );

    $this->assertDatabaseHas(
        'document_access_logs',
        [
            'actor_id' => $context['counsellorUser']->id,

            'secure_document_id' => $document->id,

            'action' => 'upload',
        ]
    );
});

it('allows the assigned counsellor to download a clinical case document', function (): void {
    $context =
        makeCounsellorCaseDocumentContext();

    $document =
        SecureDocument::factory()->create([
            'client_profile_id' => $context['clientProfile']->id,

            'client_case_id' => $context['case']->id,

            'uploaded_by' => $context['counsellorUser']->id,

            'category' => SecureDocument::CATEGORY_CLINICAL,

            'access_scope' => SecureDocument::SCOPE_CLINICAL,

            'scan_status' => SecureDocument::SCAN_CLEAN,

            'disk' => 'local',

            'path' => 'documents/private/tests/case-document.pdf',

            'original_name' => 'case-document.pdf',
        ]);

    Storage::disk('local')->put(
        $document->path,
        'clinical document content'
    );

    $response = $this
        ->actingAs(
            $context['counsellorUser']
        )
        ->get(
            route(
                'counsellor.documents.download',
                $document
            )
        );

    $response
        ->assertOk()
        ->assertDownload(
            'case-document.pdf'
        );

    $this->assertDatabaseHas(
        'document_access_logs',
        [
            'actor_id' => $context['counsellorUser']->id,

            'secure_document_id' => $document->id,

            'action' => 'download',
        ]
    );
});

it('prevents another counsellor from uploading to an unassigned case', function (): void {
    $context =
        makeCounsellorCaseDocumentContext();

    $otherUser =
        User::factory()->create([
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

    $otherUser->assignRole(
        'counsellor'
    );

    CounsellorProfile::factory()->create([
        'user_id' => $otherUser->id,
    ]);

    $file =
        UploadedFile::fake()->create(
            'unauthorised.pdf',
            100,
            'application/pdf'
        );

    $this
        ->actingAs(
            $otherUser
        )
        ->post(
            route(
                'counsellor.documents.store',
                $context['case']
            ),
            [
                'title' => 'Unauthorised',

                'category' => SecureDocument::CATEGORY_CLINICAL,

                'access_scope' => SecureDocument::SCOPE_CLINICAL,

                'file' => $file,
            ]
        )
        ->assertForbidden();

    expect(
        SecureDocument::query()->count()
    )->toBe(0);
});

it('prevents another counsellor from downloading a clinical document', function (): void {
    $context =
        makeCounsellorCaseDocumentContext();

    $otherUser =
        User::factory()->create([
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

    $otherUser->assignRole(
        'counsellor'
    );

    CounsellorProfile::factory()->create([
        'user_id' => $otherUser->id,
    ]);

    $document =
        SecureDocument::factory()->create([
            'client_profile_id' => $context['clientProfile']->id,

            'client_case_id' => $context['case']->id,

            'uploaded_by' => $context['counsellorUser']->id,

            'category' => SecureDocument::CATEGORY_CLINICAL,

            'access_scope' => SecureDocument::SCOPE_CLINICAL,

            'scan_status' => SecureDocument::SCAN_CLEAN,

            'disk' => 'local',

            'path' => 'documents/private/tests/private-clinical.pdf',
        ]);

    Storage::disk('local')->put(
        $document->path,
        'private clinical content'
    );

    $this
        ->actingAs(
            $otherUser
        )
        ->get(
            route(
                'counsellor.documents.download',
                $document
            )
        )
        ->assertForbidden();
});

it('does not allow uploads to a closed clinical case', function (): void {
    $context =
        makeCounsellorCaseDocumentContext();

    $context['case']->update([
        'status' => ClientCase::STATUS_CLOSED,

        'closed_at' => now(),

        'closed_by' => $context['counsellorUser']->id,
    ]);

    $file =
        UploadedFile::fake()->create(
            'closed-case.pdf',
            100,
            'application/pdf'
        );

    $this
        ->actingAs(
            $context['counsellorUser']
        )
        ->post(
            route(
                'counsellor.documents.store',
                $context['case']
            ),
            [
                'title' => 'Closed Case Document',

                'category' => SecureDocument::CATEGORY_CLINICAL,

                'access_scope' => SecureDocument::SCOPE_CLINICAL,

                'file' => $file,
            ]
        )
        ->assertStatus(422);

    expect(
        SecureDocument::query()->count()
    )->toBe(0);
});

it('blocks downloading a quarantined clinical document', function (): void {
    $context =
        makeCounsellorCaseDocumentContext();

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
            $context['counsellorUser']
        )
        ->get(
            route(
                'counsellor.documents.download',
                $document
            )
        )
        ->assertStatus(423);
});
