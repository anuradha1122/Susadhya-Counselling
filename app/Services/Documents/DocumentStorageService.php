<?php

namespace App\Services\Documents;

use App\Documents\DocumentScannerManager;
use App\Models\ClientCase;
use App\Models\ClientProfile;
use App\Models\CounsellingSession;
use App\Models\SecureDocument;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class DocumentStorageService
{
    public function __construct(
        private readonly DocumentScannerManager $scanner,
    ) {}

    public function store(
        UploadedFile $file,
        User $actor,
        ClientProfile $clientProfile,
        string $category,
        string $accessScope,
        ?string $title = null,
        ?ClientCase $clientCase = null,
        ?CounsellingSession $session = null,
        array $metadata = [],
    ): SecureDocument {
        $this->validateLinkage(
            $clientProfile,
            $clientCase,
            $session
        );

        $extension =
            strtolower(
                $file->getClientOriginalExtension()
            );

        $mimeType =
            $file->getMimeType()
            ?: $file->getClientMimeType()
            ?: 'application/octet-stream';

        $this->validateFile(
            $file,
            $extension,
            $mimeType
        );

        $scanResult =
            $this->scanner->scan(
                $file->getRealPath()
            );

        $disk =
            config(
                'documents.disk',
                'local'
            );

        $storedName =
            Str::uuid()->toString()
            .'.'
            .$extension;

        $directory =
            implode(
                '/',
                [
                    'documents',

                    $scanResult->status
                        === SecureDocument::SCAN_QUARANTINED
                            ? 'quarantine'
                            : 'private',

                    (string) $clientProfile->id,

                    now()->format('Y'),

                    now()->format('m'),
                ]
            );

        $path =
            Storage::disk($disk)
                ->putFileAs(
                    $directory,
                    $file,
                    $storedName
                );

        if (! $path) {
            throw ValidationException::withMessages([
                'file' => 'The document could not be stored.',
            ]);
        }

        try {
            return SecureDocument::query()
                ->create([
                    'client_profile_id' => $clientProfile->id,

                    'client_case_id' => $clientCase?->id,

                    'counselling_session_id' => $session?->id,

                    'uploaded_by' => $actor->id,

                    'category' => $category,

                    'access_scope' => $accessScope,

                    'title' => $title
                        ?: pathinfo(
                            $file
                                ->getClientOriginalName(),
                            PATHINFO_FILENAME
                        ),

                    'original_name' => $file
                        ->getClientOriginalName(),

                    'stored_name' => $storedName,

                    'disk' => $disk,

                    'path' => $path,

                    'mime_type' => $mimeType,

                    'extension' => $extension,

                    'size_bytes' => $file->getSize(),

                    'sha256' => hash_file(
                        'sha256',
                        $file->getRealPath()
                    ),

                    'scan_status' => $scanResult->status,

                    'scanner' => $scanResult->scanner,

                    'scan_message' => $scanResult->message,

                    'scanned_at' => now(),

                    'quarantined_at' => $scanResult->status
                        === SecureDocument::SCAN_QUARANTINED
                            ? now()
                            : null,

                    'metadata' => $metadata ?: null,
                ]);
        } catch (Throwable $exception) {
            Storage::disk($disk)
                ->delete($path);

            throw $exception;
        }
    }

    public function softDelete(
        SecureDocument $document,
        User $actor,
    ): void {
        $document->forceFill([
            'deleted_by' => $actor->id,
        ])->save();

        /*
         * Deliberately keep the physical file.
         *
         * M18 retention/compliance can later decide
         * when permanent file destruction is allowed.
         */
        $document->delete();
    }

    private function validateLinkage(
        ClientProfile $clientProfile,
        ?ClientCase $clientCase,
        ?CounsellingSession $session,
    ): void {
        if (
            $clientCase
            && $clientCase->client_profile_id
                !== $clientProfile->id
        ) {
            throw ValidationException::withMessages([
                'client_case_id' => 'The selected case does not belong to this client.',
            ]);
        }

        if (
            $session
            && $session->client_profile_id
                !== $clientProfile->id
        ) {
            throw ValidationException::withMessages([
                'counselling_session_id' => 'The selected session does not belong to this client.',
            ]);
        }

        if (
            $clientCase
            && $session
            && $session->counsellor_profile_id
                !== $clientCase->counsellor_profile_id
        ) {
            throw ValidationException::withMessages([
                'counselling_session_id' => 'The selected session does not belong to this case.',
            ]);
        }
    }

    private function validateFile(
        UploadedFile $file,
        string $extension,
        string $mimeType,
    ): void {
        if (
            ! in_array(
                $extension,
                config(
                    'documents.allowed_extensions',
                    []
                ),
                true
            )
        ) {
            throw ValidationException::withMessages([
                'file' => 'This file extension is not allowed.',
            ]);
        }

        if (
            ! in_array(
                $mimeType,
                config(
                    'documents.allowed_mime_types',
                    []
                ),
                true
            )
        ) {
            throw ValidationException::withMessages([
                'file' => 'This file type is not allowed.',
            ]);
        }

        $maxBytes =
            config(
                'documents.max_size_kb',
                10240
            )
            * 1024;

        if (
            $file->getSize()
            > $maxBytes
        ) {
            throw ValidationException::withMessages([
                'file' => 'The document exceeds the maximum allowed size.',
            ]);
        }
    }
}
