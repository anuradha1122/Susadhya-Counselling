<?php

use App\Models\PrivacyRequest;
use App\Models\User;
use App\Services\Compliance\PrivacyRequestService;
use Database\Seeders\CompliancePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
});

it(
    'allows a client to submit their own privacy request',
    function (): void {
        $client =
            User::factory()->create([
                'is_active' => true,
            ]);

        $client->assignRole('client');

        $response =
            $this
                ->actingAs($client)
                ->post(
                    route(
                        'client.privacy-requests.store'
                    ),
                    [
                        'type' => PrivacyRequest::TYPE_EXPORT,

                        'request_details' => 'Please provide an export of my personal information.',

                        'scope' => [
                            'account',
                            'appointments',
                        ],
                    ]
                );

        $response
            ->assertSessionHasNoErrors();

        $privacyRequest =
            PrivacyRequest::query()
                ->firstOrFail();

        expect(
            $privacyRequest
                ->requester_user_id
        )->toBe($client->id);

        expect(
            $privacyRequest
                ->subject_user_id
        )->toBe($client->id);

        expect(
            $privacyRequest->status
        )->toBe(
            PrivacyRequest::STATUS_SUBMITTED
        );

        expect(
            $privacyRequest
                ->request_details
        )->toBe(
            'Please provide an export of my personal information.'
        );

        $this->assertDatabaseHas(
            'privacy_request_events',
            [
                'privacy_request_id' => $privacyRequest->id,

                'event' => 'submitted',

                'to_status' => PrivacyRequest::STATUS_SUBMITTED,
            ]
        );
    }
);

it(
    'supports the privacy request review workflow',
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
            app(
                PrivacyRequestService::class
            )->submit(
                $client,
                [
                    'type' => PrivacyRequest::TYPE_EXPORT,

                    'request_details' => 'Export my data.',

                    'scope' => [
                        'account',
                    ],
                ]
            );

        $this
            ->actingAs($officer)
            ->post(
                route(
                    'compliance.privacy-requests.verify-identity',
                    $privacyRequest
                ),
                [
                    'notes' => 'Identity confirmed against authenticated account.',
                ]
            )
            ->assertSessionHasNoErrors();

        expect(
            $privacyRequest
                ->refresh()
                ->status
        )->toBe(
            PrivacyRequest::STATUS_IDENTITY_VERIFIED
        );

        $this
            ->actingAs($officer)
            ->post(
                route(
                    'compliance.privacy-requests.begin-review',
                    $privacyRequest
                )
            )
            ->assertSessionHasNoErrors();

        expect(
            $privacyRequest
                ->refresh()
                ->status
        )->toBe(
            PrivacyRequest::STATUS_UNDER_REVIEW
        );

        $this
            ->actingAs($officer)
            ->patch(
                route(
                    'compliance.privacy-requests.decision',
                    $privacyRequest
                ),
                [
                    'decision' => 'approved',

                    'review_notes' => 'Request approved after review.',

                    'legal_basis' => 'Approved data subject access request.',
                ]
            )
            ->assertSessionHasNoErrors();

        $privacyRequest->refresh();

        expect(
            $privacyRequest->status
        )->toBe(
            PrivacyRequest::STATUS_APPROVED
        );

        expect(
            $privacyRequest->decision
        )->toBe('approved');

        expect(
            $privacyRequest->reviewed_by
        )->toBe($officer->id);

        $this->assertDatabaseHas(
            'audit_events',
            [
                'event' => 'privacy_request.approved',

                'actor_id' => $officer->id,
            ]
        );
    }
);

it(
    'does not automatically destroy client data when a deletion request is completed',
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

        $service =
            app(
                PrivacyRequestService::class
            );

        $privacyRequest =
            $service->submit(
                $client,
                [
                    'type' => PrivacyRequest::TYPE_DELETION,

                    'request_details' => 'Please review deletion of my personal data.',
                ]
            );

        $service->verifyIdentity(
            $privacyRequest,
            $officer,
            'Identity verified.'
        );

        $service->beginReview(
            $privacyRequest,
            $officer
        );

        $service->decide(
            privacyRequest: $privacyRequest,
            actor: $officer,
            decision: 'approved',
            reviewNotes: 'Deletion reviewed.',
            legalBasis: 'Retention obligations assessed.',
        );

        $service->complete(
            privacyRequest: $privacyRequest,
            actor: $officer,
            executionNotes: 'Clinical and financial records retained where required.',
            deletionStrategy: 'retained_due_to_legal_obligation',
        );

        $this->assertDatabaseHas(
            'users',
            [
                'id' => $client->id,
                'email' => $client->email,
            ]
        );

        expect(
            $privacyRequest
                ->refresh()
                ->status
        )->toBe(
            PrivacyRequest::STATUS_COMPLETED
        );
    }
);
