<?php

use App\Models\PrivacyRequest;
use App\Models\User;
use App\Services\Compliance\PrivacyExportService;
use Database\Seeders\CompliancePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->withoutVite();

    $this->seed(
        CompliancePermissionSeeder::class
    );

    app(
        PermissionRegistrar::class
    )->forgetCachedPermissions();

    Storage::fake('local');

    config()->set(
        'compliance.privacy_export_disk',
        'local'
    );

    config()->set(
        'compliance.privacy_export_directory',
        'privacy-exports'
    );

    config()->set(
        'compliance.privacy_export_ttl_days',
        14
    );
});

it(
    'creates a reviewed privacy export without exposing account secrets',
    function (): void {
        $client =
            User::factory()->create([
                'is_active' => true,
            ]);

        $client->assignRole('client');

        $officer =
            User::factory()->create([
                'is_active' => true,
            ]);

        $officer->assignRole(
            'privacy_officer'
        );

        $privacyRequest =
            PrivacyRequest::factory()
                ->forUser($client)
                ->create([
                    'type' => PrivacyRequest::TYPE_EXPORT,

                    'status' => PrivacyRequest::STATUS_APPROVED,

                    'decision' => 'approved',

                    'reviewed_at' => now(),

                    'reviewed_by' => $officer->id,
                ]);

        $result =
            app(
                PrivacyExportService::class
            )->prepare(
                $privacyRequest,
                $officer
            );

        $result->refresh();

        expect(
            $result->status
        )->toBe(
            PrivacyRequest::STATUS_EXPORT_READY
        );

        expect(
            $result->export_checksum
        )->not->toBeNull();

        Storage::disk('local')
            ->assertExists(
                $result->export_path
            );

        $json =
            Storage::disk('local')
                ->get(
                    $result->export_path
                );

        expect($json)
            ->toContain(
                $client->email
            )
            ->not
            ->toContain(
                $client->password
            )
            ->not
            ->toContain(
                'remember_token'
            )
            ->not
            ->toContain(
                'gateway_payload'
            );
    }
);

it(
    'prevents one client from cancelling another clients privacy request',
    function (): void {
        $owner =
            User::factory()->create([
                'is_active' => true,
            ]);

        $owner->assignRole('client');

        $otherClient =
            User::factory()->create([
                'is_active' => true,
            ]);

        $otherClient->assignRole(
            'client'
        );

        $privacyRequest =
            PrivacyRequest::factory()
                ->forUser($owner)
                ->create([
                    'status' => PrivacyRequest::STATUS_SUBMITTED,
                ]);

        $this
            ->actingAs($otherClient)
            ->patch(
                route(
                    'client.privacy-requests.cancel',
                    $privacyRequest
                )
            )
            ->assertForbidden();

        expect(
            $privacyRequest
                ->refresh()
                ->status
        )->toBe(
            PrivacyRequest::STATUS_SUBMITTED
        );
    }
);

it(
    'purges expired temporary privacy exports',
    function (): void {
        $client =
            User::factory()->create([
                'is_active' => true,
            ]);

        $client->assignRole('client');

        $path =
            'privacy-exports/test-export.json';

        Storage::disk('local')
            ->put(
                $path,
                '{"test":true}'
            );

        $privacyRequest =
            PrivacyRequest::factory()
                ->forUser($client)
                ->create([
                    'status' => PrivacyRequest::STATUS_COMPLETED,

                    'export_disk' => 'local',

                    'export_path' => $path,

                    'export_checksum' => hash(
                        'sha256',
                        '{"test":true}'
                    ),

                    'export_prepared_at' => now()->subDays(15),
                ]);

        $count =
            app(
                PrivacyExportService::class
            )->purgeExpiredExports();

        expect($count)->toBe(1);

        Storage::disk('local')
            ->assertMissing($path);

        $privacyRequest->refresh();

        expect(
            $privacyRequest->export_path
        )->toBeNull();

        expect(
            $privacyRequest->export_disk
        )->toBeNull();
    }
);
