<?php

namespace App\Http\Controllers\Admin\Cms;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Cms\StoreCmsMediaRequest;
use App\Models\CmsMedia;
use App\Services\Compliance\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class CmsMediaController extends Controller
{
    public function index(
        Request $request
    ): Response {
        $filters =
            $request->validate([
                'search' => [
                    'nullable',
                    'string',
                    'max:150',
                ],

                'status' => [
                    'nullable',
                    'in:active,archived',
                ],
            ]);

        $query =
            CmsMedia::query()
                ->with(
                    'uploader:id,name'
                );

        if (
            filled(
                $filters['search']
                ?? null
            )
        ) {
            $search =
                $filters['search'];

            $query->where(
                function ($query) use ($search): void {
                    $query
                        ->where(
                            'original_name',
                            'like',
                            "%{$search}%"
                        )
                        ->orWhere(
                            'alt_text',
                            'like',
                            "%{$search}%"
                        );
                }
            );
        }

        if (
            ($filters['status'] ?? null)
            === 'active'
        ) {
            $query->where(
                'is_active',
                true
            );
        }

        if (
            ($filters['status'] ?? null)
            === 'archived'
        ) {
            $query->where(
                'is_active',
                false
            );
        }

        $media =
            $query
                ->latest()
                ->paginate(24)
                ->withQueryString()
                ->through(
                    fn (CmsMedia $item): array => [
                        'uuid' => $item->uuid,

                        'original_name' => $item
                            ->original_name,

                        'mime_type' => $item
                            ->mime_type,

                        'size_bytes' => $item
                            ->size_bytes,

                        'alt_text' => $item
                            ->alt_text,

                        'width' => $item->width,

                        'height' => $item->height,

                        'is_active' => $item
                            ->is_active,

                        'checksum' => $item
                            ->checksum,

                        'url' => $item
                            ->publicUrl(),

                        'uploaded_by' => $item
                            ->uploader
                            ?->name,

                        'created_at' => $item
                            ->created_at
                            ?->toIso8601String(),
                    ]
                );

        return Inertia::render(
            'Admin/Cms/Media/Index',
            [
                'media' => $media,

                'filters' => [
                    'search' => $filters['search']
                        ?? '',

                    'status' => $filters['status']
                        ?? 'active',
                ],
            ]
        );
    }

    public function store(
        StoreCmsMediaRequest $request,
        AuditLogger $auditLogger
    ): RedirectResponse {
        $file =
            $request->file('file');

        $extension =
            strtolower(
                $file
                    ->extension()
            );

        $filename =
            Str::uuid()
            .'.'
            .$extension;

        $directory =
            'cms/'
            .now()->format(
                'Y/m'
            );

        $path =
            $file->storeAs(
                $directory,
                $filename,
                'public'
            );

        $absolutePath =
            Storage::disk('public')
                ->path($path);

        $dimensions =
            @getimagesize(
                $absolutePath
            );

        $media =
            CmsMedia::query()
                ->create([
                    'disk' => 'public',

                    'path' => $path,

                    'original_name' => $file
                        ->getClientOriginalName(),

                    'mime_type' => $file
                        ->getMimeType()
                        ?? 'application/octet-stream',

                    'size_bytes' => $file->getSize(),

                    'checksum' => hash_file(
                        'sha256',
                        $absolutePath
                    ),

                    'alt_text' => $request
                        ->validated(
                            'alt_text'
                        ),

                    'width' => is_array(
                        $dimensions
                    )
                            ? $dimensions[0]
                            : null,

                    'height' => is_array(
                        $dimensions
                    )
                            ? $dimensions[1]
                            : null,

                    'is_active' => true,

                    'uploaded_by' => $request
                        ->user()
                        ->id,
                ]);

        $auditLogger->record(
            category: 'cms',
            event: 'cms.media_uploaded',
            action: 'upload',
            subject: $media,
            actor: $request->user(),
            metadata: [
                'mime_type' => $media->mime_type,

                'size_bytes' => $media->size_bytes,

                'checksum' => $media->checksum,
            ],
        );

        return back()->with(
            'success',
            'Media uploaded successfully.'
        );
    }

    public function archive(
        Request $request,
        CmsMedia $cmsMedia,
        AuditLogger $auditLogger
    ): RedirectResponse {
        abort_unless(
            $request->user()?->can(
                'cms.media.manage'
            ),
            403
        );

        $cmsMedia->update([
            'is_active' => false,
        ]);

        $auditLogger->record(
            category: 'cms',
            event: 'cms.media_archived',
            action: 'archive',
            subject: $cmsMedia,
            actor: $request->user(),
        );

        return back()->with(
            'success',
            'Media archived. The physical file was retained to prevent broken published content.'
        );
    }

    public function restore(
        Request $request,
        CmsMedia $cmsMedia
    ): RedirectResponse {
        abort_unless(
            $request->user()?->can(
                'cms.media.manage'
            ),
            403
        );

        $cmsMedia->update([
            'is_active' => true,
        ]);

        return back()->with(
            'success',
            'Media restored.'
        );
    }
}
