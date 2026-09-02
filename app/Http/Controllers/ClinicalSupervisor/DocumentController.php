<?php

namespace App\Http\Controllers\ClinicalSupervisor;

use App\Http\Controllers\Controller;
use App\Models\SecureDocument;
use App\Services\Documents\DocumentAccessLogger;
use App\Services\Documents\DocumentAccessService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class DocumentController extends Controller
{
    public function __construct(
        private readonly DocumentAccessService $access,
        private readonly DocumentAccessLogger $logger,
    ) {}

    public function index(
        Request $request
    ): Response {
        $filters = [
            'search' => $request
                ->string('search')
                ->toString(),

            'category' => $request
                ->string('category')
                ->toString(),

            'scope' => $request
                ->string('scope')
                ->toString(),
        ];

        $documents =
            SecureDocument::query()
                ->whereNotNull(
                    'client_case_id'
                )
                ->whereIn(
                    'access_scope',
                    [
                        SecureDocument::SCOPE_CARE_TEAM,
                        SecureDocument::SCOPE_CLINICAL,
                        SecureDocument::SCOPE_SUPERVISOR,
                        SecureDocument::SCOPE_SHARED,
                    ]
                )
                ->when(
                    $filters['search'],
                    fn ($query, $search) => $query->where(
                        fn ($inner) => $inner
                            ->where(
                                'title',
                                'like',
                                "%{$search}%"
                            )
                            ->orWhere(
                                'original_name',
                                'like',
                                "%{$search}%"
                            )
                    )
                )
                ->when(
                    $filters['category'],
                    fn ($query, $category) => $query->where(
                        'category',
                        $category
                    )
                )
                ->when(
                    $filters['scope'],
                    fn ($query, $scope) => $query->where(
                        'access_scope',
                        $scope
                    )
                )
                ->with([
                    'clientProfile.user:id,name,email',

                    'clientCase.counsellorProfile.user:id,name,email',

                    'uploader:id,name',
                ])
                ->latest()
                ->paginate(12)
                ->withQueryString();

        return Inertia::render(
            'ClinicalSupervisor/Documents/Index',
            [
                'documents' => $documents,

                'filters' => $filters,

                'categories' => SecureDocument::categories(),

                'scopes' => [
                    SecureDocument::SCOPE_CARE_TEAM,
                    SecureDocument::SCOPE_CLINICAL,
                    SecureDocument::SCOPE_SUPERVISOR,
                    SecureDocument::SCOPE_SHARED,
                ],
            ]
        );
    }

    public function download(
        Request $request,
        SecureDocument $document,
    ) {
        $this->access
            ->authorizeDownload(
                $request->user(),
                $document
            );

        abort_unless(
            Storage::disk(
                $document->disk
            )->exists(
                $document->path
            ),
            404
        );

        $this->logger->log(
            $request->user(),
            $document,
            'supervisor_download',
            $request
        );

        return Storage::disk(
            $document->disk
        )->download(
            $document->path,
            $document->original_name
        );
    }
}
