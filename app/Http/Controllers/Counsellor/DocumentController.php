<?php

namespace App\Http\Controllers\Counsellor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Counsellor\StoreCaseDocumentRequest;
use App\Models\ClientCase;
use App\Models\SecureDocument;
use App\Services\Documents\DocumentAccessLogger;
use App\Services\Documents\DocumentAccessService;
use App\Services\Documents\DocumentStorageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class DocumentController extends Controller
{
    public function __construct(
        private readonly DocumentStorageService $storage,
        private readonly DocumentAccessService $access,
        private readonly DocumentAccessLogger $logger,
    ) {}

    public function index(
        Request $request
    ): Response {
        $counsellor =
            $request
                ->user()
                ->counsellorProfile;

        abort_unless(
            $counsellor,
            404
        );

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
                ->whereHas(
                    'clientCase',
                    fn ($query) => $query->where(
                        'counsellor_profile_id',
                        $counsellor->id
                    )
                )
                ->whereIn(
                    'access_scope',
                    [
                        SecureDocument::SCOPE_CARE_TEAM,
                        SecureDocument::SCOPE_CLINICAL,
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

                    'clientCase:id,client_profile_id,status',

                    'uploader:id,name',
                ])
                ->latest()
                ->paginate(12)
                ->withQueryString();

        $documents->through(
            fn (SecureDocument $document) => array_merge(
                $document->toArray(),
                [
                    'can_delete' => $this->access
                        ->canDelete(
                            $request->user(),
                            $document
                        ),
                ]
            )
        );

        $cases =
            ClientCase::query()
                ->where(
                    'counsellor_profile_id',
                    $counsellor->id
                )
                ->whereIn(
                    'status',
                    [
                        ClientCase::STATUS_OPEN,
                        ClientCase::STATUS_ON_HOLD,
                    ]
                )
                ->with(
                    'clientProfile.user:id,name,email'
                )
                ->latest(
                    'opened_at'
                )
                ->get([
                    'id',
                    'client_profile_id',
                    'status',
                    'opened_at',
                ]);

        return Inertia::render(
            'Counsellor/Documents/Index',
            [
                'documents' => $documents,

                'cases' => $cases,

                'filters' => $filters,

                'categories' => [
                    SecureDocument::CATEGORY_CONSENT,
                    SecureDocument::CATEGORY_CLINICAL,
                    SecureDocument::CATEGORY_REPORT,
                    SecureDocument::CATEGORY_OTHER,
                ],

                'scopes' => [
                    SecureDocument::SCOPE_CARE_TEAM,
                    SecureDocument::SCOPE_CLINICAL,
                    SecureDocument::SCOPE_SHARED,
                ],

                'maxUploadKb' => config(
                    'documents.max_size_kb',
                    10240
                ),
            ]
        );
    }

    public function store(
        StoreCaseDocumentRequest $request,
        ClientCase $case,
    ): RedirectResponse {
        $counsellor =
            $request
                ->user()
                ->counsellorProfile;

        abort_unless(
            $counsellor
            && $case->counsellor_profile_id
                === $counsellor->id,
            403
        );

        abort_if(
            $case->status
                === ClientCase::STATUS_CLOSED,
            422,
            'Closed cases are read-only.'
        );

        $document =
            $this->storage->store(
                file: $request->file(
                    'file'
                ),

                actor: $request->user(),

                clientProfile: $case->clientProfile,

                category: $request->validated(
                    'category'
                ),

                accessScope: $request->validated(
                    'access_scope'
                ),

                title: $request->validated(
                    'title'
                ),

                clientCase: $case,
            );

        $this->logger->log(
            $request->user(),
            $document,
            'upload',
            $request,
            [
                'case_id' => $case->id,
            ]
        );

        return back()->with(
            $document->isQuarantined()
                ? 'error'
                : 'success',

            $document->isQuarantined()
                ? 'The document was quarantined and cannot be downloaded.'
                : 'Case document uploaded securely.'
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
            'download',
            $request
        );

        return Storage::disk(
            $document->disk
        )->download(
            $document->path,
            $document->original_name
        );
    }

    public function destroy(
        Request $request,
        SecureDocument $document,
    ): RedirectResponse {
        abort_unless(
            $this->access->canDelete(
                $request->user(),
                $document
            ),
            403
        );

        $this->logger->log(
            $request->user(),
            $document,
            'delete',
            $request
        );

        $this->storage->softDelete(
            $document,
            $request->user()
        );

        return back()->with(
            'success',
            'Document removed from active records.'
        );
    }
}
