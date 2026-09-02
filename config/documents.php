<?php

return [
    'disk' => env(
        'DOCUMENT_DISK',
        'local'
    ),

    'max_size_kb' => (int) env(
        'DOCUMENT_MAX_SIZE_KB',
        10240
    ),

    'allowed_extensions' => [
        'pdf',
        'jpg',
        'jpeg',
        'png',
        'doc',
        'docx',
    ],

    'allowed_mime_types' => [
        'application/pdf',
        'image/jpeg',
        'image/png',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    ],

    'allow_unscanned_downloads' => env(
        'DOCUMENT_ALLOW_UNSCANNED_DOWNLOADS',
        true
    ),

    'scanner' => [
        'driver' => env(
            'DOCUMENT_SCANNER_DRIVER',
            'none'
        ),

        'clamav_binary' => env(
            'DOCUMENT_CLAMAV_BINARY',
            'clamscan'
        ),

        'timeout_seconds' => (int) env(
            'DOCUMENT_SCANNER_TIMEOUT',
            30
        ),
    ],
];
