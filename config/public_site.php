<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Public service visibility
    |--------------------------------------------------------------------------
    |
    | Only catalogue services with one of these statuses may appear publicly.
    |
    */

    'service_statuses' => [
        'active',
        'published',
    ],

    /*
    |--------------------------------------------------------------------------
    | Public counsellor visibility
    |--------------------------------------------------------------------------
    |
    | Counsellor schema has evolved through M05-M08. The public directory
    | service therefore uses safe schema inspection, while these status
    | values define which profiles may be exposed.
    |
    */

    'counsellor_statuses' => [
        'active',
        'approved',
        'verified',
    ],

    /*
    |--------------------------------------------------------------------------
    | Homepage limits
    |--------------------------------------------------------------------------
    */

    'home_services_limit' => 6,

    'home_counsellors_limit' => 4,

    'home_faq_limit' => 6,

    'home_testimonials_limit' => 6,

    /*
    |--------------------------------------------------------------------------
    | Pagination
    |--------------------------------------------------------------------------
    */

    'services_per_page' => 12,

    'counsellors_per_page' => 12,
];
