<?php

namespace App\Documents\Scanners;

use App\Documents\Contracts\DocumentScanner;
use App\Documents\DocumentScanResult;
use App\Models\SecureDocument;
use Symfony\Component\Process\Process;
use Throwable;

class ClamAvDocumentScanner implements DocumentScanner
{
    public function scan(
        string $absolutePath
    ): DocumentScanResult {
        try {
            $process =
                new Process([
                    config(
                        'documents.scanner.clamav_binary',
                        'clamscan'
                    ),

                    '--no-summary',

                    $absolutePath,
                ]);

            $process->setTimeout(
                config(
                    'documents.scanner.timeout_seconds',
                    30
                )
            );

            $process->run();

            if (
                $process->getExitCode()
                === 0
            ) {
                return new DocumentScanResult(
                    status: SecureDocument::SCAN_CLEAN,

                    scanner: 'clamav',

                    message: 'No malware detected.',
                );
            }

            if (
                $process->getExitCode()
                === 1
            ) {
                return new DocumentScanResult(
                    status: SecureDocument::SCAN_QUARANTINED,

                    scanner: 'clamav',

                    message: trim(
                        $process->getOutput()
                    )
                        ?: 'Malware detected.',
                );
            }

            return new DocumentScanResult(
                status: SecureDocument::SCAN_FAILED,

                scanner: 'clamav',

                message: trim(
                    $process->getErrorOutput()
                    ?: $process->getOutput()
                )
                    ?: 'ClamAV scan failed.',
            );
        } catch (Throwable $exception) {
            return new DocumentScanResult(
                status: SecureDocument::SCAN_FAILED,

                scanner: 'clamav',

                message: $exception->getMessage(),
            );
        }
    }
}
