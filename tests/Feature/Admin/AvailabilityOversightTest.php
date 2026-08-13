<?php

use App\Models\CounsellorAvailabilityBreak;
use App\Models\CounsellorAvailabilityRule;
use App\Models\CounsellorBlockedSlot;
use App\Models\CounsellorLeaveDay;
use App\Models\CounsellorProfile;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    app(PermissionRegistrar::class)->forgetCachedPermissions();

    $this->seed(RolePermissionSeeder::class);
});

function createAdminUserForAvailabilityModule(): User
{
    $user = User::factory()->create([
        'name' => 'Availability Admin',
        'email' => 'availability.admin@example.com',
        'is_active' => true,
    ]);

    $user->assignRole('admin');

    return $user;
}

function createRestrictedAdminForAvailabilityModule(): User
{
    $role = Role::create([
        'name' => 'availability_restricted_admin',
        'guard_name' => 'web',
    ]);

    $role->givePermissionTo([
        'users.view',
    ]);

    $user = User::factory()->create([
        'name' => 'Restricted Availability Admin',
        'email' => 'restricted.availability.admin@example.com',
        'is_active' => true,
    ]);

    $user->assignRole($role);

    return $user;
}

function createCounsellorProfileForAvailabilityOversight(
    array $userOverrides = []
): CounsellorProfile {
    $user = User::factory()->create(array_merge([
        'name' => 'Oversight Counsellor',
        'email' => fake()->unique()->safeEmail(),
        'phone' => '+94 77 123 4567',
        'is_active' => true,
    ], $userOverrides));

    $user->assignRole('counsellor');

    return CounsellorProfile::factory()->create([
        'user_id' => $user->id,
    ]);
}

it('allows an admin to view availability oversight', function (): void {
    $admin = createAdminUserForAvailabilityModule();

    $profile = createCounsellorProfileForAvailabilityOversight([
        'name' => 'Calendar Counsellor',
        'email' => 'calendar.counsellor@example.com',
    ]);

    $rule = CounsellorAvailabilityRule::factory()
        ->for($profile)
        ->create([
            'day_of_week' => CounsellorAvailabilityRule::MONDAY,
            'start_time' => '09:00',
            'end_time' => '17:00',
            'is_active' => true,
        ]);

    CounsellorAvailabilityBreak::factory()
        ->for($rule, 'availabilityRule')
        ->create([
            'title' => 'Lunch',
            'start_time' => '12:00',
            'end_time' => '13:00',
        ]);

    CounsellorBlockedSlot::factory()
        ->for($profile)
        ->create([
            'blocked_date' => now()->addDays(5)->toDateString(),
            'start_time' => '10:00',
            'end_time' => '11:00',
            'is_full_day' => false,
        ]);

    CounsellorLeaveDay::factory()
        ->for($profile)
        ->fullDay()
        ->create([
            'leave_date' => now()->addDays(8)->toDateString(),
        ]);

    $response = $this
        ->actingAs($admin)
        ->get(route('admin.availability.index'));

    $response->assertOk();

    $counsellors = $response->viewData('page')['props']['counsellors']['data'];

    expect($counsellors)
        ->toHaveCount(1)
        ->and($counsellors[0]['display_name'])->toBe('Calendar Counsellor')
        ->and($counsellors[0]['rules_count'])->toBe(1)
        ->and($counsellors[0]['active_rules_count'])->toBe(1)
        ->and($counsellors[0]['blocked_slots_count'])->toBe(1)
        ->and($counsellors[0]['leave_days_count'])->toBe(1)
        ->and($counsellors[0]['rules'][0]['breaks'][0]['title'])->toBe('Lunch');
});

it('filters availability oversight by counsellor search day and rule status', function (): void {
    $admin = createAdminUserForAvailabilityModule();

    $alpha = createCounsellorProfileForAvailabilityOversight([
        'name' => 'Alpha Calendar',
        'email' => 'alpha.calendar@example.com',
    ]);

    $beta = createCounsellorProfileForAvailabilityOversight([
        'name' => 'Beta Calendar',
        'email' => 'beta.calendar@example.com',
    ]);

    CounsellorAvailabilityRule::factory()
        ->for($alpha)
        ->create([
            'day_of_week' => CounsellorAvailabilityRule::MONDAY,
            'is_active' => true,
        ]);

    CounsellorAvailabilityRule::factory()
        ->for($beta)
        ->inactive()
        ->create([
            'day_of_week' => CounsellorAvailabilityRule::TUESDAY,
        ]);

    $response = $this
        ->actingAs($admin)
        ->get(route('admin.availability.index', [
            'search' => 'Alpha',
            'day_of_week' => CounsellorAvailabilityRule::MONDAY,
            'status' => 'active',
        ]));

    $response->assertOk();

    $counsellors = $response->viewData('page')['props']['counsellors']['data'];

    expect($counsellors)
        ->toHaveCount(1)
        ->and($counsellors[0]['display_name'])->toBe('Alpha Calendar')
        ->and($counsellors[0]['rules'][0]['day_of_week'])->toBe(CounsellorAvailabilityRule::MONDAY)
        ->and($counsellors[0]['rules'][0]['is_active'])->toBeTrue();
});

it('prevents an admin without dashboard admin permission from viewing availability oversight', function (): void {
    $restrictedAdmin = createRestrictedAdminForAvailabilityModule();

    $this
        ->actingAs($restrictedAdmin)
        ->get(route('admin.availability.index'))
        ->assertForbidden();
});
