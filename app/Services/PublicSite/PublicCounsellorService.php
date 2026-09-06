<?php

namespace App\Services\PublicSite;

use App\Models\CounsellorProfile;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class PublicCounsellorService
{
    private ?array $columns = null;

    public function paginate(
        ?string $search = null,
        int $perPage = 12
    ): LengthAwarePaginator {
        $query =
            $this->baseQuery();

        if (filled($search)) {
            $this->applySearch(
                $query,
                $search
            );
        }

        $paginator =
            $query
                ->orderBy('id')
                ->paginate($perPage)
                ->withQueryString();

        $paginator->through(
            fn (
                CounsellorProfile $profile
            ): array => $this->transform(
                $profile
            )
        );

        return $paginator;
    }

    public function featured(
        int $limit = 4
    ): array {
        return $this
            ->baseQuery()
            ->limit($limit)
            ->get()
            ->map(
                fn (
                    CounsellorProfile $profile
                ): array => $this->transform(
                    $profile
                )
            )
            ->values()
            ->all();
    }

    public function findPublic(
        CounsellorProfile $profile
    ): CounsellorProfile {
        $query =
            $this->baseQuery()
                ->whereKey(
                    $profile->getKey()
                );

        return $query
            ->firstOrFail();
    }

    public function transform(
        CounsellorProfile $profile
    ): array {
        $user =
            $profile->relationLoaded('user')
                ? $profile->user
                : $profile->user;

        $qualifications =
            $profile->relationLoaded('qualifications')
                ? $profile->qualifications
                : $profile->qualifications()->get();

        $specializations =
            $profile->relationLoaded('specializations')
                ? $profile->specializations
                : $profile->specializations()->get();

        $languages =
            $profile->relationLoaded('languages')
                ? $profile->languages
                : $profile->languages()->get();

        return [
            'uuid' => $profile->uuid,

            'name' => $user?->name
                ?? 'Counsellor',

            'headline' => $profile->professional_title,

            'registration_number' => $profile->registration_number,

            'years_of_experience' => $profile->years_of_experience,

            'city' => $profile->city,

            'bio' => $profile->biography,

            'profile_image_url' => $this->imageUrl(
                $profile,
                $user
            ),

            'specialties' => $specializations
                ->map(
                    fn ($specialization): array => [
                        'id' => $specialization->id,

                        'name' => $specialization->name,
                    ]
                )
                ->values()
                ->all(),

            'languages' => $languages
                ->map(
                    fn ($language): array => [
                        'id' => $language->id,

                        'name' => $language->name,

                        'code' => $language->code,

                        'proficiency' => $language
                            ->pivot
                            ?->proficiency,
                    ]
                )
                ->values()
                ->all(),

            'qualifications' => $qualifications
                ->map(
                    fn ($qualification): array => [
                        'id' => $qualification->id,

                        'qualification' => $qualification
                            ->qualification,

                        'institution' => $qualification
                            ->institution,

                        'field_of_study' => $qualification
                            ->field_of_study,

                        'year_completed' => $qualification
                            ->year_completed,
                    ]
                )
                ->values()
                ->all(),

            /*
            * There is currently no direct
            * counsellor-to-service relation.
            */
            'services' => [],
        ];
    }

    private function baseQuery(): Builder
    {
        $query =
            CounsellorProfile::query()
                ->with([
                    'user',
                    'qualifications',
                    'specializations',
                    'languages',
                ]);

        $columns =
            $this->columns();

        if (
            in_array(
                'status',
                $columns,
                true
            )
        ) {
            $query->whereIn(
                'status',
                config(
                    'public_site.counsellor_statuses',
                    [
                        'active',
                        'approved',
                        'verified',
                    ]
                )
            );
        }

        if (
            in_array(
                'is_active',
                $columns,
                true
            )
        ) {
            $query->where(
                'is_active',
                true
            );
        }

        if (
            in_array(
                'is_public',
                $columns,
                true
            )
        ) {
            $query->where(
                'is_public',
                true
            );
        }

        if (
            in_array(
                'archived_at',
                $columns,
                true
            )
        ) {
            $query->whereNull(
                'archived_at'
            );
        }

        $query->whereHas(
            'user',
            fn (Builder $userQuery) => $userQuery->where(
                'is_active',
                true
            )
        );

        return $query;
    }

    private function applySearch(
        Builder $query,
        string $search
    ): void {
        $columns =
            $this->columns();

        $searchable =
            array_values(
                array_intersect(
                    [
                        'display_name',
                        'name',
                        'professional_title',
                        'headline',
                        'designation',
                        'bio',
                        'professional_summary',
                        'specialties',
                        'specialisations',
                        'specializations',
                    ],
                    $columns
                )
            );

        $hasUser =
            method_exists(
                CounsellorProfile::class,
                'user'
            );

        if (
            $searchable === []
            && ! $hasUser
        ) {
            return;
        }

        $query->where(
            function (
                Builder $query
            ) use (
                $search,
                $searchable,
                $hasUser
            ): void {
                foreach (
                    $searchable as $index => $column
                ) {
                    if ($index === 0) {
                        $query->where(
                            $column,
                            'like',
                            "%{$search}%"
                        );

                        continue;
                    }

                    $query->orWhere(
                        $column,
                        'like',
                        "%{$search}%"
                    );
                }

                if ($hasUser) {
                    if (
                        $searchable === []
                    ) {
                        $query->whereHas(
                            'user',
                            fn (
                                Builder $userQuery
                            ) => $userQuery
                                ->where(
                                    'name',
                                    'like',
                                    "%{$search}%"
                                )
                        );
                    } else {
                        $query->orWhereHas(
                            'user',
                            fn (
                                Builder $userQuery
                            ) => $userQuery
                                ->where(
                                    'name',
                                    'like',
                                    "%{$search}%"
                                )
                        );
                    }
                }
            }
        );
    }

    private function extractServices(
        CounsellorProfile $profile
    ): array {
        foreach (
            [
                'counsellingServices',
                'services',
            ] as $relation
        ) {
            if (
                ! $profile
                    ->relationLoaded(
                        $relation
                    )
            ) {
                continue;
            }

            $services =
                $profile
                    ->getRelation(
                        $relation
                    );

            if (
                ! $services
                    instanceof Collection
            ) {
                continue;
            }

            return $services
                ->map(
                    function (
                        mixed $service
                    ): array {
                        return [
                            'name' => data_get(
                                $service,
                                'name'
                            ),

                            'slug' => data_get(
                                $service,
                                'slug'
                            ),

                            'short_description' => data_get(
                                $service,
                                'short_description'
                            ),
                        ];
                    }
                )
                ->filter(
                    fn (
                        array $service
                    ): bool => filled(
                        $service[
                            'name'
                        ]
                    )
                )
                ->values()
                ->all();
        }

        return [];
    }

    private function imageUrl(
        CounsellorProfile $profile,
        mixed $user
    ): ?string {
        $value =
            $this->attribute(
                $profile,
                [
                    'profile_photo_path',
                    'profile_image_path',
                    'photo_path',
                    'image_path',
                ]
            );

        if (filled($value)) {
            if (
                Str::startsWith(
                    $value,
                    [
                        'http://',
                        'https://',
                        '/',
                    ]
                )
            ) {
                return $value;
            }

            return asset(
                'storage/'
                .ltrim(
                    $value,
                    '/'
                )
            );
        }

        $userPhoto =
            data_get(
                $user,
                'profile_photo_url'
            );

        return filled($userPhoto)
            ? $userPhoto
            : null;
    }

    private function normaliseList(
        mixed $value
    ): array {
        if (is_array($value)) {
            return array_values(
                array_filter(
                    $value,
                    fn (
                        mixed $item
                    ): bool => filled($item)
                )
            );
        }

        if (
            $value
            instanceof Collection
        ) {
            return $value
                ->filter()
                ->values()
                ->all();
        }

        if (! is_string($value)) {
            return [];
        }

        $decoded =
            json_decode(
                $value,
                true
            );

        if (is_array($decoded)) {
            return array_values(
                array_filter(
                    $decoded
                )
            );
        }

        return collect(
            preg_split(
                '/[,;\n]+/',
                $value
            )
        )
            ->map(
                fn (
                    string $item
                ): string => trim($item)
            )
            ->filter()
            ->values()
            ->all();
    }

    private function attribute(
        CounsellorProfile $profile,
        array $candidates
    ): mixed {
        foreach (
            $candidates as $candidate
        ) {
            if (
                ! in_array(
                    $candidate,
                    $this->columns(),
                    true
                )
            ) {
                continue;
            }

            $value =
                $profile
                    ->getAttribute(
                        $candidate
                    );

            if (filled($value)) {
                return $value;
            }
        }

        return null;
    }

    private function columns(): array
    {
        if ($this->columns !== null) {
            return $this->columns;
        }

        $model =
            new CounsellorProfile;

        $this->columns =
            Schema::getColumnListing(
                $model->getTable()
            );

        return $this->columns;
    }
}
