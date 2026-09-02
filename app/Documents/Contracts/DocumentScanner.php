<?php

namespace App\Documents\Contracts;

use App\Documents\DocumentScanResult;

interface DocumentScanner
{
    public function scan(
        string $absolutePath
    ): DocumentScanResult;
}
