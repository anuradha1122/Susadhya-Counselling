<?php

namespace App\Documents;

final readonly class DocumentScanResult
{
    public function __construct(
        public string $status,
        public string $scanner,
        public ?string $message = null,
    ) {}
}
