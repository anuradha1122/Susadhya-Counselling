<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreDocumentRequest;
use App\Models\ClientProfile;
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
                ->whereIn(
                    'access_scope',
                    [
                        SecureDocument::SCOPE_ADMIN,
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

        $clients =
            ClientProfile::query()
                ->with(
                    'user:id,name,email'
                )
                ->whereHas('user')
                ->latest('id')
                ->limit(500)
                ->get([
                    'id',
                    'user_id',
                ]);

        return Inertia::render(
            'Admin/Documents/Index',
            [
                'documents' => $documents,

                'clients' => $clients,

                'filters' => $filters,

                'categories' => [
                    SecureDocument::CATEGORY_CONSENT,
                    SecureDocument::CATEGORY_REPORT,
                    SecureDocument::CATEGORY_ADMINISTRATIVE,
                    SecureDocument::CATEGORY_OTHER,
                ],

                'scopes' => [
                    SecureDocument::SCOPE_ADMIN,
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
        StoreDocumentRequest $request
    ): RedirectResponse {
        $clientProfile =
            ClientProfile::query()
                ->findOrFail(
                    $request->validated(
                        'client_profile_id'
                    )
                );

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
                : 'Administrative document uploaded securely.'
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
