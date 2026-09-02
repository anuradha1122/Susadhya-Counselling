<?php

namespace App\Console\Commands;

use App\Documents\DocumentScannerManager;
use App\Models\SecureDocument;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ScanDocuments extends Command
{
    protected $signature =
        'documents:scan
        {--limit=100 : Maximum number of documents to scan}
        {--retry-failed : Include previously failed scans}';

    protected $description =
        'Scan pending or unscanned secure documents for malware';

    public function handle(
        DocumentScannerManager $scanner
    ): int {
        $statuses = [
            SecureDocument::SCAN_PENDING,
            SecureDocument::SCAN_UNAVAILABLE,
        ];

        if (
            $this->option(
                'retry-failed'
            )
        ) {
            $statuses[] =
                SecureDocument::SCAN_FAILED;
        }

        $documents =
            SecureDocument::query()
                ->whereIn(
                    'scan_status',
                    $statuses
                )
                ->oldest('id')
                ->limit(
                    max(
                        1,
                        (int) $this->option(
                            'limit'
                        )
                    )
                )
                ->get();

        if (
            $documents->isEmpty()
        ) {
            $this->info(
                'No documents require scanning.'
            );

            return self::SUCCESS;
        }

        foreach (
            $documents as $document
        ) {
            try {
                $disk =
                    Storage::disk(
                        $document->disk
                    );

                if (
                    ! $disk->exists(
                        $document->path
                    )
                ) {
                    $document->update([
                        'scan_status' => SecureDocument::SCAN_FAILED,

                        'scanner' => 'storage',

                        'scan_message' => 'Stored file is missing.',

                        'scanned_at' => now(),
                    ]);

                    $this->error(
                        "{$document->uuid}: file missing"
                    );

                    continue;
                }

                $absolutePath =
                    $disk->path(
                        $document->path
                    );

                $result =
                    $scanner->scan(
                        $absolutePath
                    );

                if (
                    $result->status
                        === SecureDocument::SCAN_QUARANTINED
                    && ! str_contains(
                        $document->path,
                        '/quarantine/'
                    )
                ) {
                    $quarantinePath =
                        implode(
                            '/',
                            [
                                'documents',
                                'quarantine',

                                (string) $document
                                    ->client_profile_id,

                                now()->format('Y'),

                                now()->format('m'),

                                $document
                                    ->stored_name,
                            ]
                        );

                    $disk->move(
                        $document->path,
                        $quarantinePath
                    );

                    $document->path =
                        $quarantinePath;
                }

                $document->forceFill([
                    'scan_status' => $result->status,

                    'scanner' => $result->scanner,

                    'scan_message' => $result->message,

                    'scanned_at' => now(),

                    'quarantined_at' => $result->status
                        === SecureDocument::SCAN_QUARANTINED
                            ? now()
                            : null,
                ])->save();

                $this->line(
                    "{$document->uuid}: {$result->status}"
                );
            } catch (Throwable $exception) {
                $document->update([
                    'scan_status' => SecureDocument::SCAN_FAILED,

                    'scan_message' => $exception->getMessage(),

                    'scanned_at' => now(),
                ]);

                $this->error(
                    "{$document->uuid}: scan failed"
                );
            }
        }

        return self::SUCCESS;
    }
}
