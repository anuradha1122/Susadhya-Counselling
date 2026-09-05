<?php

return [
    'privacy_export_disk' => env(
        'PRIVACY_EXPORT_DISK',
        'local'
    ),

    'privacy_export_directory' => env(
        'PRIVACY_EXPORT_DIRECTORY',
        'privacy-exports'
    ),

    'privacy_export_ttl_days' => (int) env(
        'PRIVACY_EXPORT_TTL_DAYS',
        14
    ),

    'retention' => [
        'automatic_categories' => [
            'notifications',
        ],
    ],
];
