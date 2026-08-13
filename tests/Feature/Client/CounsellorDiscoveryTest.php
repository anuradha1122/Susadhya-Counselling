<?php

use App\Models\CounsellorAvailabilityBreak;
use App\Models\CounsellorAvailabilityRule;
use App\Models\CounsellorProfile;
use App\Models\Language;
use App\Models\Specialization;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    app(PermissionRegistrar::class)->forgetCachedPermissions();

    $this->seed(RolePermissionSeeder::class);
});

function createClientUserForCounsellorDiscoveryModule(): User
{
    $user = User::factory()->create([
        'name' => 'Discovery Client',
        'email' => 'discovery.client@example.com',
        'is_active' => true,
    ]);

    $user->assignRole('client');

    return $user;
}

function createSpecializationForCounsellorDiscoveryModule(string $name): Specialization
{
    return Specialization::query()->create([
        'name' => $name,
        'slug' => str($name)->slug()->append('-', fake()->unique()->numberBetween(1000, 9999))->toString(),
        'description' => "{$name} counselling support.",
        'is_active' => true,
    ]);
}

function createLanguageForCounsellorDiscoveryModule(string $name, string $code): Language
{
    return Language::query()->create([
        'name' => $name,
        'code' => $code,
        'is_active' => true,
    ]);
}

function createDiscoverableCounsellorForModule(
    array $userOverrides = [],
    array $profileOverrides = []
): CounsellorProfile {
    $user = User::factory()->create(array_merge([
        'name' => fake()->name(),
        'email' => fake()->unique()->safeEmail(),
        'phone' => '+94 77 123 4567',
        'is_active' => true,
    ], $userOverrides));

    $user->assignRole('counsellor');

    return CounsellorProfile::factory()->create(array_merge([
        'user_id' => $user->id,
        'professional_title' => 'Clinical Counsellor',
        'years_of_experience' => 5,
        'biography' => 'Experienced counsellor supporting clients with emotional wellbeing.',
        'city' => 'Colombo',
        'status' => 'active',
    ], $profileOverrides));
}

function attachDiscoveryMetadata(
    CounsellorProfile $profile,
    Specialization $specialization,
    Language $language,
    string $proficiency = 'fluent'
): void {
    $profile->specializations()->syncWithoutDetaching([
        $specialization->id,
    ]);

    $profile->languages()->syncWithoutDetaching([
        $language->id => [
            'proficiency' => $proficiency,
        ],
    ]);
}

function addDiscoveryAvailability(
    CounsellorProfile $profile,
    int $dayOfWeek,
    string $mode,
    string $startTime = '09:00',
    string $endTime = '17:00'
): CounsellorAvailabilityRule {
    return CounsellorAvailabilityRule::factory()
        ->for($profile)
        ->create([
            'day_of_week' => $dayOfWeek,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'mode' => $mode,
            'is_active' => true,
        ]);
}

it('allows a client to view counsellor discovery', function (): void {
    $client = createClientUserForCounsellorDiscoveryModule();

    $this
        ->actingAs($client)
        ->get(route('client.counsellors.index'))
        ->assertOk();
});

it('shows only active counsellors with active user accounts', function (): void {
    $client = createClientUserForCounsellorDiscoveryModule();

    createDiscoverableCounsellorForModule([
        'name' => 'Visible Counsellor',
        'email' => 'visible.counsellor@example.com',
        'is_active' => true,
    ], [
        'status' => 'active',
    ]);

    createDiscoverableCounsellorForModule([
        'name' => 'Inactive User Counsellor',
        'email' => 'inactive.user.counsellor@example.com',
        'is_active' => false,
    ], [
        'status' => 'active',
    ]);

    createDiscoverableCounsellorForModule([
        'name' => 'Archived Counsellor',
        'email' => 'archived.counsellor@example.com',
        'is_active' => true,
    ], [
        'status' => 'archived',
    ]);

    $response = $this
        ->actingAs($client)
        ->get(route('client.counsellors.index'));

    $response->assertOk();

    $counsellors = $response->viewData('page')['props']['counsellors']['data'];

    expect($counsellors)
        ->toHaveCount(1)
        ->and($counsellors[0]['name'])->toBe('Visible Counsellor');
});

it('filters counsellors by keyword search', function (): void {
    $client = createClientUserForCounsellorDiscoveryModule();

    createDiscoverableCounsellorForModule([
        'name' => 'Anxiety Support Counsellor',
        'email' => 'anxiety.support@example.com',
    ], [
        'professional_title' => 'Anxiety Specialist',
        'biography' => 'Supports clients with stress and anxiety.',
        'city' => 'Kandy',
    ]);

    createDiscoverableCounsellorForModule([
        'name' => 'Family Wellness Counsellor',
        'email' => 'family.wellness@example.com',
    ], [
        'professional_title' => 'Family Counsellor',
        'biography' => 'Supports families and couples.',
        'city' => 'Galle',
    ]);

    $response = $this
        ->actingAs($client)
        ->get(route('client.counsellors.index', [
            'search' => 'Anxiety',
        ]));

    $response->assertOk();

    $counsellors = $response->viewData('page')['props']['counsellors']['data'];

    expect($counsellors)
        ->toHaveCount(1)
        ->and($counsellors[0]['name'])->toBe('Anxiety Support Counsellor');
});

it('filters counsellors by specialization', function (): void {
    $client = createClientUserForCounsellorDiscoveryModule();

    $stress = createSpecializationForCounsellorDiscoveryModule('Stress Management');
    $family = createSpecializationForCounsellorDiscoveryModule('Family Counselling');
    $english = createLanguageForCounsellorDiscoveryModule('English', 'en');

    $stressCounsellor = createDiscoverableCounsellorForModule([
        'name' => 'Stress Specialist',
        'email' => 'stress.specialist@example.com',
    ]);

    $familyCounsellor = createDiscoverableCounsellorForModule([
        'name' => 'Family Specialist',
        'email' => 'family.specialist@example.com',
    ]);

    attachDiscoveryMetadata($stressCounsellor, $stress, $english);
    attachDiscoveryMetadata($familyCounsellor, $family, $english);

    $response = $this
        ->actingAs($client)
        ->get(route('client.counsellors.index', [
            'specialization_id' => $stress->id,
        ]));

    $response->assertOk();

    $counsellors = $response->viewData('page')['props']['counsellors']['data'];

    expect($counsellors)
        ->toHaveCount(1)
        ->and($counsellors[0]['name'])->toBe('Stress Specialist')
        ->and($counsellors[0]['specializations'][0]['name'])->toBe('Stress Management');
});

it('filters counsellors by language', function (): void {
    $client = createClientUserForCounsellorDiscoveryModule();

    $stress = createSpecializationForCounsellorDiscoveryModule('Stress Management');
    $english = createLanguageForCounsellorDiscoveryModule('English', 'en');
    $sinhala = createLanguageForCounsellorDiscoveryModule('Sinhala', 'si');

    $englishCounsellor = createDiscoverableCounsellorForModule([
        'name' => 'English Counsellor',
        'email' => 'english.counsellor@example.com',
    ]);

    $sinhalaCounsellor = createDiscoverableCounsellorForModule([
        'name' => 'Sinhala Counsellor',
        'email' => 'sinhala.counsellor@example.com',
    ]);

    attachDiscoveryMetadata($englishCounsellor, $stress, $english);
    attachDiscoveryMetadata($sinhalaCounsellor, $stress, $sinhala);

    $response = $this
        ->actingAs($client)
        ->get(route('client.counsellors.index', [
            'language_id' => $sinhala->id,
        ]));

    $response->assertOk();

    $counsellors = $response->viewData('page')['props']['counsellors']['data'];

    expect($counsellors)
        ->toHaveCount(1)
        ->and($counsellors[0]['name'])->toBe('Sinhala Counsellor')
        ->and($counsellors[0]['languages'][0]['name'])->toBe('Sinhala');
});

it('filters counsellors by counselling mode', function (): void {
    $client = createClientUserForCounsellorDiscoveryModule();

    $onlineCounsellor = createDiscoverableCounsellorForModule([
        'name' => 'Online Counsellor',
        'email' => 'online.counsellor@example.com',
    ]);

    $inPersonCounsellor = createDiscoverableCounsellorForModule([
        'name' => 'In Person Counsellor',
        'email' => 'in.person.counsellor@example.com',
    ]);

    addDiscoveryAvailability(
        $onlineCounsellor,
        CounsellorAvailabilityRule::MONDAY,
        CounsellorAvailabilityRule::MODE_ONLINE
    );

    addDiscoveryAvailability(
        $inPersonCounsellor,
        CounsellorAvailabilityRule::MONDAY,
        CounsellorAvailabilityRule::MODE_IN_PERSON
    );

    $response = $this
        ->actingAs($client)
        ->get(route('client.counsellors.index', [
            'mode' => CounsellorAvailabilityRule::MODE_ONLINE,
        ]));

    $response->assertOk();

    $counsellors = $response->viewData('page')['props']['counsellors']['data'];

    expect($counsellors)
        ->toHaveCount(1)
        ->and($counsellors[0]['name'])->toBe('Online Counsellor');
});

it('includes both-mode counsellors when filtering by online or in-person mode', function (): void {
    $client = createClientUserForCounsellorDiscoveryModule();

    $bothModeCounsellor = createDiscoverableCounsellorForModule([
        'name' => 'Flexible Counsellor',
        'email' => 'flexible.counsellor@example.com',
    ]);

    addDiscoveryAvailability(
        $bothModeCounsellor,
        CounsellorAvailabilityRule::MONDAY,
        CounsellorAvailabilityRule::MODE_BOTH
    );

    $response = $this
        ->actingAs($client)
        ->get(route('client.counsellors.index', [
            'mode' => CounsellorAvailabilityRule::MODE_ONLINE,
        ]));

    $response->assertOk();

    $counsellors = $response->viewData('page')['props']['counsellors']['data'];

    expect($counsellors)
        ->toHaveCount(1)
        ->and($counsellors[0]['name'])->toBe('Flexible Counsellor');
});

it('filters counsellors by availability day', function (): void {
    $client = createClientUserForCounsellorDiscoveryModule();

    $mondayCounsellor = createDiscoverableCounsellorForModule([
        'name' => 'Monday Counsellor',
        'email' => 'monday.counsellor@example.com',
    ]);

    $tuesdayCounsellor = createDiscoverableCounsellorForModule([
        'name' => 'Tuesday Counsellor',
        'email' => 'tuesday.counsellor@example.com',
    ]);

    addDiscoveryAvailability(
        $mondayCounsellor,
        CounsellorAvailabilityRule::MONDAY,
        CounsellorAvailabilityRule::MODE_BOTH
    );

    addDiscoveryAvailability(
        $tuesdayCounsellor,
        CounsellorAvailabilityRule::TUESDAY,
        CounsellorAvailabilityRule::MODE_BOTH
    );

    $response = $this
        ->actingAs($client)
        ->get(route('client.counsellors.index', [
            'availability_day' => CounsellorAvailabilityRule::TUESDAY,
        ]));

    $response->assertOk();

    $counsellors = $response->viewData('page')['props']['counsellors']['data'];

    expect($counsellors)
        ->toHaveCount(1)
        ->and($counsellors[0]['name'])->toBe('Tuesday Counsellor');
});

it('allows a client to view active counsellor profile detail', function (): void {
    $client = createClientUserForCounsellorDiscoveryModule();

    $stress = createSpecializationForCounsellorDiscoveryModule('Stress Management');
    $english = createLanguageForCounsellorDiscoveryModule('English', 'en');

    $counsellor = createDiscoverableCounsellorForModule([
        'name' => 'Profile Detail Counsellor',
        'email' => 'profile.detail@example.com',
    ], [
        'professional_title' => 'Senior Counsellor',
        'years_of_experience' => 8,
        'biography' => 'Detailed counsellor biography.',
        'city' => 'Colombo',
    ]);

    attachDiscoveryMetadata($counsellor, $stress, $english);

    $rule = addDiscoveryAvailability(
        $counsellor,
        CounsellorAvailabilityRule::WEDNESDAY,
        CounsellorAvailabilityRule::MODE_BOTH,
        '10:00',
        '16:00'
    );

    CounsellorAvailabilityBreak::factory()
        ->for($rule, 'availabilityRule')
        ->create([
            'title' => 'Lunch',
            'start_time' => '12:00',
            'end_time' => '13:00',
            'is_active' => true,
        ]);

    $response = $this
        ->actingAs($client)
        ->get(route('client.counsellors.show', $counsellor));

    $response->assertOk();

    $payload = $response->viewData('page')['props']['counsellor'];

    expect($payload['name'])->toBe('Profile Detail Counsellor')
        ->and($payload['professional_title'])->toBe('Senior Counsellor')
        ->and($payload['years_of_experience'])->toBe(8)
        ->and($payload['specializations'][0]['name'])->toBe('Stress Management')
        ->and($payload['languages'][0]['name'])->toBe('English')
        ->and($payload['availability_summary'][0]['day_name'])->toBe('Wednesday')
        ->and($payload['availability_summary'][0]['slots'][0]['breaks'][0]['title'])->toBe('Lunch');
});

it('returns not found for archived counsellor profile detail', function (): void {
    $client = createClientUserForCounsellorDiscoveryModule();

    $archivedCounsellor = createDiscoverableCounsellorForModule([
        'name' => 'Archived Detail Counsellor',
        'email' => 'archived.detail@example.com',
    ], [
        'status' => 'archived',
    ]);

    $this
        ->actingAs($client)
        ->get(route('client.counsellors.show', $archivedCounsellor))
        ->assertNotFound();
});

it('returns not found for counsellor profile detail when user account is inactive', function (): void {
    $client = createClientUserForCounsellorDiscoveryModule();

    $inactiveCounsellor = createDiscoverableCounsellorForModule([
        'name' => 'Inactive Detail Counsellor',
        'email' => 'inactive.detail@example.com',
        'is_active' => false,
    ], [
        'status' => 'active',
    ]);

    $this
        ->actingAs($client)
        ->get(route('client.counsellors.show', $inactiveCounsellor))
        ->assertNotFound();
});
