<?php

use App\Models\ClientProfile;
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

function makeClientDocumentContext(): array
{
    $user = User::factory()->create([
        'is_active' => true,
        'email_verified_at' => now(),
    ]);

    $user->assignRole('client');

    $clientProfile =
        ClientProfile::factory()->create([
            'user_id' => $user->id,
        ]);

    return [
        'user' => $user,
        'clientProfile' => $clientProfile,
    ];
}

it('allows a client to upload a private pdf document', function (): void {
    $context =
        makeClientDocumentContext();

    $file =
        UploadedFile::fake()->create(
            'consent-form.pdf',
            100,
            'application/pdf'
        );

    $response = $this
        ->actingAs(
            $context['user']
        )
        ->post(
            route(
                'client.documents.store'
            ),
            [
                'title' => 'Signed Consent Form',

                'category' => SecureDocument::CATEGORY_CONSENT,

                'access_scope' => SecureDocument::SCOPE_CLIENT,

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
        $document->client_profile_id
    )->toBe(
        $context['clientProfile']->id
    );

    expect(
        $document->uploaded_by
    )->toBe(
        $context['user']->id
    );

    expect(
        $document->category
    )->toBe(
        SecureDocument::CATEGORY_CONSENT
    );

    expect(
        $document->access_scope
    )->toBe(
        SecureDocument::SCOPE_CLIENT
    );

    expect(
        $document->original_name
    )->toBe(
        'consent-form.pdf'
    );

    expect(
        $document->stored_name
    )->not->toBe(
        $document->original_name
    );

    expect(
        $document->sha256
    )->toHaveLength(64);

    expect(
        $document->scan_status
    )->toBe(
        SecureDocument::SCAN_UNAVAILABLE
    );

    Storage::disk('local')
        ->assertExists(
            $document->path
        );

    $this->assertDatabaseHas(
        'document_access_logs',
        [
            'actor_id' => $context['user']->id,

            'secure_document_id' => $document->id,

            'document_uuid' => $document->uuid,

            'action' => 'upload',
        ]
    );
});

it('allows a client to download their own private document and logs access', function (): void {
    $context =
        makeClientDocumentContext();

    $document =
        SecureDocument::factory()->create([
            'client_profile_id' => $context['clientProfile']->id,

            'uploaded_by' => $context['user']->id,

            'access_scope' => SecureDocument::SCOPE_CLIENT,

            'scan_status' => SecureDocument::SCAN_CLEAN,

            'disk' => 'local',

            'path' => 'documents/private/tests/client-document.pdf',

            'original_name' => 'client-document.pdf',
        ]);

    Storage::disk('local')->put(
        $document->path,
        'fake pdf content'
    );

    $response = $this
        ->actingAs(
            $context['user']
        )
        ->get(
            route(
                'client.documents.download',
                $document
            )
        );

    $response
        ->assertOk()
        ->assertDownload(
            'client-document.pdf'
        );

    $this->assertDatabaseHas(
        'document_access_logs',
        [
            'actor_id' => $context['user']->id,

            'secure_document_id' => $document->id,

            'document_uuid' => $document->uuid,

            'action' => 'download',
        ]
    );
});

it('prevents another client from downloading a private document', function (): void {
    $owner =
        makeClientDocumentContext();

    $otherClient =
        makeClientDocumentContext();

    $document =
        SecureDocument::factory()->create([
            'client_profile_id' => $owner['clientProfile']->id,

            'uploaded_by' => $owner['user']->id,

            'access_scope' => SecureDocument::SCOPE_CLIENT,

            'scan_status' => SecureDocument::SCAN_CLEAN,

            'disk' => 'local',

            'path' => 'documents/private/tests/private-client.pdf',
        ]);

    Storage::disk('local')->put(
        $document->path,
        'private content'
    );

    $this
        ->actingAs(
            $otherClient['user']
        )
        ->get(
            route(
                'client.documents.download',
                $document
            )
        )
        ->assertForbidden();

    $this->assertDatabaseMissing(
        'document_access_logs',
        [
            'actor_id' => $otherClient['user']->id,

            'secure_document_id' => $document->id,

            'action' => 'download',
        ]
    );
});

it('rejects unsupported executable uploads', function (): void {
    $context =
        makeClientDocumentContext();

    $file =
        UploadedFile::fake()->create(
            'dangerous.exe',
            100,
            'application/x-msdownload'
        );

    $response = $this
        ->actingAs(
            $context['user']
        )
        ->post(
            route(
                'client.documents.store'
            ),
            [
                'title' => 'Invalid File',

                'category' => SecureDocument::CATEGORY_OTHER,

                'access_scope' => SecureDocument::SCOPE_CLIENT,

                'file' => $file,
            ]
        );

    $response
        ->assertSessionHasErrors(
            'file'
        );

    expect(
        SecureDocument::query()->count()
    )->toBe(0);
});

it('prevents a client from downloading a quarantined document', function (): void {
    $context =
        makeClientDocumentContext();

    $document =
        SecureDocument::factory()->create([
            'client_profile_id' => $context['clientProfile']->id,

            'uploaded_by' => $context['user']->id,

            'access_scope' => SecureDocument::SCOPE_CLIENT,

            'scan_status' => SecureDocument::SCAN_QUARANTINED,

            'quarantined_at' => now(),
        ]);

    $this
        ->actingAs(
            $context['user']
        )
        ->get(
            route(
                'client.documents.download',
                $document
            )
        )
        ->assertStatus(423);
});

it('allows a client to remove a document they uploaded', function (): void {
    $context =
        makeClientDocumentContext();

    $document =
        SecureDocument::factory()->create([
            'client_profile_id' => $context['clientProfile']->id,

            'uploaded_by' => $context['user']->id,

            'access_scope' => SecureDocument::SCOPE_CLIENT,

            'scan_status' => SecureDocument::SCAN_CLEAN,

            'disk' => 'local',

            'path' => 'documents/private/tests/delete-client.pdf',
        ]);

    Storage::disk('local')->put(
        $document->path,
        'delete test'
    );

    $response = $this
        ->actingAs(
            $context['user']
        )
        ->delete(
            route(
                'client.documents.destroy',
                $document
            )
        );

    $response
        ->assertRedirect()
        ->assertSessionHas(
            'success'
        );

    $this->assertSoftDeleted(
        'secure_documents',
        [
            'id' => $document->id,

            'deleted_by' => $context['user']->id,
        ]
    );

    /*
     * Physical file deliberately remains.
     * Permanent destruction belongs to M18.
     */
    Storage::disk('local')
        ->assertExists(
            $document->path
        );

    $this->assertDatabaseHas(
        'document_access_logs',
        [
            'actor_id' => $context['user']->id,

            'secure_document_id' => $document->id,

            'action' => 'delete',
        ]
    );
});
