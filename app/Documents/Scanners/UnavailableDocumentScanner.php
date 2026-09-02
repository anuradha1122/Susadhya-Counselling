<?php

namespace App\Documents\Scanners;

use App\Documents\Contracts\DocumentScanner;
use App\Documents\DocumentScanResult;
use App\Models\SecureDocument;

class UnavailableDocumentScanner implements DocumentScanner
{
    public function scan(
        string $absolutePath
    ): DocumentScanResult {
        return new DocumentScanResult(
            status: SecureDocument::SCAN_UNAVAILABLE,

            scanner: 'none',

            message: 'No malware scanner is configured.',
        );
    }
}
