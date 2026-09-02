<?php

namespace App\Documents;

use App\Documents\Contracts\DocumentScanner;
use App\Documents\Scanners\ClamAvDocumentScanner;
use App\Documents\Scanners\UnavailableDocumentScanner;
use InvalidArgumentException;

class DocumentScannerManager implements DocumentScanner
{
    public function scan(
        string $absolutePath
    ): DocumentScanResult {
        return $this
            ->driver()
            ->scan(
                $absolutePath
            );
    }

    private function driver(): DocumentScanner
    {
        return match (
            config(
                'documents.scanner.driver',
                'none'
            )
        ) {
            'none' => app(
                UnavailableDocumentScanner::class
            ),

            'clamav' => app(
                ClamAvDocumentScanner::class
            ),

            default => throw new InvalidArgumentException(
                'Unsupported document scanner driver.'
            ),
        };
    }
}
