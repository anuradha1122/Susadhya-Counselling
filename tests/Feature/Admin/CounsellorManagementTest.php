<?php

use App\Models\CounsellorProfile;
use App\Models\Language;
use App\Models\Specialization;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(RolePermissionSeeder::class);
});

function adminWithPermissions(array $permissions): User
{
    $user = User::factory()->create([
        'is_active' => true,
        'email_verified_at' => now(),
    ]);

    $user->givePermissionTo([
        'dashboard.admin.view',
        ...$permissions,
    ]);

    return $user;
}

function counsellorPayload(
    User $user,
    array $overrides = []
): array {
    return array_replace_recursive([
        'user_id' => $user->id,
        'registration_number' => 'COUN-00001',
        'professional_title' => 'Senior Counsellor',
        'nic' => '199012345678',
        'date_of_birth' => '1990-01-15',
        'gender' => 'female',
        'years_of_experience' => 8,
        'biography' => 'Experienced professional counsellor.',
        'address' => '10 Main Street',
        'city' => 'Kandy',
        'status' => 'active',
        'specialization_ids' => [],
        'languages' => [],
        'qualifications' => [],
    ], $overrides);
}

test('guests are redirected from counsellor management', function () {
    $this->get(route('admin.counsellors.index'))
        ->assertRedirect();

    $this->get(route('admin.counsellors.create'))
        ->assertRedirect();
});

test('users without permission cannot view counsellors', function () {
    $user = User::factory()->create([
        'is_active' => true,
        'email_verified_at' => now(),
    ]);

    $this->actingAs($user)
        ->get(route('admin.counsellors.index'))
        ->assertForbidden();
});

test('authorized administrators can view the counsellor list', function () {
    $admin = adminWithPermissions(['counsellors.view']);

    $counsellor = CounsellorProfile::factory()->create();

    $this->actingAs($admin)
        ->get(route('admin.counsellors.index'))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('Admin/Counsellors/Index')
                ->has('counsellors.data', 1)
                ->where(
                    'counsellors.data.0.id',
                    $counsellor->id
                )
                ->has('filters')
                ->has('specializations')
                ->has('permissions')
        );
});

test('the counsellor list can be searched by user name', function () {
    $admin = adminWithPermissions(['counsellors.view']);

    $matchingUser = User::factory()->create([
        'name' => 'Nimali Perera',
    ]);

    $otherUser = User::factory()->create([
        'name' => 'Kasun Silva',
    ]);

    $matching = CounsellorProfile::factory()->create([
        'user_id' => $matchingUser->id,
    ]);

    CounsellorProfile::factory()->create([
        'user_id' => $otherUser->id,
    ]);

    $this->actingAs($admin)
        ->get(route('admin.counsellors.index', [
            'search' => 'Nimali',
        ]))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->has('counsellors.data', 1)
                ->where(
                    'counsellors.data.0.id',
                    $matching->id
                )
                ->where('filters.search', 'Nimali')
        );
});

test('the counsellor list can be filtered by status', function () {
    $admin = adminWithPermissions(['counsellors.view']);

    CounsellorProfile::factory()->create([
        'status' => 'active',
    ]);

    $inactive = CounsellorProfile::factory()->create([
        'status' => 'inactive',
    ]);

    $this->actingAs($admin)
        ->get(route('admin.counsellors.index', [
            'status' => 'inactive',
        ]))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->has('counsellors.data', 1)
                ->where(
                    'counsellors.data.0.id',
                    $inactive->id
                )
                ->where('filters.status', 'inactive')
        );
});

test('the counsellor list can be filtered by specialization', function () {
    $admin = adminWithPermissions(['counsellors.view']);

    $trauma = Specialization::query()->create([
        'name' => 'Trauma Counselling',
        'slug' => 'trauma-counselling',
        'is_active' => true,
    ]);

    $career = Specialization::query()->create([
        'name' => 'Career Counselling',
        'slug' => 'career-counselling',
        'is_active' => true,
    ]);

    $matching = CounsellorProfile::factory()->create();
    $matching->specializations()->attach($trauma);

    $other = CounsellorProfile::factory()->create();
    $other->specializations()->attach($career);

    $this->actingAs($admin)
        ->get(route('admin.counsellors.index', [
            'specialization_id' => $trauma->id,
        ]))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->has('counsellors.data', 1)
                ->where(
                    'counsellors.data.0.id',
                    $matching->id
                )
                ->where(
                    'filters.specialization_id',
                    (string) $trauma->id
                )
        );
});

test('create page only offers eligible active users', function () {
    $admin = adminWithPermissions([
        'counsellors.create',
    ]);

    $eligibleUser = User::factory()->create([
        'is_active' => true,
    ]);

    $inactiveUser = User::factory()->create([
        'is_active' => false,
    ]);

    $assignedUser = User::factory()->create([
        'is_active' => true,
    ]);

    CounsellorProfile::factory()->create([
        'user_id' => $assignedUser->id,
    ]);

    $this->actingAs($admin)
        ->get(route('admin.counsellors.create'))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('Admin/Counsellors/Create')
                ->where(
                    'users',
                    fn ($users) => collect($users)
                        ->pluck('id')
                        ->contains($eligibleUser->id)
                        && ! collect($users)
                            ->pluck('id')
                            ->contains($inactiveUser->id)
                        && ! collect($users)
                            ->pluck('id')
                            ->contains($assignedUser->id)
                )
        );
});

test('an authorized administrator can create a complete counsellor profile', function () {
    $admin = adminWithPermissions([
        'counsellors.create',
        'counsellors.view',
    ]);

    $user = User::factory()->create([
        'is_active' => true,
    ]);

    $specialization = Specialization::query()->create([
        'name' => 'Career Counselling',
        'slug' => 'career-counselling',
        'is_active' => true,
    ]);

    $language = Language::query()->create([
        'name' => 'Sinhala',
        'code' => 'si',
        'is_active' => true,
    ]);

    $response = $this->actingAs($admin)
        ->post(
            route('admin.counsellors.store'),
            counsellorPayload($user, [
                'specialization_ids' => [
                    $specialization->id,
                ],
                'languages' => [
                    [
                        'language_id' => $language->id,
                        'proficiency' => 'native',
                    ],
                ],
                'qualifications' => [
                    [
                        'qualification' => 'MSc Psychology',
                        'institution' => 'University of Peradeniya',
                        'field_of_study' => 'Psychology',
                        'year_completed' => 2024,
                        'certificate_number' => 'CERT-001',
                    ],
                ],
            ])
        );

    $counsellor = CounsellorProfile::query()
        ->where('user_id', $user->id)
        ->firstOrFail();

    $response->assertRedirect(
        route('admin.counsellors.show', $counsellor)
    );

    $this->assertDatabaseHas('counsellor_profiles', [
        'id' => $counsellor->id,
        'user_id' => $user->id,
        'registration_number' => 'COUN-00001',
        'status' => 'active',
    ]);

    $this->assertDatabaseHas(
        'counsellor_profile_specialization',
        [
            'counsellor_profile_id' => $counsellor->id,
            'specialization_id' => $specialization->id,
        ]
    );

    $this->assertDatabaseHas(
        'counsellor_profile_language',
        [
            'counsellor_profile_id' => $counsellor->id,
            'language_id' => $language->id,
            'proficiency' => 'native',
        ]
    );

    $this->assertDatabaseHas('counsellor_qualifications', [
        'counsellor_profile_id' => $counsellor->id,
        'qualification' => 'MSc Psychology',
        'institution' => 'University of Peradeniya',
        'year_completed' => 2024,
    ]);

    expect($user->fresh()->hasRole('counsellor'))->toBeTrue();
});

test('creating an inactive counsellor disables the user', function () {
    $admin = adminWithPermissions([
        'counsellors.create',
    ]);

    $user = User::factory()->create([
        'is_active' => true,
    ]);

    $this->actingAs($admin)
        ->post(
            route('admin.counsellors.store'),
            counsellorPayload($user, [
                'status' => 'inactive',
            ])
        )
        ->assertRedirect();

    expect($user->fresh()->is_active)->toBeFalse();
});

test('counsellor creation validates unique profile fields', function () {
    $admin = adminWithPermissions([
        'counsellors.create',
    ]);

    $existing = CounsellorProfile::factory()->create([
        'registration_number' => 'COUN-EXISTING',
        'nic' => '901234567V',
    ]);

    $this->actingAs($admin)
        ->from(route('admin.counsellors.create'))
        ->post(
            route('admin.counsellors.store'),
            counsellorPayload($existing->user, [
                'registration_number' => 'COUN-EXISTING',
                'nic' => '901234567V',
            ])
        )
        ->assertSessionHasErrors([
            'user_id',
            'registration_number',
            'nic',
        ]);
});

test('inactive reference values cannot be submitted', function () {
    $admin = adminWithPermissions([
        'counsellors.create',
    ]);

    $user = User::factory()->create([
        'is_active' => true,
    ]);

    $specialization = Specialization::query()->create([
        'name' => 'Inactive Specialization',
        'slug' => 'inactive-specialization',
        'is_active' => false,
    ]);

    $language = Language::query()->create([
        'name' => 'Inactive Language',
        'code' => 'xx',
        'is_active' => false,
    ]);

    $this->actingAs($admin)
        ->post(
            route('admin.counsellors.store'),
            counsellorPayload($user, [
                'specialization_ids' => [
                    $specialization->id,
                ],
                'languages' => [
                    [
                        'language_id' => $language->id,
                        'proficiency' => 'fluent',
                    ],
                ],
            ])
        )
        ->assertSessionHasErrors([
            'specialization_ids.0',
            'languages.0.language_id',
        ]);
});

test('an authorized administrator can view a counsellor profile', function () {
    $admin = adminWithPermissions([
        'counsellors.view',
    ]);

    $counsellor = CounsellorProfile::factory()->create();

    $this->actingAs($admin)
        ->get(route('admin.counsellors.show', $counsellor))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('Admin/Counsellors/Show')
                ->where('counsellor.id', $counsellor->id)
                ->has('counsellor.qualifications')
                ->has('counsellor.specializations')
                ->has('counsellor.languages')
                ->has('permissions')
        );
});

test('an authorized administrator can update relationships and qualifications', function () {
    $admin = adminWithPermissions([
        'counsellors.update',
    ]);

    $counsellor = CounsellorProfile::factory()->create();

    $oldSpecialization = Specialization::query()->create([
        'name' => 'Old Specialization',
        'slug' => 'old-specialization',
        'is_active' => true,
    ]);

    $newSpecialization = Specialization::query()->create([
        'name' => 'New Specialization',
        'slug' => 'new-specialization',
        'is_active' => true,
    ]);

    $language = Language::query()->create([
        'name' => 'English',
        'code' => 'en',
        'is_active' => true,
    ]);

    $counsellor->specializations()->attach(
        $oldSpecialization
    );

    $counsellor->qualifications()->create([
        'qualification' => 'Old Qualification',
        'institution' => 'Old Institution',
        'year_completed' => 2018,
    ]);

    $this->actingAs($admin)
        ->put(
            route('admin.counsellors.update', $counsellor),
            counsellorPayload($counsellor->user, [
                'registration_number' => 'COUN-UPDATED',
                'status' => 'active',
                'specialization_ids' => [
                    $newSpecialization->id,
                ],
                'languages' => [
                    [
                        'language_id' => $language->id,
                        'proficiency' => 'fluent',
                    ],
                ],
                'qualifications' => [
                    [
                        'qualification' => 'Updated Qualification',
                        'institution' => 'Updated Institution',
                        'field_of_study' => 'Counselling',
                        'year_completed' => 2025,
                        'certificate_number' => 'UPDATED-001',
                    ],
                ],
            ])
        )
        ->assertRedirect(
            route('admin.counsellors.show', $counsellor)
        );

    $this->assertDatabaseMissing(
        'counsellor_profile_specialization',
        [
            'counsellor_profile_id' => $counsellor->id,
            'specialization_id' => $oldSpecialization->id,
        ]
    );

    $this->assertDatabaseHas(
        'counsellor_profile_specialization',
        [
            'counsellor_profile_id' => $counsellor->id,
            'specialization_id' => $newSpecialization->id,
        ]
    );

    $this->assertDatabaseMissing(
        'counsellor_qualifications',
        [
            'counsellor_profile_id' => $counsellor->id,
            'qualification' => 'Old Qualification',
        ]
    );

    $this->assertDatabaseHas(
        'counsellor_qualifications',
        [
            'counsellor_profile_id' => $counsellor->id,
            'qualification' => 'Updated Qualification',
            'year_completed' => 2025,
        ]
    );

    expect($counsellor->fresh()->registration_number)
        ->toBe('COUN-UPDATED');

    expect($language->counsellorProfiles()
        ->whereKey($counsellor->id)
        ->first()
        ->pivot
        ->proficiency)
        ->toBe('fluent');
});

test('an inactive counsellor can retain the current inactive user while being edited', function () {
    $admin = adminWithPermissions([
        'counsellors.update',
    ]);

    $user = User::factory()->create([
        'is_active' => false,
    ]);

    $counsellor = CounsellorProfile::factory()->create([
        'user_id' => $user->id,
        'status' => 'inactive',
    ]);

    $this->actingAs($admin)
        ->get(route('admin.counsellors.edit', $counsellor))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('Admin/Counsellors/Edit')
                ->where(
                    'users',
                    fn ($users) => collect($users)
                        ->pluck('id')
                        ->contains($user->id)
                )
        );

    $this->actingAs($admin)
        ->put(
            route('admin.counsellors.update', $counsellor),
            counsellorPayload($user, [
                'registration_number' => $counsellor->registration_number,
                'nic' => $counsellor->nic,
                'status' => 'inactive',
            ])
        )
        ->assertRedirect(
            route('admin.counsellors.show', $counsellor)
        );

    expect($user->fresh()->is_active)->toBeFalse();
});

test('updating a counsellor preserves existing user roles', function () {
    $admin = adminWithPermissions([
        'counsellors.update',
    ]);

    $user = User::factory()->create([
        'is_active' => true,
    ]);

    $user->assignRole('admin');

    $counsellor = CounsellorProfile::factory()->create([
        'user_id' => $user->id,
    ]);

    $this->actingAs($admin)
        ->put(
            route('admin.counsellors.update', $counsellor),
            counsellorPayload($user, [
                'registration_number' => $counsellor->registration_number,
                'nic' => $counsellor->nic,
            ])
        )
        ->assertRedirect();

    $user->refresh();

    expect($user->hasRole('admin'))->toBeTrue()
        ->and($user->hasRole('counsellor'))->toBeTrue();
});

test('an authorized administrator can archive a counsellor', function () {
    $admin = adminWithPermissions([
        'counsellors.archive',
    ]);

    $counsellor = CounsellorProfile::factory()->create([
        'status' => 'active',
    ]);

    $this->actingAs($admin)
        ->delete(
            route('admin.counsellors.destroy', $counsellor)
        )
        ->assertRedirect(route('admin.counsellors.index'));

    $counsellor->refresh();

    expect($counsellor->status)->toBe('archived')
        ->and($counsellor->archived_at)->not->toBeNull()
        ->and($counsellor->archived_by)->toBe($admin->id)
        ->and($counsellor->user->fresh()->is_active)->toBeFalse();
});

test('archived counsellors cannot be edited or archived again', function () {
    $admin = adminWithPermissions([
        'counsellors.update',
        'counsellors.archive',
    ]);

    $counsellor = CounsellorProfile::factory()->create([
        'status' => 'archived',
        'archived_at' => now(),
        'archived_by' => $admin->id,
    ]);

    $this->actingAs($admin)
        ->get(route('admin.counsellors.edit', $counsellor))
        ->assertForbidden();

    $this->actingAs($admin)
        ->delete(
            route('admin.counsellors.destroy', $counsellor)
        )
        ->assertForbidden();
});

test('an authorized administrator can restore an archived counsellor', function () {
    $admin = adminWithPermissions([
        'counsellors.update',
    ]);

    $user = User::factory()->create([
        'is_active' => false,
    ]);

    $counsellor = CounsellorProfile::factory()->create([
        'user_id' => $user->id,
        'status' => 'archived',
        'archived_at' => now(),
        'archived_by' => $admin->id,
    ]);

    $this->actingAs($admin)
        ->patch(
            route('admin.counsellors.restore', $counsellor)
        )
        ->assertRedirect(
            route('admin.counsellors.show', $counsellor)
        );

    $counsellor->refresh();

    expect($counsellor->status)->toBe('active')
        ->and($counsellor->archived_at)->toBeNull()
        ->and($counsellor->archived_by)->toBeNull()
        ->and($user->fresh()->is_active)->toBeTrue();
});

test('users without mutation permissions cannot modify counsellors', function () {
    $viewer = adminWithPermissions([
        'counsellors.view',
    ]);

    $counsellor = CounsellorProfile::factory()->create();

    $this->actingAs($viewer)
        ->get(route('admin.counsellors.edit', $counsellor))
        ->assertForbidden();

    $this->actingAs($viewer)
        ->put(
            route('admin.counsellors.update', $counsellor),
            counsellorPayload($counsellor->user)
        )
        ->assertForbidden();

    $this->actingAs($viewer)
        ->delete(
            route('admin.counsellors.destroy', $counsellor)
        )
        ->assertForbidden();
});
