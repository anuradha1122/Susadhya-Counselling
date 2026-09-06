<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCounsellorRequest;
use App\Http\Requests\Admin\UpdateCounsellorRequest;
use App\Models\CounsellorProfile;
use App\Models\Language;
use App\Models\Specialization;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class CounsellorController extends Controller
{
    private const PROFILE_PHOTO_DIRECTORY =
        'counsellors/profile-photos';

    public function index(
        Request $request
    ): Response {
        $this->authorize(
            'viewAny',
            CounsellorProfile::class
        );

        $filters =
            $request->validate([
                'search' => [
                    'nullable',
                    'string',
                    'max:100',
                ],
                'status' => [
                    'nullable',
                    'in:active,inactive,archived',
                ],
                'specialization_id' => [
                    'nullable',
                    'integer',
                    'exists:specializations,id',
                ],
            ]);

        $counsellors =
            CounsellorProfile::query()
                ->with([
                    'user:id,name,email,profile_photo_path',
                    'specializations:id,name',
                    'languages:id,name,code',
                ])
                ->when(
                    $filters['search']
                        ?? null,
                    function (
                        $query,
                        string $search
                    ): void {
                        $query->where(
                            function (
                                $query
                            ) use (
                                $search
                            ): void {
                                $query
                                    ->where(
                                        'registration_number',
                                        'like',
                                        "%{$search}%"
                                    )
                                    ->orWhere(
                                        'professional_title',
                                        'like',
                                        "%{$search}%"
                                    )
                                    ->orWhere(
                                        'nic',
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
                                            $query
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
                                    );
                            }
                        );
                    }
                )
                ->when(
                    $filters['status']
                        ?? null,
                    fn (
                        $query,
                        string $status
                    ) => $query->where(
                        'status',
                        $status
                    )
                )
                ->when(
                    $filters[
                        'specialization_id'
                    ]
                        ?? null,
                    fn (
                        $query,
                        int|string $specializationId
                    ) => $query
                        ->whereHas(
                            'specializations',
                            fn ($query) => $query
                                ->whereKey(
                                    $specializationId
                                )
                        )
                )
                ->latest()
                ->paginate(15)
                ->withQueryString()
                ->through(
                    fn (
                        CounsellorProfile $counsellor
                    ) => [
                        'id' => $counsellor->id,
                        'uuid' => $counsellor->uuid,

                        'registration_number' => $counsellor
                            ->registration_number,

                        'professional_title' => $counsellor
                            ->professional_title,

                        'years_of_experience' => $counsellor
                            ->years_of_experience,

                        'city' => $counsellor->city,

                        'status' => $counsellor->status,

                        'user' => $counsellor->user
                                ? [
                                    'id' => $counsellor
                                        ->user
                                        ->id,

                                    'name' => $counsellor
                                        ->user
                                        ->name,

                                    'email' => $counsellor
                                        ->user
                                        ->email,

                                    'profile_photo_url' => $counsellor
                                        ->user
                                        ->profile_photo_url,
                                ]
                                : null,

                        'specializations' => $counsellor
                            ->specializations,

                        'languages' => $counsellor
                            ->languages,
                    ]
                );

        return Inertia::render(
            'Admin/Counsellors/Index',
            [
                'counsellors' => $counsellors,

                'filters' => [
                    'search' => $filters['search']
                            ?? '',

                    'status' => $filters['status']
                            ?? '',

                    'specialization_id' => isset(
                        $filters[
                            'specialization_id'
                        ]
                    )
                            ? (string) $filters[
                                'specialization_id'
                            ]
                            : '',
                ],

                'specializations' => Specialization::query()
                    ->where(
                        'is_active',
                        true
                    )
                    ->orderBy('name')
                    ->get([
                        'id',
                        'name',
                    ]),

                'permissions' => [
                    'create' => $request
                        ->user()
                        ->can(
                            'counsellors.create'
                        ),

                    'update' => $request
                        ->user()
                        ->can(
                            'counsellors.update'
                        ),

                    'archive' => $request
                        ->user()
                        ->can(
                            'counsellors.archive'
                        ),
                ],
            ]
        );
    }

    public function create(): Response
    {
        $this->authorize(
            'create',
            CounsellorProfile::class
        );

        return Inertia::render(
            'Admin/Counsellors/Create',
            $this->formOptions()
        );
    }

    public function store(
        StoreCounsellorRequest $request
    ): RedirectResponse {
        $validated =
            $request->validated();

        $newPhotoPath =
            $this->storeProfilePhoto(
                $request
            );

        $previousPhotoPath = null;

        try {
            $counsellor =
                DB::transaction(
                    function () use (
                        $validated,
                        $newPhotoPath,
                        &$previousPhotoPath
                    ): CounsellorProfile {
                        $counsellor =
                            CounsellorProfile::create(
                                $this->profileData(
                                    $validated
                                )
                            );

                        $this->saveRelationships(
                            $counsellor,
                            $validated
                        );

                        $user =
                            $counsellor
                                ->user;

                        $previousPhotoPath =
                            $user
                                ->profile_photo_path;

                        if (
                            ! $user->hasRole(
                                'counsellor'
                            )
                        ) {
                            $user->assignRole(
                                'counsellor'
                            );
                        }

                        $userData = [];

                        if (
                            $newPhotoPath
                            !== null
                        ) {
                            $userData[
                                'profile_photo_path'
                            ] =
                                $newPhotoPath;
                        }

                        if (
                            $counsellor
                                ->status
                            === 'inactive'
                        ) {
                            $userData[
                                'is_active'
                            ] = false;
                        }

                        if (
                            $userData
                            !== []
                        ) {
                            $user->update(
                                $userData
                            );
                        }

                        return $counsellor;
                    }
                );
        } catch (Throwable $exception) {
            $this->deleteManagedProfilePhoto(
                $newPhotoPath
            );

            throw $exception;
        }

        if (
            $newPhotoPath !== null
            && $previousPhotoPath
                !== $newPhotoPath
        ) {
            $this->deleteManagedProfilePhoto(
                $previousPhotoPath
            );
        }

        return to_route(
            'admin.counsellors.show',
            $counsellor
        )->with(
            'success',
            'Counsellor profile created successfully.'
        );
    }

    public function show(
        CounsellorProfile $counsellor
    ): Response {
        $this->authorize(
            'view',
            $counsellor
        );

        $counsellor->load([
            'user:id,name,email,is_active,profile_photo_path',
            'specializations:id,name',
            'languages:id,name,code',
            'qualifications',
            'archivedBy:id,name',
        ]);

        $counsellor
            ->user
            ?->append(
                'profile_photo_url'
            );

        return Inertia::render(
            'Admin/Counsellors/Show',
            [
                'counsellor' => $counsellor,

                'permissions' => [
                    'update' => request()
                        ->user()
                        ->can(
                            'update',
                            $counsellor
                        ),

                    'archive' => request()
                        ->user()
                        ->can(
                            'delete',
                            $counsellor
                        ),

                    'restore' => request()
                        ->user()
                        ->can(
                            'restore',
                            $counsellor
                        ),
                ],
            ]
        );
    }

    public function edit(
        CounsellorProfile $counsellor
    ): Response {
        Gate::authorize(
            'update',
            $counsellor
        );

        $counsellor->load([
            'user:id,name,email,is_active,profile_photo_path',
            'specializations:id,name',
            'languages:id,name,code',
            'qualifications',
        ]);

        $counsellor
            ->user
            ?->append(
                'profile_photo_url'
            );

        return Inertia::render(
            'Admin/Counsellors/Edit',
            [
                'counsellor' => $counsellor,

                ...$this
                    ->formOptions(
                        $counsellor
                    ),
            ]
        );
    }

    public function update(
        UpdateCounsellorRequest $request,
        CounsellorProfile $counsellor
    ): RedirectResponse {
        $validated =
            $request->validated();

        $newPhotoPath =
            $this->storeProfilePhoto(
                $request
            );

        $removeProfilePhoto =
            (bool) (
                $validated[
                    'remove_profile_photo'
                ]
                ?? false
            );

        $previousPhotoPath = null;

        try {
            DB::transaction(
                function () use (
                    $counsellor,
                    $validated,
                    $newPhotoPath,
                    $removeProfilePhoto,
                    &$previousPhotoPath
                ): void {
                    $counsellor->update(
                        $this->profileData(
                            $validated
                        )
                    );

                    $this->saveRelationships(
                        $counsellor,
                        $validated
                    );

                    $counsellor
                        ->unsetRelation(
                            'user'
                        );

                    $user =
                        $counsellor
                            ->user()
                            ->firstOrFail();

                    $previousPhotoPath =
                        $user
                            ->profile_photo_path;

                    if (
                        ! $user->hasRole(
                            'counsellor'
                        )
                    ) {
                        $user->assignRole(
                            'counsellor'
                        );
                    }

                    $userData = [
                        'is_active' => $counsellor
                            ->status
                            === 'active',
                    ];

                    if (
                        $newPhotoPath
                        !== null
                    ) {
                        $userData[
                            'profile_photo_path'
                        ] =
                            $newPhotoPath;
                    } elseif (
                        $removeProfilePhoto
                    ) {
                        $userData[
                            'profile_photo_path'
                        ] = null;
                    }

                    $user->update(
                        $userData
                    );
                }
            );
        } catch (Throwable $exception) {
            $this->deleteManagedProfilePhoto(
                $newPhotoPath
            );

            throw $exception;
        }

        if (
            (
                $newPhotoPath
                !== null
                || $removeProfilePhoto
            )
            && $previousPhotoPath
                !== $newPhotoPath
        ) {
            $this->deleteManagedProfilePhoto(
                $previousPhotoPath
            );
        }

        return to_route(
            'admin.counsellors.show',
            $counsellor
        )->with(
            'success',
            'Counsellor profile updated successfully.'
        );
    }

    public function destroy(
        CounsellorProfile $counsellor
    ): RedirectResponse {
        $this->authorize(
            'delete',
            $counsellor
        );

        DB::transaction(
            function () use (
                $counsellor
            ): void {
                $counsellor->update([
                    'status' => 'archived',

                    'archived_at' => now(),

                    'archived_by' => request()
                        ->user()
                        ->id,
                ]);

                $counsellor
                    ->user
                    ->update([
                        'is_active' => false,
                    ]);
            }
        );

        return to_route(
            'admin.counsellors.index'
        )->with(
            'success',
            'Counsellor archived successfully.'
        );
    }

    public function restore(
        CounsellorProfile $counsellor
    ): RedirectResponse {
        $this->authorize(
            'restore',
            $counsellor
        );

        DB::transaction(
            function () use (
                $counsellor
            ): void {
                $counsellor->update([
                    'status' => 'active',

                    'archived_at' => null,

                    'archived_by' => null,
                ]);

                $counsellor
                    ->user
                    ->update([
                        'is_active' => true,
                    ]);
            }
        );

        return to_route(
            'admin.counsellors.show',
            $counsellor
        )->with(
            'success',
            'Counsellor restored successfully.'
        );
    }

    private function formOptions(
        ?CounsellorProfile $currentCounsellor = null
    ): array {
        $users =
            User::query()
                ->where(
                    function (
                        $query
                    ) use (
                        $currentCounsellor
                    ): void {
                        $query->where(
                            'is_active',
                            true
                        );

                        if (
                            $currentCounsellor
                        ) {
                            $query
                                ->orWhere(
                                    'id',
                                    $currentCounsellor
                                        ->user_id
                                );
                        }
                    }
                )
                ->where(
                    function (
                        $query
                    ) use (
                        $currentCounsellor
                    ): void {
                        $query
                            ->whereDoesntHave(
                                'counsellorProfile'
                            );

                        if (
                            $currentCounsellor
                        ) {
                            $query
                                ->orWhere(
                                    'id',
                                    $currentCounsellor
                                        ->user_id
                                );
                        }
                    }
                )
                ->orderBy('name')
                ->get([
                    'id',
                    'name',
                    'email',
                    'profile_photo_path',
                ])
                ->map(
                    fn (
                        User $user
                    ): array => [
                        'id' => $user->id,

                        'name' => $user->name,

                        'email' => $user->email,

                        'profile_photo_url' => $user
                            ->profile_photo_url,
                    ]
                )
                ->values();

        return [
            'users' => $users,

            'specializations' => Specialization::query()
                ->where(
                    'is_active',
                    true
                )
                ->orderBy('name')
                ->get([
                    'id',
                    'name',
                ]),

            'languages' => Language::query()
                ->where(
                    'is_active',
                    true
                )
                ->orderBy('name')
                ->get([
                    'id',
                    'name',
                    'code',
                ]),
        ];
    }

    private function profileData(
        array $validated
    ): array {
        return collect(
            $validated
        )
            ->only([
                'user_id',
                'registration_number',
                'professional_title',
                'nic',
                'date_of_birth',
                'gender',
                'years_of_experience',
                'biography',
                'address',
                'city',
                'status',
            ])
            ->all();
    }

    private function saveRelationships(
        CounsellorProfile $counsellor,
        array $validated
    ): void {
        $counsellor
            ->specializations()
            ->sync(
                $validated[
                    'specialization_ids'
                ]
                    ?? []
            );

        $languageSync =
            collect(
                $validated[
                    'languages'
                ]
                    ?? []
            )
                ->mapWithKeys(
                    fn (
                        array $language
                    ) => [
                        $language[
                            'language_id'
                        ] => [
                            'proficiency' => $language[
                                    'proficiency'
                                ],
                        ],
                    ]
                )
                ->all();

        $counsellor
            ->languages()
            ->sync(
                $languageSync
            );

        $counsellor
            ->qualifications()
            ->delete();

        if (
            ! empty(
                $validated[
                    'qualifications'
                ]
            )
        ) {
            $counsellor
                ->qualifications()
                ->createMany(
                    $validated[
                        'qualifications'
                    ]
                );
        }
    }

    private function storeProfilePhoto(
        Request $request
    ): ?string {
        if (
            ! $request->hasFile(
                'profile_photo'
            )
        ) {
            return null;
        }

        return $request
            ->file(
                'profile_photo'
            )
            ->store(
                self::PROFILE_PHOTO_DIRECTORY
                    .'/'
                    .$request->integer(
                        'user_id'
                    ),
                'public'
            );
    }

    private function deleteManagedProfilePhoto(
        ?string $path
    ): void {
        if (blank($path)) {
            return;
        }

        $normalized =
            ltrim(
                $path,
                '/'
            );

        if (
            ! str_starts_with(
                $normalized,
                self::PROFILE_PHOTO_DIRECTORY
                    .'/'
            )
        ) {
            return;
        }

        Storage::disk(
            'public'
        )->delete(
            $normalized
        );
    }
}
