<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Compliance\StorePrivacyRequestRequest;
use App\Models\PrivacyRequest;
use App\Services\Compliance\AuditLogger;
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
        $requests =
            PrivacyRequest::query()
                ->where(
                    'requester_user_id',
                    $request->user()->id
                )
                ->latest('submitted_at')
                ->paginate(15)
                ->withQueryString()
                ->through(
                    fn (PrivacyRequest $privacyRequest): array => [
                        'uuid' => $privacyRequest
                            ->uuid,

                        'type' => $privacyRequest
                            ->type,

                        'status' => $privacyRequest
                            ->status,

                        'request_details' => $privacyRequest
                            ->request_details,

                        'submitted_at' => $privacyRequest
                            ->submitted_at
                            ?->toIso8601String(),

                        'reviewed_at' => $privacyRequest
                            ->reviewed_at
                            ?->toIso8601String(),

                        'completed_at' => $privacyRequest
                            ->completed_at
                            ?->toIso8601String(),

                        'export_available' => filled(
                            $privacyRequest
                                ->export_path
                        ),
                    ]
                );

        return Inertia::render(
            'Client/PrivacyRequests/Index',
            [
                'privacyRequests' => $requests,

                'types' => PrivacyRequest::types(),
            ]
        );
    }

    public function store(
        StorePrivacyRequestRequest $request,
        PrivacyRequestService $service
    ): RedirectResponse {
        $service->submit(
            $request->user(),
            $request->validated()
        );

        return back()->with(
            'success',
            'Privacy request submitted.'
        );
    }

    public function cancel(
        Request $request,
        PrivacyRequest $privacyRequest,
        PrivacyRequestService $service
    ): RedirectResponse {
        $this->assertOwnership(
            $request,
            $privacyRequest
        );

        $service->cancelByRequester(
            $privacyRequest,
            $request->user()
        );

        return back()->with(
            'success',
            'Privacy request cancelled.'
        );
    }

    public function downloadExport(
        Request $request,
        PrivacyRequest $privacyRequest,
        AuditLogger $auditLogger
    ): StreamedResponse {
        $this->assertOwnership(
            $request,
            $privacyRequest
        );

        abort_unless(
            $privacyRequest->status
                ===
                PrivacyRequest::STATUS_EXPORT_READY
                || $privacyRequest->status
                ===
                PrivacyRequest::STATUS_COMPLETED,
            403
        );

        abort_unless(
            $privacyRequest->export_disk
                && $privacyRequest->export_path,
            404
        );

        $disk =
            Storage::disk(
                $privacyRequest
                    ->export_disk
            );

        abort_unless(
            $disk->exists(
                $privacyRequest
                    ->export_path
            ),
            404
        );

        $auditLogger->record(
            category: 'privacy',
            event: 'privacy_request.client_export_downloaded',
            action: 'download_export',
            subject: $privacyRequest,
            actor: $request->user(),
        );

        return $disk->download(
            $privacyRequest->export_path,
            sprintf(
                'my-susadhya-data-%s.json',
                $privacyRequest->uuid
            ),
            [
                'Content-Type' => 'application/json',
            ]
        );
    }

    private function assertOwnership(
        Request $request,
        PrivacyRequest $privacyRequest
    ): void {
        abort_unless(
            (int)
            $privacyRequest
                ->requester_user_id
            ===
            (int)
            $request->user()->id
            &&
            (int)
            $privacyRequest
                ->subject_user_id
            ===
            (int)
            $request->user()->id,
            403
        );
    }
}
