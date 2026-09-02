<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Client\StoreDocumentRequest;
use App\Models\ClientCase;
use App\Models\SecureDocument;
use App\Services\Documents\DocumentAccessLogger;
use App\Services\Documents\DocumentAccessService;
use App\Services\Documents\DocumentStorageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
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
        $clientProfile =
            $request
                ->user()
                ->clientProfile;

        abort_unless(
            $clientProfile,
            404
        );

        $filters = [
            'search' => $request
                ->string('search')
                ->toString(),

            'category' => $request
                ->string('category')
                ->toString(),
        ];

        $documents =
            SecureDocument::query()
                ->where(
                    'client_profile_id',
                    $clientProfile->id
                )
                ->whereIn(
                    'access_scope',
                    [
                        SecureDocument::SCOPE_CLIENT,
                        SecureDocument::SCOPE_CARE_TEAM,
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
                ->with(
                    'uploader:id,name'
                )
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

        return Inertia::render(
            'Client/Documents/Index',
            [
                'documents' => $documents,

                'filters' => $filters,

                'categories' => [
                    SecureDocument::CATEGORY_CLIENT_UPLOAD,
                    SecureDocument::CATEGORY_CONSENT,
                    SecureDocument::CATEGORY_OTHER,
                ],

                'scopes' => [
                    SecureDocument::SCOPE_CLIENT,
                    SecureDocument::SCOPE_CARE_TEAM,
                ],

                'maxUploadKb' => config(
                    'documents.max_size_kb',
                    10240
                ),
            ]
        );
    }

    public function store(
        StoreDocumentRequest $request
    ): RedirectResponse {
        $clientProfile =
            $request
                ->user()
                ->clientProfile;

        abort_unless(
            $clientProfile,
            404
        );

        $clientCase = null;

        if (
            $request->validated(
                'access_scope'
            )
            === SecureDocument::SCOPE_CARE_TEAM
        ) {
            $clientCase =
                ClientCase::query()
                    ->where(
                        'client_profile_id',
                        $clientProfile->id
                    )
                    ->whereIn(
                        'status',
                        [
                            ClientCase::STATUS_OPEN,
                            ClientCase::STATUS_ON_HOLD,
                        ]
                    )
                    ->latest(
                        'opened_at'
                    )
                    ->first();

            if (! $clientCase) {
                throw ValidationException::withMessages([
                    'access_scope' => 'A care-team document requires an active counselling case.',
                ]);
            }
        }

        $document =
            $this->storage->store(
                file: $request->file(
                    'file'
                ),

                actor: $request->user(),

                clientProfile: $clientProfile,

                category: $request->validated(
                    'category'
                ),

                accessScope: $request->validated(
                    'access_scope'
                ),

                title: $request->validated(
                    'title'
                ),

                clientCase: $clientCase,
            );

        $this->logger->log(
            $request->user(),
            $document,
            'upload',
            $request
        );

        return back()->with(
            $document->isQuarantined()
                ? 'error'
                : 'success',

            $document->isQuarantined()
                ? 'The document was quarantined and cannot be downloaded.'
                : 'Document uploaded securely.'
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
