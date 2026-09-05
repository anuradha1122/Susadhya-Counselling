<?php

namespace App\Http\Controllers\Compliance;

use App\Http\Controllers\Controller;
use App\Http\Requests\Compliance\CompletePrivacyRequestRequest;
use App\Http\Requests\Compliance\ReviewPrivacyRequestRequest;
use App\Http\Requests\Compliance\VerifyPrivacyRequestIdentityRequest;
use App\Models\PrivacyRequest;
use App\Services\Compliance\AuditLogger;
use App\Services\Compliance\PrivacyExportService;
use App\Services\Compliance\PrivacyRequestService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PrivacyRequestController extends Controller
{
    public function index(
        Request $request
    ): Response {
        $filters = $request->validate([
            'search' => [
                'nullable',
                'string',
                'max:150',
            ],

            'type' => [
                'nullable',
                'string',
                'max:32',
            ],

            'status' => [
                'nullable',
                'string',
                'max:40',
            ],
        ]);

        $query = PrivacyRequest::query()
            ->with([
                'requester:id,name,email',
                'subject:id,name,email',
            ]);

        if (
            isset($filters['search'])
            && $filters['search'] !== ''
        ) {
            $search = $filters['search'];

            $query->where(
                function ($query) use ($search): void {
                    $query
                        ->where(
                            'uuid',
                            'like',
                            "%{$search}%"
                        )
                        ->orWhereHas(
                            'subject',
                            function ($subjectQuery) use ($search): void {
                                $subjectQuery
                                    ->where(
                                        'name',
                                        'like',
                                        "%{$search}%"
                                    )
                                    ->orWhere(
                                        'email',
                                        'like',
                                        "%{$search}%"
                                    );
                            }
                        );
                }
            );
        }

        if (
            isset($filters['type'])
            && $filters['type'] !== ''
        ) {
            $query->where(
                'type',
                $filters['type']
            );
        }

        if (
            isset($filters['status'])
            && $filters['status'] !== ''
        ) {
            $query->where(
                'status',
                $filters['status']
            );
        }

        $requests = $query
            ->latest('submitted_at')
            ->paginate(20)
            ->withQueryString()
            ->through(
                fn (PrivacyRequest $privacyRequest): array => [
                    'uuid' => $privacyRequest->uuid,

                    'type' => $privacyRequest->type,

                    'status' => $privacyRequest->status,

                    'subject' => $privacyRequest->subject
                            ? [
                                'name' => $privacyRequest
                                    ->subject
                                    ->name,

                                'email' => $privacyRequest
                                    ->subject
                                    ->email,
                            ]
                            : null,

                    'submitted_at' => $privacyRequest
                        ->submitted_at
                        ?->toIso8601String(),

                    'due_at' => $privacyRequest
                        ->due_at
                        ?->toIso8601String(),
                ]
            );

        return Inertia::render(
            'Compliance/PrivacyRequests/Index',
            [
                'privacyRequests' => $requests,

                'types' => PrivacyRequest::types(),

                'statuses' => PrivacyRequest::statuses(),

                'filters' => [
                    'search' => $filters['search'] ?? '',

                    'type' => $filters['type'] ?? '',

                    'status' => $filters['status'] ?? '',
                ],
            ]
        );
    }

    public function show(
        PrivacyRequest $privacyRequest
    ): Response {
        $privacyRequest->load([
            'requester:id,name,email',
            'subject:id,name,email',
            'identityVerifier:id,name,email',
            'reviewer:id,name,email',
            'completer:id,name,email',
            'events.actor:id,name,email',
        ]);

        return Inertia::render(
            'Compliance/PrivacyRequests/Show',
            [
                'privacyRequest' => [
                    'uuid' => $privacyRequest->uuid,

                    'type' => $privacyRequest->type,

                    'status' => $privacyRequest->status,

                    'request_details' => $privacyRequest
                        ->request_details,

                    'scope' => $privacyRequest->scope ?? [],

                    'submitted_at' => $privacyRequest
                        ->submitted_at
                        ?->toIso8601String(),

                    'identity_verified_at' => $privacyRequest
                        ->identity_verified_at
                        ?->toIso8601String(),

                    'reviewed_at' => $privacyRequest
                        ->reviewed_at
                        ?->toIso8601String(),

                    'decision' => $privacyRequest->decision,

                    'review_notes' => $privacyRequest
                        ->review_notes,

                    'legal_basis' => $privacyRequest
                        ->legal_basis,

                    'due_at' => $privacyRequest
                        ->due_at
                        ?->toIso8601String(),

                    'export_prepared_at' => $privacyRequest
                        ->export_prepared_at
                        ?->toIso8601String(),

                    'export_checksum' => $privacyRequest
                        ->export_checksum,

                    'deletion_strategy' => $privacyRequest
                        ->deletion_strategy,

                    'execution_notes' => $privacyRequest
                        ->execution_notes,

                    'completed_at' => $privacyRequest
                        ->completed_at
                        ?->toIso8601String(),

                    'requester' => $this->userData(
                        $privacyRequest
                            ->requester
                    ),

                    'subject' => $this->userData(
                        $privacyRequest
                            ->subject
                    ),

                    'identity_verifier' => $this->userData(
                        $privacyRequest
                            ->identityVerifier
                    ),

                    'reviewer' => $this->userData(
                        $privacyRequest
                            ->reviewer
                    ),

                    'completer' => $this->userData(
                        $privacyRequest
                            ->completer
                    ),

                    'events' => $privacyRequest
                        ->events
                        ->map(
                            fn ($event): array => [
                                'uuid' => $event->uuid,

                                'event' => $event->event,

                                'from_status' => $event
                                    ->from_status,

                                'to_status' => $event
                                    ->to_status,

                                'notes' => $event->notes,

                                'metadata' => $event->metadata
                                    ?? [],

                                'actor' => $this->userData(
                                    $event->actor
                                ),

                                'created_at' => $event
                                    ->created_at
                                    ?->toIso8601String(),
                            ]
                        )
                        ->values(),
                ],
            ]
        );
    }

    public function verifyIdentity(
        VerifyPrivacyRequestIdentityRequest $request,
        PrivacyRequest $privacyRequest,
        PrivacyRequestService $service
    ): RedirectResponse {
        $service->verifyIdentity(
            privacyRequest: $privacyRequest,
            actor: $request->user(),
            notes: $request->validated(
                'notes'
            ),
        );

        return back()->with(
            'success',
            'Identity verification recorded.'
        );
    }

    public function beginReview(
        Request $request,
        PrivacyRequest $privacyRequest,
        PrivacyRequestService $service
    ): RedirectResponse {
        abort_unless(
            $request->user()?->can(
                'compliance.privacy.manage'
            ),
            403
        );

        $service->beginReview(
            $privacyRequest,
            $request->user()
        );

        return back()->with(
            'success',
            'Privacy request moved to review.'
        );
    }

    public function decide(
        ReviewPrivacyRequestRequest $request,
        PrivacyRequest $privacyRequest,
        PrivacyRequestService $service
    ): RedirectResponse {
        $data = $request->validated();

        $service->decide(
            privacyRequest: $privacyRequest,
            actor: $request->user(),
            decision: $data['decision'],
            reviewNotes: $data['review_notes'],
            legalBasis: $data['legal_basis']
                ?? null,
        );

        return back()->with(
            'success',
            'Privacy request decision recorded.'
        );
    }

    public function prepareExport(
        Request $request,
        PrivacyRequest $privacyRequest,
        PrivacyExportService $service
    ): RedirectResponse {
        abort_unless(
            $request->user()?->can(
                'compliance.privacy.manage'
            ),
            403
        );

        $service->prepare(
            $privacyRequest,
            $request->user()
        );

        return back()->with(
            'success',
            'Reviewed privacy export prepared.'
        );
    }

    public function downloadExport(
        Request $request,
        PrivacyRequest $privacyRequest,
        AuditLogger $auditLogger
    ): StreamedResponse {
        abort_unless(
            $request->user()?->can(
                'compliance.privacy.view'
            ),
            403
        );

        abort_unless(
            $privacyRequest->export_disk
                && $privacyRequest->export_path,
            404
        );

        $disk =
            Storage::disk(
                $privacyRequest->export_disk
            );

        abort_unless(
            $disk->exists(
                $privacyRequest->export_path
            ),
            404
        );

        $auditLogger->record(
            category: 'privacy',
            event: 'privacy_request.export_downloaded',
            action: 'download_export',
            subject: $privacyRequest,
            actor: $request->user(),
        );

        return $disk->download(
            $privacyRequest->export_path,
            sprintf(
                'privacy-export-%s.json',
                $privacyRequest->uuid
            ),
            [
                'Content-Type' => 'application/json',
            ]
        );
    }

    public function complete(
        CompletePrivacyRequestRequest $request,
        PrivacyRequest $privacyRequest,
        PrivacyRequestService $service
    ): RedirectResponse {
        $data =
            $request->validated();

        $service->complete(
            privacyRequest: $privacyRequest,
            actor: $request->user(),
            executionNotes: $data['execution_notes'],
            deletionStrategy: $data['deletion_strategy']
                ?? null,
        );

        return back()->with(
            'success',
            'Privacy request completed.'
        );
    }

    private function userData(
        mixed $user
    ): ?array {
        if ($user === null) {
            return null;
        }

        return [
            'name' => $user->name,
            'email' => $user->email,
        ];
    }
}
