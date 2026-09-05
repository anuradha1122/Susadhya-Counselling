<?php

namespace App\Services\Compliance;

use App\Models\PrivacyRequest;
use App\Models\PrivacyRequestEvent;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PrivacyRequestService
{
    public function __construct(
        private readonly AuditLogger $auditLogger
    ) {}

    public function submit(
        User $user,
        array $data
    ): PrivacyRequest {
        return DB::transaction(
            function () use (
                $user,
                $data
            ): PrivacyRequest {
                $request =
                    PrivacyRequest::query()
                        ->create([
                            'requester_user_id' => $user->id,

                            'subject_user_id' => $user->id,

                            'type' => $data['type'],

                            'status' => PrivacyRequest::STATUS_SUBMITTED,

                            'request_details' => $data[
                                    'request_details'
                                ],

                            'scope' => $data['scope']
                                ?? null,

                            'submitted_at' => now(),
                        ]);

                $this->addEvent(
                    request: $request,
                    actor: $user,
                    event: 'submitted',
                    fromStatus: null,
                    toStatus: PrivacyRequest::STATUS_SUBMITTED,
                );

                $this->auditLogger->record(
                    category: 'privacy',
                    event: 'privacy_request.submitted',
                    action: 'create',
                    subject: $request,
                    actor: $user,
                    metadata: [
                        'type' => $request->type,

                        'status' => $request->status,
                    ],
                );

                return $request;
            }
        );
    }

    public function verifyIdentity(
        PrivacyRequest $privacyRequest,
        User $actor,
        ?string $notes = null
    ): PrivacyRequest {
        return DB::transaction(
            function () use (
                $privacyRequest,
                $actor,
                $notes
            ): PrivacyRequest {
                $request =
                    $this->lock(
                        $privacyRequest
                    );

                $this->assertStatus(
                    $request,
                    [
                        PrivacyRequest::STATUS_SUBMITTED,
                    ]
                );

                $from =
                    $request->status;

                $request->forceFill([
                    'status' => PrivacyRequest::STATUS_IDENTITY_VERIFIED,

                    'identity_verified_at' => now(),

                    'identity_verified_by' => $actor->id,
                ])->save();

                $this->addEvent(
                    request: $request,
                    actor: $actor,
                    event: 'identity_verified',
                    fromStatus: $from,
                    toStatus: $request->status,
                    notes: $notes,
                );

                $this->auditLogger->record(
                    category: 'privacy',
                    event: 'privacy_request.identity_verified',
                    action: 'verify_identity',
                    subject: $request,
                    actor: $actor,
                    metadata: [
                        'type' => $request->type,
                    ],
                );

                return $request->refresh();
            }
        );
    }

    public function beginReview(
        PrivacyRequest $privacyRequest,
        User $actor
    ): PrivacyRequest {
        return DB::transaction(
            function () use (
                $privacyRequest,
                $actor
            ): PrivacyRequest {
                $request =
                    $this->lock(
                        $privacyRequest
                    );

                $this->assertStatus(
                    $request,
                    [
                        PrivacyRequest::STATUS_IDENTITY_VERIFIED,
                    ]
                );

                $from =
                    $request->status;

                $request->forceFill([
                    'status' => PrivacyRequest::STATUS_UNDER_REVIEW,
                ])->save();

                $this->addEvent(
                    request: $request,
                    actor: $actor,
                    event: 'review_started',
                    fromStatus: $from,
                    toStatus: $request->status,
                );

                $this->auditLogger->record(
                    category: 'privacy',
                    event: 'privacy_request.review_started',
                    action: 'review',
                    subject: $request,
                    actor: $actor,
                );

                return $request->refresh();
            }
        );
    }

    public function decide(
        PrivacyRequest $privacyRequest,
        User $actor,
        string $decision,
        string $reviewNotes,
        ?string $legalBasis = null,
    ): PrivacyRequest {
        return DB::transaction(
            function () use (
                $privacyRequest,
                $actor,
                $decision,
                $reviewNotes,
                $legalBasis,
            ): PrivacyRequest {
                $request =
                    $this->lock(
                        $privacyRequest
                    );

                $this->assertStatus(
                    $request,
                    [
                        PrivacyRequest::STATUS_IDENTITY_VERIFIED,
                        PrivacyRequest::STATUS_UNDER_REVIEW,
                    ]
                );

                if (
                    ! in_array(
                        $decision,
                        [
                            'approved',
                            'rejected',
                        ],
                        true
                    )
                ) {
                    throw ValidationException::withMessages([
                        'decision' => 'The privacy request decision is invalid.',
                    ]);
                }

                $from =
                    $request->status;

                $status =
                    $decision === 'approved'
                        ? PrivacyRequest::STATUS_APPROVED
                        : PrivacyRequest::STATUS_REJECTED;

                $request->forceFill([
                    'status' => $status,

                    'decision' => $decision,

                    'review_notes' => $reviewNotes,

                    'legal_basis' => $legalBasis,

                    'reviewed_at' => now(),

                    'reviewed_by' => $actor->id,
                ])->save();

                $this->addEvent(
                    request: $request,
                    actor: $actor,
                    event: "decision_{$decision}",
                    fromStatus: $from,
                    toStatus: $status,
                    notes: $reviewNotes,
                    metadata: [
                        'has_legal_basis' => filled(
                            $legalBasis
                        ),
                    ],
                );

                $this->auditLogger->record(
                    category: 'privacy',
                    event: "privacy_request.{$decision}",
                    action: 'decision',
                    subject: $request,
                    actor: $actor,
                    metadata: [
                        'type' => $request->type,

                        'decision' => $decision,
                    ],
                );

                return $request->refresh();
            }
        );
    }

    public function markExportReady(
        PrivacyRequest $privacyRequest,
        User $actor,
        string $disk,
        string $path,
        string $checksum,
    ): PrivacyRequest {
        return DB::transaction(
            function () use (
                $privacyRequest,
                $actor,
                $disk,
                $path,
                $checksum,
            ): PrivacyRequest {
                $request =
                    $this->lock(
                        $privacyRequest
                    );

                $this->assertStatus(
                    $request,
                    [
                        PrivacyRequest::STATUS_APPROVED,
                    ]
                );

                if (
                    ! in_array(
                        $request->type,
                        [
                            PrivacyRequest::TYPE_ACCESS,
                            PrivacyRequest::TYPE_EXPORT,
                        ],
                        true
                    )
                ) {
                    throw ValidationException::withMessages([
                        'type' => 'Only approved access or export requests can generate a privacy export.',
                    ]);
                }

                $from =
                    $request->status;

                $request->forceFill([
                    'status' => PrivacyRequest::STATUS_EXPORT_READY,

                    'export_disk' => $disk,

                    'export_path' => $path,

                    'export_checksum' => $checksum,

                    'export_prepared_at' => now(),
                ])->save();

                $this->addEvent(
                    request: $request,
                    actor: $actor,
                    event: 'export_prepared',
                    fromStatus: $from,
                    toStatus: $request->status,
                    metadata: [
                        'checksum' => $checksum,
                    ],
                );

                $this->auditLogger->record(
                    category: 'privacy',
                    event: 'privacy_request.export_prepared',
                    action: 'generate_export',
                    subject: $request,
                    actor: $actor,
                    metadata: [
                        'checksum' => $checksum,
                    ],
                );

                return $request->refresh();
            }
        );
    }

    public function complete(
        PrivacyRequest $privacyRequest,
        User $actor,
        string $executionNotes,
        ?string $deletionStrategy = null,
    ): PrivacyRequest {
        return DB::transaction(
            function () use (
                $privacyRequest,
                $actor,
                $executionNotes,
                $deletionStrategy,
            ): PrivacyRequest {
                $request =
                    $this->lock(
                        $privacyRequest
                    );

                $this->assertStatus(
                    $request,
                    [
                        PrivacyRequest::STATUS_APPROVED,
                        PrivacyRequest::STATUS_EXPORT_READY,
                    ]
                );

                $from =
                    $request->status;

                $request->forceFill([
                    'status' => PrivacyRequest::STATUS_COMPLETED,

                    'execution_notes' => $executionNotes,

                    'deletion_strategy' => $deletionStrategy,

                    'completed_at' => now(),

                    'completed_by' => $actor->id,
                ])->save();

                $this->addEvent(
                    request: $request,
                    actor: $actor,
                    event: 'completed',
                    fromStatus: $from,
                    toStatus: PrivacyRequest::STATUS_COMPLETED,
                    notes: $executionNotes,
                    metadata: [
                        'deletion_strategy' => $deletionStrategy,
                    ],
                );

                $this->auditLogger->record(
                    category: 'privacy',
                    event: 'privacy_request.completed',
                    action: 'complete',
                    subject: $request,
                    actor: $actor,
                    metadata: [
                        'type' => $request->type,

                        'deletion_strategy' => $deletionStrategy,
                    ],
                );

                return $request->refresh();
            }
        );
    }

    public function cancelByRequester(
        PrivacyRequest $privacyRequest,
        User $requester
    ): PrivacyRequest {
        return DB::transaction(
            function () use (
                $privacyRequest,
                $requester
            ): PrivacyRequest {
                $request =
                    $this->lock(
                        $privacyRequest
                    );

                if (
                    (int)
                    $request->requester_user_id
                    !== (int) $requester->id
                ) {
                    abort(403);
                }

                $this->assertStatus(
                    $request,
                    [
                        PrivacyRequest::STATUS_SUBMITTED,
                    ]
                );

                $from =
                    $request->status;

                $request->forceFill([
                    'status' => PrivacyRequest::STATUS_CANCELLED,
                ])->save();

                $this->addEvent(
                    request: $request,
                    actor: $requester,
                    event: 'cancelled',
                    fromStatus: $from,
                    toStatus: PrivacyRequest::STATUS_CANCELLED,
                );

                $this->auditLogger->record(
                    category: 'privacy',
                    event: 'privacy_request.cancelled',
                    action: 'cancel',
                    subject: $request,
                    actor: $requester,
                );

                return $request->refresh();
            }
        );
    }

    private function addEvent(
        PrivacyRequest $request,
        ?User $actor,
        string $event,
        ?string $fromStatus,
        ?string $toStatus,
        ?string $notes = null,
        array $metadata = [],
    ): PrivacyRequestEvent {
        return PrivacyRequestEvent::query()
            ->create([
                'privacy_request_id' => $request->id,

                'actor_id' => $actor?->id,

                'event' => $event,

                'from_status' => $fromStatus,

                'to_status' => $toStatus,

                'notes' => $notes,

                'metadata' => $metadata,
            ]);
    }

    private function lock(
        PrivacyRequest $request
    ): PrivacyRequest {
        return PrivacyRequest::query()
            ->whereKey(
                $request->getKey()
            )
            ->lockForUpdate()
            ->firstOrFail();
    }

    private function assertStatus(
        PrivacyRequest $request,
        array $allowedStatuses
    ): void {
        if (
            in_array(
                $request->status,
                $allowedStatuses,
                true
            )
        ) {
            return;
        }

        throw ValidationException::withMessages([
            'status' => sprintf(
                'This action cannot be performed while the privacy request is in "%s" status.',
                $request->status
            ),
        ]);
    }
}
