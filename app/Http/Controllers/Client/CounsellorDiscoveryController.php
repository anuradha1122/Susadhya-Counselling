<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\CounsellingService;
use App\Models\CounsellorAvailabilityRule;
use App\Models\CounsellorProfile;
use App\Models\Language;
use App\Models\Specialization;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class CounsellorDiscoveryController extends Controller
{
    public function index(
        Request $request
    ): Response {
        $filters =
            $request->validate([
                'search' => [
                    'nullable',
                    'string',
                    'max:120',
                ],

                'specialization_id' => [
                    'nullable',
                    'integer',
                    'exists:specializations,id',
                ],

                'language_id' => [
                    'nullable',
                    'integer',
                    'exists:languages,id',
                ],

                'mode' => [
                    'nullable',
                    Rule::in([
                        CounsellorAvailabilityRule::MODE_ONLINE,
                        CounsellorAvailabilityRule::MODE_IN_PERSON,
                        CounsellorAvailabilityRule::MODE_BOTH,
                    ]),
                ],

                'availability_day' => [
                    'nullable',
                    'integer',
                    'between:0,6',
                ],
            ]);

        $search =
            $filters['search']
                ?? null;

        $specializationId =
            $filters[
                'specialization_id'
            ] ?? null;

        $languageId =
            $filters[
                'language_id'
            ] ?? null;

        $mode =
            $filters['mode']
                ?? null;

        $availabilityDay =
            $filters[
                'availability_day'
            ] ?? null;

        $counsellors =
            CounsellorProfile::query()
                ->with([
                    'user:id,name,email,phone,is_active,profile_photo_path',

                    'specializations:id,name',

                    'languages:id,name,code',

                    'availabilityRules' => function (
                        $query
                    ): void {
                        $query
                            ->where(
                                'is_active',
                                true
                            )
                            ->orderBy(
                                'day_of_week'
                            )
                            ->orderBy(
                                'start_time'
                            );
                    },
                ])
                ->where(
                    'status',
                    'active'
                )
                ->whereHas(
                    'user',
                    function (
                        Builder $query
                    ): void {
                        $query->where(
                            'is_active',
                            true
                        );
                    }
                )
                ->when(
                    $search,
                    function (
                        Builder $query,
                        string $search
                    ): void {
                        $query->where(
                            function (
                                Builder $query
                            ) use (
                                $search
                            ): void {
                                $query
                                    ->where(
                                        'professional_title',
                                        'like',
                                        "%{$search}%"
                                    )
                                    ->orWhere(
                                        'biography',
                                        'like',
                                        "%{$search}%"
                                    )
                                    ->orWhere(
                                        'city',
                                        'like',
                                        "%{$search}%"
                                    )
                                    ->orWhereHas(
                                        'user',
                                        function (
                                            Builder $query
                                        ) use (
                                            $search
                                        ): void {
                                            $query
                                                ->where(
                                                    'name',
                                                    'like',
                                                    "%{$search}%"
                                                )
                                                ->orWhere(
                                                    'email',
                                                    'like',
                                                    "%{$search}%"
                                                );
                                        }
                                    )
                                    ->orWhereHas(
                                        'specializations',
                                        function (
                                            Builder $query
                                        ) use (
                                            $search
                                        ): void {
                                            $query
                                                ->where(
                                                    'name',
                                                    'like',
                                                    "%{$search}%"
                                                );
                                        }
                                    );
                            }
                        );
                    }
                )
                ->when(
                    $specializationId,
                    function (
                        Builder $query,
                        int|string $specializationId
                    ): void {
                        $query
                            ->whereHas(
                                'specializations',
                                function (
                                    Builder $query
                                ) use (
                                    $specializationId
                                ): void {
                                    $query
                                        ->whereKey(
                                            (int)
                                            $specializationId
                                        );
                                }
                            );
                    }
                )
                ->when(
                    $languageId,
                    function (
                        Builder $query,
                        int|string $languageId
                    ): void {
                        $query
                            ->whereHas(
                                'languages',
                                function (
                                    Builder $query
                                ) use (
                                    $languageId
                                ): void {
                                    $query
                                        ->whereKey(
                                            (int)
                                            $languageId
                                        );
                                }
                            );
                    }
                )
                ->when(
                    $mode,
                    function (
                        Builder $query,
                        string $mode
                    ): void {
                        $query
                            ->whereHas(
                                'availabilityRules',
                                function (
                                    Builder $query
                                ) use (
                                    $mode
                                ): void {
                                    $query
                                        ->where(
                                            'is_active',
                                            true
                                        )
                                        ->where(
                                            function (
                                                Builder $query
                                            ) use (
                                                $mode
                                            ): void {
                                                $query
                                                    ->where(
                                                        'mode',
                                                        $mode
                                                    )
                                                    ->orWhere(
                                                        'mode',
                                                        CounsellorAvailabilityRule::MODE_BOTH
                                                    );
                                            }
                                        );
                                }
                            );
                    }
                )
                ->when(
                    $availabilityDay
                        !== null
                    && $availabilityDay
                        !== '',
                    function (
                        Builder $query
                    ) use (
                        $availabilityDay
                    ): void {
                        $query
                            ->whereHas(
                                'availabilityRules',
                                function (
                                    Builder $query
                                ) use (
                                    $availabilityDay
                                ): void {
                                    $query
                                        ->where(
                                            'is_active',
                                            true
                                        )
                                        ->where(
                                            'day_of_week',
                                            (int)
                                            $availabilityDay
                                        );
                                }
                            );
                    }
                )
                ->orderByDesc(
                    'years_of_experience'
                )
                ->orderByDesc('id')
                ->paginate(9)
                ->withQueryString()
                ->through(
                    fn (
                        CounsellorProfile $counsellor
                    ): array => $this
                        ->counsellorCardPayload(
                            $counsellor
                        )
                );

        return Inertia::render(
            'Client/Counsellors/Index',
            [
                'counsellors' => $counsellors,

                'filters' => [
                    'search' => $filters[
                            'search'
                        ] ?? '',

                    'specialization_id' => isset(
                        $filters[
                            'specialization_id'
                        ]
                    )
                            ? (string)
                                $filters[
                                    'specialization_id'
                                ]
                            : '',

                    'language_id' => isset(
                        $filters[
                            'language_id'
                        ]
                    )
                            ? (string)
                                $filters[
                                    'language_id'
                                ]
                            : '',

                    'mode' => $filters[
                            'mode'
                        ] ?? '',

                    'availability_day' => isset(
                        $filters[
                            'availability_day'
                        ]
                    )
                            ? (string)
                                $filters[
                                    'availability_day'
                                ]
                            : '',
                ],

                'options' => [
                    'specializations' => Specialization::query()
                        ->where(
                            'is_active',
                            true
                        )
                        ->orderBy(
                            'name'
                        )
                        ->get([
                            'id',
                            'name',
                        ]),

                    'languages' => Language::query()
                        ->where(
                            'is_active',
                            true
                        )
                        ->orderBy(
                            'name'
                        )
                        ->get([
                            'id',
                            'name',
                            'code',
                        ]),

                    'modes' => [
                        [
                            'value' => '',
                            'label' => 'Any mode',
                        ],
                        [
                            'value' => CounsellorAvailabilityRule::MODE_ONLINE,
                            'label' => 'Online',
                        ],
                        [
                            'value' => CounsellorAvailabilityRule::MODE_IN_PERSON,
                            'label' => 'In person',
                        ],
                        [
                            'value' => CounsellorAvailabilityRule::MODE_BOTH,
                            'label' => 'Online and in person',
                        ],
                    ],

                    'days' => collect(
                        CounsellorAvailabilityRule::days()
                    )
                        ->map(
                            fn (
                                string $label,
                                int $value
                            ): array => [
                                'value' => $value,
                                'label' => $label,
                            ]
                        )
                        ->values(),
                ],
            ]
        );
    }

    public function show(
        CounsellorProfile $counsellor
    ): Response {
        abort_unless(
            $counsellor->status
                === 'active',
            404
        );

        abort_unless(
            (bool)
            $counsellor
                ->user
                ?->is_active,
            404
        );

        $counsellor->load([
            'user:id,name,email,phone,is_active,profile_photo_path',

            'specializations:id,name',

            'languages:id,name,code',

            'qualifications',

            'availabilityRules' => function (
                $query
            ): void {
                $query
                    ->with([
                        'activeBreaks',
                    ])
                    ->where(
                        'is_active',
                        true
                    )
                    ->orderBy(
                        'day_of_week'
                    )
                    ->orderBy(
                        'start_time'
                    );
            },
        ]);

        $services =
            $this
                ->bookableServicesForCounsellor(
                    $counsellor
                );

        return Inertia::render(
            'Client/Counsellors/Show',
            [
                'counsellor' => $this
                    ->counsellorDetailPayload(
                        $counsellor
                    ),

                'services' => $services,
            ]
        );
    }

    private function bookableServicesForCounsellor(
        CounsellorProfile $counsellor
    ): Collection {
        $availableModes =
            $counsellor
                ->availabilityRules
                ->pluck('mode')
                ->unique()
                ->values();

        $query =
            CounsellingService::query()
                ->bookable();

        if (
            ! $availableModes->contains(
                CounsellorAvailabilityRule::MODE_BOTH
            )
        ) {
            $specificModes =
                $availableModes
                    ->filter(
                        fn (
                            string $mode
                        ): bool => in_array(
                            $mode,
                            [
                                CounsellorAvailabilityRule::MODE_ONLINE,
                                CounsellorAvailabilityRule::MODE_IN_PERSON,
                            ],
                            true
                        )
                    )
                    ->values()
                    ->all();

            if (
                empty(
                    $specificModes
                )
            ) {
                $query->whereRaw(
                    '1 = 0'
                );
            } else {
                $query->where(
                    function (
                        Builder $query
                    ) use (
                        $specificModes
                    ): void {
                        $query
                            ->where(
                                'service_mode',
                                'both'
                            )
                            ->orWhereIn(
                                'service_mode',
                                $specificModes
                            );
                    }
                );
            }
        }

        return $query
            ->orderBy(
                'display_order'
            )
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'short_description',
                'duration_minutes',
                'service_mode',
                'price',
                'currency',
                'status',
            ]);
    }

    private function counsellorCardPayload(
        CounsellorProfile $counsellor
    ): array {
        return [
            'id' => $counsellor->id,

            'uuid' => $counsellor->uuid,

            'name' => $counsellor->user?->name
                ?? "Counsellor #{$counsellor->id}",

            'profile_photo_url' => $counsellor->user
                ?->profile_photo_url,

            'professional_title' => $counsellor->professional_title,

            'years_of_experience' => $counsellor->years_of_experience,

            'biography' => $counsellor->biography,

            'city' => $counsellor->city,

            'status' => $counsellor->status,

            'rating_placeholder' => 'New',

            'specializations' => $this->specializationPayload(
                $counsellor
            ),

            'languages' => $this->languagePayload(
                $counsellor
            ),

            'availability_summary' => $this->availabilitySummaryPayload(
                $counsellor
            ),
        ];
    }

    private function counsellorDetailPayload(
        CounsellorProfile $counsellor
    ): array {
        return [
            'id' => $counsellor->id,

            'uuid' => $counsellor->uuid,

            'name' => $counsellor->user?->name
                ?? "Counsellor #{$counsellor->id}",

            'profile_photo_url' => $counsellor->user
                ?->profile_photo_url,

            'email' => $counsellor->user?->email,

            'phone' => $counsellor->user?->phone,

            'registration_number' => $counsellor->registration_number,

            'professional_title' => $counsellor->professional_title,

            'gender' => $counsellor->gender,

            'years_of_experience' => $counsellor->years_of_experience,

            'biography' => $counsellor->biography,

            'address' => $counsellor->address,

            'city' => $counsellor->city,

            'status' => $counsellor->status,

            'rating_placeholder' => 'New',

            'specializations' => $this->specializationPayload(
                $counsellor
            ),

            'languages' => $this->languagePayload(
                $counsellor
            ),

            'qualifications' => $counsellor
                ->qualifications
                ->map(
                    fn (
                        Model $qualification
                    ): array => [
                        'id' => $qualification
                            ->getKey(),

                        'qualification' => $this
                            ->firstAvailableAttribute(
                                $qualification,
                                [
                                    'qualification',
                                    'qualification_name',
                                    'title',
                                    'name',
                                ]
                            ),

                        'institution' => $this
                            ->firstAvailableAttribute(
                                $qualification,
                                [
                                    'institution',
                                    'institute',
                                    'awarding_body',
                                ]
                            ),

                        'field_of_study' => $this
                            ->firstAvailableAttribute(
                                $qualification,
                                [
                                    'field_of_study',
                                    'field',
                                    'study_area',
                                ]
                            ),

                        'year_completed' => $this
                            ->firstAvailableAttribute(
                                $qualification,
                                [
                                    'year_completed',
                                    'completed_year',
                                    'year',
                                ]
                            ),

                        'notes' => $this
                            ->firstAvailableAttribute(
                                $qualification,
                                [
                                    'notes',
                                    'description',
                                ]
                            ),
                    ]
                )
                ->values(),

            'availability_summary' => $this
                ->availabilitySummaryPayload(
                    $counsellor,
                    includeBreaks: true
                ),
        ];
    }

    private function specializationPayload(
        CounsellorProfile $counsellor
    ): array {
        return $counsellor
            ->specializations
            ->map(
                fn (
                    $specialization
                ): array => [
                    'id' => $specialization
                        ->id,

                    'name' => $specialization
                        ->name,
                ]
            )
            ->values()
            ->all();
    }

    private function languagePayload(
        CounsellorProfile $counsellor
    ): array {
        return $counsellor
            ->languages
            ->map(
                fn (
                    $language
                ): array => [
                    'id' => $language->id,

                    'name' => $language->name,

                    'code' => $language->code,

                    'proficiency' => $language
                        ->pivot
                        ?->proficiency,
                ]
            )
            ->values()
            ->all();
    }

    private function availabilitySummaryPayload(
        CounsellorProfile $counsellor,
        bool $includeBreaks = false
    ): array {
        return $counsellor
            ->availabilityRules
            ->groupBy(
                'day_of_week'
            )
            ->map(
                function (
                    $rules,
                    int|string $dayOfWeek
                ) use (
                    $includeBreaks
                ): array {
                    return [
                        'day_of_week' => (int)
                            $dayOfWeek,

                        'day_name' => CounsellorAvailabilityRule::days()[
                                (int)
                                $dayOfWeek
                            ] ?? 'Unknown',

                        'slots' => $rules
                            ->map(
                                fn (
                                    $rule
                                ): array => [
                                    'id' => $rule->id,

                                    'start_time' => $this
                                        ->formatTime(
                                            $rule
                                                ->start_time
                                        ),

                                    'end_time' => $this
                                        ->formatTime(
                                            $rule
                                                ->end_time
                                        ),

                                    'mode' => $rule->mode,

                                    'slot_duration_minutes' => $rule
                                        ->slot_duration_minutes,

                                    'buffer_minutes' => $rule
                                        ->buffer_minutes,

                                    'capacity_per_slot' => $rule
                                        ->capacity_per_slot,

                                    'timezone' => $rule
                                        ->timezone,

                                    'breaks' => $includeBreaks
                                            ? $rule
                                                ->activeBreaks
                                                ->map(
                                                    fn (
                                                        $break
                                                    ): array => [
                                                        'id' => $break
                                                            ->id,

                                                        'title' => $break
                                                            ->title,

                                                        'start_time' => $this
                                                            ->formatTime(
                                                                $break
                                                                    ->start_time
                                                            ),

                                                        'end_time' => $this
                                                            ->formatTime(
                                                                $break
                                                                    ->end_time
                                                            ),
                                                    ]
                                                )
                                                ->values()
                                            : [],
                                ]
                            )
                            ->values(),
                    ];
                }
            )
            ->values()
            ->all();
    }

    private function firstAvailableAttribute(
        Model $model,
        array $attributes
    ): mixed {
        foreach (
            $attributes as $attribute
        ) {
            if (
                array_key_exists(
                    $attribute,
                    $model
                        ->getAttributes()
                )
            ) {
                return $model
                    ->getAttribute(
                        $attribute
                    );
            }
        }

        return null;
    }

    private function formatTime(
        mixed $value
    ): ?string {
        if (! $value) {
            return null;
        }

        if (
            method_exists(
                $value,
                'format'
            )
        ) {
            return $value
                ->format('H:i');
        }

        return substr(
            (string) $value,
            0,
            5
        );
    }
}
