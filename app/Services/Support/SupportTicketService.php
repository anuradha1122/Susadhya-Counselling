<?php

namespace App\Services\Support;

use App\Models\Appointment;
use App\Models\SessionFeedback;
use App\Models\SupportTicket;
use App\Models\SupportTicketHistory;
use App\Models\SupportTicketReply;
use App\Models\User;
use App\Services\Compliance\AuditLogger;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SupportTicketService
{
    public function __construct(
        private readonly AuditLogger $auditLogger
    ) {}

    public function createGuest(
        array $data
    ): SupportTicket {
        return DB::transaction(
            function () use ($data): SupportTicket {
                $ticket = SupportTicket::query()
                    ->create([
                        'requester_id' => null,
                        'guest_name' => $data['name'],
                        'guest_email' => strtolower(
                            $data['email']
                        ),
                        'category' => $data['category'],
                        'subject' => $data['subject'],
                        'description' => $data['message'],
                        'priority' => SupportTicket::PRIORITY_NORMAL,
                        'status' => SupportTicket::STATUS_OPEN,
                        'last_activity_at' => now(),
                    ]);

                $this->history(
                    ticket: $ticket,
                    event: 'created'
                );

                return $ticket;
            }
        );
    }

    public function createForClient(
        User $user,
        array $data
    ): SupportTicket {
        return DB::transaction(
            function () use (
                $user,
                $data
            ): SupportTicket {
                $ticket = SupportTicket::query()
                    ->create([
                        'requester_id' => $user->id,
                        'guest_name' => null,
                        'guest_email' => null,
                        'category' => $data['category'],
                        'subject' => $data['subject'],
                        'description' => $data['description'],
                        'priority' => SupportTicket::PRIORITY_NORMAL,
                        'status' => SupportTicket::STATUS_OPEN,
                        'last_activity_at' => now(),
                        'created_by' => $user->id,
                        'updated_by' => $user->id,
                    ]);

                $this->history(
                    ticket: $ticket,
                    event: 'created',
                    actor: $user
                );

                /*
                 * Do not place the subject or description
                 * in audit metadata.
                 */
                $this->auditLogger->record(
                    category: 'support',
                    event: 'support.ticket_created',
                    action: 'create',
                    subject: $ticket,
                    actor: $user,
                    metadata: [
                        'category' => $ticket->category,
                        'priority' => $ticket->priority,
                        'status' => $ticket->status,
                    ],
                );

                return $ticket;
            }
        );
    }

    public function addClientReply(
        SupportTicket $ticket,
        User $user,
        string $body
    ): SupportTicketReply {
        if (
            (int) $ticket->requester_id
            !== (int) $user->id
        ) {
            abort(404);
        }

        if ($ticket->isClosed()) {
            throw ValidationException::withMessages([
                'body' => 'This support request is closed. Reopen must be handled by support administration.',
            ]);
        }

        return DB::transaction(
            function () use (
                $ticket,
                $user,
                $body
            ): SupportTicketReply {
                if ($ticket->isResolved()) {
                    $fromStatus =
                        $ticket->status;

                    $ticket->forceFill([
                        'status' => SupportTicket::STATUS_OPEN,
                        'resolved_at' => null,
                        'closed_at' => null,
                        'updated_by' => $user->id,
                        'last_activity_at' => now(),
                    ])->save();

                    $this->history(
                        ticket: $ticket,
                        event: 'reopened_by_client',
                        actor: $user,
                        fromStatus: $fromStatus,
                        toStatus: SupportTicket::STATUS_OPEN
                    );
                }

                $reply = $ticket
                    ->replies()
                    ->create([
                        'user_id' => $user->id,
                        'body' => $body,
                        'is_internal' => false,
                    ]);

                $ticket->forceFill([
                    'last_activity_at' => now(),
                    'updated_by' => $user->id,
                ])->save();

                $this->history(
                    ticket: $ticket,
                    event: 'client_replied',
                    actor: $user
                );

                $this->auditLogger->record(
                    category: 'support',
                    event: 'support.client_reply_added',
                    action: 'create',
                    subject: $ticket,
                    actor: $user,
                    metadata: [
                        'status' => $ticket->status,
                    ],
                );

                return $reply;
            }
        );
    }

    public function addAdminReply(
        SupportTicket $ticket,
        User $user,
        string $body,
        bool $isInternal
    ): SupportTicketReply {
        if (
            $ticket->isClosed()
            && ! $isInternal
        ) {
            throw ValidationException::withMessages([
                'body' => 'A closed support request cannot receive a client-visible reply until it is reopened.',
            ]);
        }

        return DB::transaction(
            function () use (
                $ticket,
                $user,
                $body,
                $isInternal
            ): SupportTicketReply {
                if (
                    ! $isInternal
                    && $ticket->status
                        === SupportTicket::STATUS_OPEN
                ) {
                    $fromStatus =
                        $ticket->status;

                    $ticket->forceFill([
                        'status' => SupportTicket::STATUS_IN_PROGRESS,
                        'updated_by' => $user->id,
                    ])->save();

                    $this->history(
                        ticket: $ticket,
                        event: 'status_changed',
                        actor: $user,
                        fromStatus: $fromStatus,
                        toStatus: SupportTicket::STATUS_IN_PROGRESS
                    );
                }

                $reply = $ticket
                    ->replies()
                    ->create([
                        'user_id' => $user->id,
                        'body' => $body,
                        'is_internal' => $isInternal,
                    ]);

                $ticket->forceFill([
                    'last_activity_at' => now(),
                    'updated_by' => $user->id,
                ])->save();

                $this->history(
                    ticket: $ticket,
                    event: $isInternal
                        ? 'internal_note_added'
                        : 'admin_replied',
                    actor: $user
                );

                $this->auditLogger->record(
                    category: 'support',
                    event: $isInternal
                        ? 'support.internal_note_added'
                        : 'support.admin_reply_added',
                    action: 'create',
                    subject: $ticket,
                    actor: $user,
                    metadata: [
                        'status' => $ticket->status,
                        'is_internal' => $isInternal,
                    ],
                );

                return $reply;
            }
        );
    }

    public function updateAdmin(
        SupportTicket $ticket,
        User $actor,
        array $data
    ): SupportTicket {
        $newStatus =
            $data['status'];

        $newPriority =
            $data['priority'];

        $newOwnerId =
            $data['owner_id']
            ?? null;

        $resolutionNote =
            $data['resolution_note']
            ?? null;

        if (
            $newOwnerId !== null
        ) {
            $owner = User::query()
                ->find($newOwnerId);

            if (
                ! $owner
                || ! $owner->is_active
                || ! $owner->hasAnyRole([
                    'admin',
                    'super_admin',
                ])
            ) {
                throw ValidationException::withMessages([
                    'owner_id' => 'The selected owner must be an active administrator.',
                ]);
            }
        }

        if (
            ! $ticket->canTransitionTo(
                $newStatus
            )
        ) {
            throw ValidationException::withMessages([
                'status' => sprintf(
                    'A support request cannot move directly from "%s" to "%s".',
                    $ticket->status,
                    $newStatus
                ),
            ]);
        }

        if (
            $newStatus
                === SupportTicket::STATUS_RESOLVED
            && $ticket->status
                !== SupportTicket::STATUS_RESOLVED
            && blank($resolutionNote)
        ) {
            throw ValidationException::withMessages([
                'resolution_note' => 'A resolution note is required when resolving a support request.',
            ]);
        }

        return DB::transaction(
            function () use (
                $ticket,
                $actor,
                $newStatus,
                $newPriority,
                $newOwnerId,
                $resolutionNote
            ): SupportTicket {
                $fromStatus =
                    $ticket->status;

                $fromPriority =
                    $ticket->priority;

                $fromOwnerId =
                    $ticket->owner_id;

                $resolvedAt =
                    $ticket->resolved_at;

                $closedAt =
                    $ticket->closed_at;

                if (
                    $newStatus
                    === SupportTicket::STATUS_RESOLVED
                    && $fromStatus
                    !== SupportTicket::STATUS_RESOLVED
                ) {
                    $resolvedAt =
                        now();

                    $closedAt =
                        null;
                }

                if (
                    $newStatus
                    === SupportTicket::STATUS_CLOSED
                ) {
                    $closedAt =
                        now();
                }

                if (
                    $newStatus
                    === SupportTicket::STATUS_OPEN
                    && in_array(
                        $fromStatus,
                        [
                            SupportTicket::STATUS_RESOLVED,
                            SupportTicket::STATUS_CLOSED,
                        ],
                        true
                    )
                ) {
                    $resolvedAt =
                        null;

                    $closedAt =
                        null;
                }

                $ticket->forceFill([
                    'status' => $newStatus,
                    'priority' => $newPriority,
                    'owner_id' => $newOwnerId,
                    'resolved_at' => $resolvedAt,
                    'closed_at' => $closedAt,
                    'last_activity_at' => now(),
                    'updated_by' => $actor->id,
                ])->save();

                $this->history(
                    ticket: $ticket,
                    event: 'administrative_update',
                    actor: $actor,
                    fromStatus: $fromStatus,
                    toStatus: $newStatus,
                    fromOwnerId: $fromOwnerId,
                    toOwnerId: $newOwnerId,
                    fromPriority: $fromPriority,
                    toPriority: $newPriority,
                    note: $resolutionNote
                );

                $this->auditLogger->record(
                    category: 'support',
                    event: 'support.ticket_updated',
                    action: 'update',
                    subject: $ticket,
                    actor: $actor,
                    metadata: [
                        'from_status' => $fromStatus,
                        'to_status' => $newStatus,
                        'from_priority' => $fromPriority,
                        'to_priority' => $newPriority,
                        'owner_changed' => (int) $fromOwnerId
                            !== (int) $newOwnerId,
                    ],
                );

                return $ticket->fresh();
            }
        );
    }

    public function submitFeedback(
        User $user,
        Appointment $appointment,
        array $data
    ): SessionFeedback {
        $clientProfile =
            $user->clientProfile;

        if (
            ! $clientProfile
            || (int) $appointment->client_profile_id
                !== (int) $clientProfile->id
        ) {
            abort(404);
        }

        if (
            $appointment->status
            !== Appointment::STATUS_COMPLETED
        ) {
            throw ValidationException::withMessages([
                'appointment' => 'Feedback can only be submitted after a completed appointment.',
            ]);
        }

        if (
            SessionFeedback::query()
                ->where(
                    'appointment_id',
                    $appointment->id
                )
                ->exists()
        ) {
            throw ValidationException::withMessages([
                'appointment' => 'Feedback has already been submitted for this appointment.',
            ]);
        }

        return DB::transaction(
            function () use (
                $user,
                $clientProfile,
                $appointment,
                $data
            ): SessionFeedback {
                $feedback =
                    SessionFeedback::query()
                        ->create([
                            'appointment_id' => $appointment->id,
                            'client_profile_id' => $clientProfile->id,
                            'overall_rating' => $data['overall_rating'],
                            'technical_rating' => $data['technical_rating']
                                ?? null,
                            'comment' => $data['comment']
                                ?? null,
                            'would_recommend' => array_key_exists(
                                'would_recommend',
                                $data
                            )
                                ? $data['would_recommend']
                                : null,
                            'consent_to_follow_up' => $data[
                                'consent_to_follow_up'
                            ],
                            'submitted_at' => now(),
                        ]);

                /*
                 * Deliberately excludes feedback comment.
                 */
                $this->auditLogger->record(
                    category: 'support',
                    event: 'support.session_feedback_submitted',
                    action: 'create',
                    subject: $feedback,
                    actor: $user,
                    metadata: [
                        'appointment_uuid' => $appointment->uuid,
                        'overall_rating' => $feedback->overall_rating,
                        'consent_to_follow_up' => $feedback
                            ->consent_to_follow_up,
                    ],
                );

                return $feedback;
            }
        );
    }

    private function history(
        SupportTicket $ticket,
        string $event,
        ?User $actor = null,
        ?string $fromStatus = null,
        ?string $toStatus = null,
        ?int $fromOwnerId = null,
        ?int $toOwnerId = null,
        ?string $fromPriority = null,
        ?string $toPriority = null,
        ?string $note = null,
        ?array $metadata = null
    ): SupportTicketHistory {
        return SupportTicketHistory::query()
            ->create([
                'support_ticket_id' => $ticket->id,
                'actor_id' => $actor?->id,
                'event' => $event,
                'from_status' => $fromStatus,
                'to_status' => $toStatus,
                'from_owner_id' => $fromOwnerId,
                'to_owner_id' => $toOwnerId,
                'from_priority' => $fromPriority,
                'to_priority' => $toPriority,
                'note' => $note,
                'metadata' => $metadata,
            ]);
    }
}
