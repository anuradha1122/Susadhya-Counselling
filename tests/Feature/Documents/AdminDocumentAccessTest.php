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
        'documents.scanner.driver',
        'none'
    );

    config()->set(
        'documents.allow_unscanned_downloads',
        true
    );
});

function makeAdminDocumentContext(): array
{
    $admin =
        User::factory()->create([
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

    $admin->assignRole(
        'admin'
    );

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

    return [
        'admin' => $admin,

        'clientUser' => $clientUser,

        'clientProfile' => $clientProfile,
    ];
}

it('allows an administrator to upload an administrative document', function (): void {
    $context =
        makeAdminDocumentContext();

    $file =
        UploadedFile::fake()->create(
            'administrative-report.pdf',
            100,
            'application/pdf'
        );

    $response = $this
        ->actingAs(
            $context['admin']
        )
        ->post(
            route(
                'admin.documents.store'
            ),
            [
                'client_profile_id' => $context['clientProfile']->id,

                'title' => 'Administrative Report',

                'category' => SecureDocument::CATEGORY_ADMINISTRATIVE,

                'access_scope' => SecureDocument::SCOPE_ADMIN,

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
        $context['admin']->id
    );

    expect(
        $document->category
    )->toBe(
        SecureDocument::CATEGORY_ADMINISTRATIVE
    );

    expect(
        $document->access_scope
    )->toBe(
        SecureDocument::SCOPE_ADMIN
    );

    Storage::disk('local')
        ->assertExists(
            $document->path
        );

    $this->assertDatabaseHas(
        'document_access_logs',
        [
            'actor_id' => $context['admin']->id,

            'secure_document_id' => $document->id,

            'action' => 'upload',
        ]
    );
});

it('shows only administrative and shared documents on the admin index', function (): void {
    $context =
        makeAdminDocumentContext();

    SecureDocument::factory()->create([
        'client_profile_id' => $context['clientProfile']->id,

        'uploaded_by' => $context['admin']->id,

        'access_scope' => SecureDocument::SCOPE_ADMIN,

        'category' => SecureDocument::CATEGORY_ADMINISTRATIVE,
    ]);

    SecureDocument::factory()->create([
        'client_profile_id' => $context['clientProfile']->id,

        'uploaded_by' => $context['admin']->id,

        'access_scope' => SecureDocument::SCOPE_SHARED,

        'category' => SecureDocument::CATEGORY_REPORT,
    ]);

    SecureDocument::factory()->create([
        'client_profile_id' => $context['clientProfile']->id,

        'uploaded_by' => $context['clientUser']->id,

        'access_scope' => SecureDocument::SCOPE_CLINICAL,

        'category' => SecureDocument::CATEGORY_CLINICAL,
    ]);

    $this
        ->actingAs(
            $context['admin']
        )
        ->get(
            route(
                'admin.documents.index'
            )
        )
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component(
                    'Admin/Documents/Index'
                )
                ->has(
                    'documents.data',
                    2
                )
        );
});

it('allows an administrator to download an admin document and logs access', function (): void {
    $context =
        makeAdminDocumentContext();

    $document =
        SecureDocument::factory()->create([
            'client_profile_id' => $context['clientProfile']->id,

            'uploaded_by' => $context['admin']->id,

            'category' => SecureDocument::CATEGORY_ADMINISTRATIVE,

            'access_scope' => SecureDocument::SCOPE_ADMIN,

            'scan_status' => SecureDocument::SCAN_CLEAN,

            'disk' => 'local',

            'path' => 'documents/private/tests/admin-report.pdf',

            'original_name' => 'admin-report.pdf',
        ]);

    Storage::disk('local')->put(
        $document->path,
        'administrative content'
    );

    $response = $this
        ->actingAs(
            $context['admin']
        )
        ->get(
            route(
                'admin.documents.download',
                $document
            )
        );

    $response
        ->assertOk()
        ->assertDownload(
            'admin-report.pdf'
        );

    $this->assertDatabaseHas(
        'document_access_logs',
        [
            'actor_id' => $context['admin']->id,

            'secure_document_id' => $document->id,

            'action' => 'download',
        ]
    );
});

it('prevents an ordinary administrator from downloading clinical only documents', function (): void {
    $context =
        makeAdminDocumentContext();

    $document =
        SecureDocument::factory()->create([
            'client_profile_id' => $context['clientProfile']->id,

            'uploaded_by' => $context['clientUser']->id,

            'category' => SecureDocument::CATEGORY_CLINICAL,

            'access_scope' => SecureDocument::SCOPE_CLINICAL,

            'scan_status' => SecureDocument::SCAN_CLEAN,

            'disk' => 'local',

            'path' => 'documents/private/tests/clinical-only.pdf',
        ]);

    Storage::disk('local')->put(
        $document->path,
        'confidential clinical content'
    );

    $this
        ->actingAs(
            $context['admin']
        )
        ->get(
            route(
                'admin.documents.download',
                $document
            )
        )
        ->assertForbidden();

    $this->assertDatabaseMissing(
        'document_access_logs',
        [
            'actor_id' => $context['admin']->id,

            'secure_document_id' => $document->id,

            'action' => 'download',
        ]
    );
});

it('blocks quarantined admin documents from download', function (): void {
    $context =
        makeAdminDocumentContext();

    $document =
        SecureDocument::factory()->create([
            'client_profile_id' => $context['clientProfile']->id,

            'uploaded_by' => $context['admin']->id,

            'category' => SecureDocument::CATEGORY_ADMINISTRATIVE,

            'access_scope' => SecureDocument::SCOPE_ADMIN,

            'scan_status' => SecureDocument::SCAN_QUARANTINED,

            'quarantined_at' => now(),
        ]);

    $this
        ->actingAs(
            $context['admin']
        )
        ->get(
            route(
                'admin.documents.download',
                $document
            )
        )
        ->assertStatus(423);
});

it('allows an administrator to soft delete an administrative document', function (): void {
    $context =
        makeAdminDocumentContext();

    $document =
        SecureDocument::factory()->create([
            'client_profile_id' => $context['clientProfile']->id,

            'uploaded_by' => $context['admin']->id,

            'category' => SecureDocument::CATEGORY_ADMINISTRATIVE,

            'access_scope' => SecureDocument::SCOPE_ADMIN,

            'scan_status' => SecureDocument::SCAN_CLEAN,

            'disk' => 'local',

            'path' => 'documents/private/tests/delete-admin.pdf',
        ]);

    Storage::disk('local')->put(
        $document->path,
        'retained physical content'
    );

    $response = $this
        ->actingAs(
            $context['admin']
        )
        ->delete(
            route(
                'admin.documents.destroy',
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

            'deleted_by' => $context['admin']->id,
        ]
    );

    Storage::disk('local')
        ->assertExists(
            $document->path
        );

    $this->assertDatabaseHas(
        'document_access_logs',
        [
            'actor_id' => $context['admin']->id,

            'secure_document_id' => $document->id,

            'action' => 'delete',
        ]
    );
});
