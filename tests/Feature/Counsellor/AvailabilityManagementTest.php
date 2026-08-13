<?php

use App\Models\CounsellorAvailabilityBreak;
use App\Models\CounsellorAvailabilityRule;
use App\Models\CounsellorBlockedSlot;
use App\Models\CounsellorLeaveDay;
use App\Models\CounsellorProfile;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    app(PermissionRegistrar::class)->forgetCachedPermissions();

    $this->seed(RolePermissionSeeder::class);
});

function createCounsellorUserForAvailabilityModule(array $userOverrides = []): User
{
    $user = User::factory()->create(array_merge([
        'name' => 'Availability Counsellor',
        'email' => fake()->unique()->safeEmail(),
        'is_active' => true,
    ], $userOverrides));

    $user->assignRole('counsellor');

    CounsellorProfile::factory()->create([
        'user_id' => $user->id,
    ]);

    return $user->refresh();
}

it('allows a counsellor to view their availability page', function (): void {
    $counsellor = createCounsellorUserForAvailabilityModule();

    $this
        ->actingAs($counsellor)
        ->get(route('counsellor.availability.index'))
        ->assertOk();
});

it('allows a counsellor to create a recurring availability rule', function (): void {
    $counsellor = createCounsellorUserForAvailabilityModule();

    $response = $this
        ->actingAs($counsellor)
        ->post(route('counsellor.availability.rules.store'), [
            'day_of_week' => CounsellorAvailabilityRule::MONDAY,
            'start_time' => '09:00',
            'end_time' => '17:00',
            'mode' => CounsellorAvailabilityRule::MODE_BOTH,
            'slot_duration_minutes' => 60,
            'buffer_minutes' => 15,
            'capacity_per_slot' => 1,
            'timezone' => 'Asia/Colombo',
            'effective_from' => null,
            'effective_until' => null,
            'is_active' => '1',
            'notes' => 'Available on Mondays.',
        ]);

    $response->assertRedirect(route('counsellor.availability.index'));

    $this->assertDatabaseHas('counsellor_availability_rules', [
        'counsellor_profile_id' => $counsellor->counsellorProfile->id,
        'day_of_week' => CounsellorAvailabilityRule::MONDAY,
        'start_time' => '09:00',
        'end_time' => '17:00',
        'mode' => CounsellorAvailabilityRule::MODE_BOTH,
        'slot_duration_minutes' => 60,
        'buffer_minutes' => 15,
        'capacity_per_slot' => 1,
        'timezone' => 'Asia/Colombo',
        'is_active' => 1,
    ]);
});

it('rejects overlapping recurring availability rules for the same counsellor and day', function (): void {
    $counsellor = createCounsellorUserForAvailabilityModule();

    CounsellorAvailabilityRule::factory()
        ->for($counsellor->counsellorProfile)
        ->create([
            'day_of_week' => CounsellorAvailabilityRule::MONDAY,
            'start_time' => '09:00',
            'end_time' => '17:00',
            'is_active' => true,
        ]);

    $response = $this
        ->actingAs($counsellor)
        ->from(route('counsellor.availability.index'))
        ->post(route('counsellor.availability.rules.store'), [
            'day_of_week' => CounsellorAvailabilityRule::MONDAY,
            'start_time' => '10:00',
            'end_time' => '12:00',
            'mode' => CounsellorAvailabilityRule::MODE_ONLINE,
            'slot_duration_minutes' => 60,
            'buffer_minutes' => 0,
            'capacity_per_slot' => 1,
            'timezone' => 'Asia/Colombo',
            'is_active' => '1',
        ]);

    $response
        ->assertRedirect(route('counsellor.availability.index'))
        ->assertSessionHasErrors('start_time');

    expect(
        CounsellorAvailabilityRule::query()
            ->where('counsellor_profile_id', $counsellor->counsellorProfile->id)
            ->count()
    )->toBe(1);
});

it('allows non-overlapping recurring availability rules for the same counsellor and day', function (): void {
    $counsellor = createCounsellorUserForAvailabilityModule();

    CounsellorAvailabilityRule::factory()
        ->for($counsellor->counsellorProfile)
        ->create([
            'day_of_week' => CounsellorAvailabilityRule::MONDAY,
            'start_time' => '09:00',
            'end_time' => '12:00',
            'is_active' => true,
        ]);

    $response = $this
        ->actingAs($counsellor)
        ->post(route('counsellor.availability.rules.store'), [
            'day_of_week' => CounsellorAvailabilityRule::MONDAY,
            'start_time' => '13:00',
            'end_time' => '17:00',
            'mode' => CounsellorAvailabilityRule::MODE_IN_PERSON,
            'slot_duration_minutes' => 60,
            'buffer_minutes' => 0,
            'capacity_per_slot' => 1,
            'timezone' => 'Asia/Colombo',
            'is_active' => '1',
        ]);

    $response->assertRedirect(route('counsellor.availability.index'));

    expect(
        CounsellorAvailabilityRule::query()
            ->where('counsellor_profile_id', $counsellor->counsellorProfile->id)
            ->count()
    )->toBe(2);
});

it('allows a counsellor to create a break inside their availability rule', function (): void {
    $counsellor = createCounsellorUserForAvailabilityModule();

    $rule = CounsellorAvailabilityRule::factory()
        ->for($counsellor->counsellorProfile)
        ->create([
            'day_of_week' => CounsellorAvailabilityRule::TUESDAY,
            'start_time' => '09:00',
            'end_time' => '17:00',
        ]);

    $response = $this
        ->actingAs($counsellor)
        ->post(route('counsellor.availability.breaks.store', $rule), [
            'title' => 'Lunch',
            'start_time' => '12:00',
            'end_time' => '13:00',
            'is_active' => '1',
        ]);

    $response->assertRedirect(route('counsellor.availability.index'));

    $this->assertDatabaseHas('counsellor_availability_breaks', [
        'counsellor_availability_rule_id' => $rule->id,
        'title' => 'Lunch',
        'start_time' => '12:00',
        'end_time' => '13:00',
        'is_active' => 1,
    ]);
});

it('rejects a break outside the availability rule time range', function (): void {
    $counsellor = createCounsellorUserForAvailabilityModule();

    $rule = CounsellorAvailabilityRule::factory()
        ->for($counsellor->counsellorProfile)
        ->create([
            'start_time' => '09:00',
            'end_time' => '17:00',
        ]);

    $response = $this
        ->actingAs($counsellor)
        ->from(route('counsellor.availability.index'))
        ->post(route('counsellor.availability.breaks.store', $rule), [
            'title' => 'Too Early',
            'start_time' => '08:00',
            'end_time' => '09:30',
            'is_active' => '1',
        ]);

    $response
        ->assertRedirect(route('counsellor.availability.index'))
        ->assertSessionHasErrors('start_time');

    $this->assertDatabaseMissing('counsellor_availability_breaks', [
        'title' => 'Too Early',
    ]);
});

it('rejects overlapping breaks inside the same availability rule', function (): void {
    $counsellor = createCounsellorUserForAvailabilityModule();

    $rule = CounsellorAvailabilityRule::factory()
        ->for($counsellor->counsellorProfile)
        ->create([
            'start_time' => '09:00',
            'end_time' => '17:00',
        ]);

    CounsellorAvailabilityBreak::factory()
        ->for($rule, 'availabilityRule')
        ->create([
            'title' => 'Lunch',
            'start_time' => '12:00',
            'end_time' => '13:00',
            'is_active' => true,
        ]);

    $response = $this
        ->actingAs($counsellor)
        ->from(route('counsellor.availability.index'))
        ->post(route('counsellor.availability.breaks.store', $rule), [
            'title' => 'Overlap',
            'start_time' => '12:30',
            'end_time' => '13:30',
            'is_active' => '1',
        ]);

    $response
        ->assertRedirect(route('counsellor.availability.index'))
        ->assertSessionHasErrors('start_time');

    expect($rule->breaks()->count())->toBe(1);
});

it('allows a counsellor to create blocked slots and rejects overlapping blocked slots', function (): void {
    $counsellor = createCounsellorUserForAvailabilityModule();

    $date = now()->addDays(10)->toDateString();

    $firstResponse = $this
        ->actingAs($counsellor)
        ->post(route('counsellor.availability.blocked-slots.store'), [
            'blocked_date' => $date,
            'is_full_day' => '0',
            'start_time' => '10:00',
            'end_time' => '11:00',
            'reason' => 'Meeting',
            'notes' => 'External meeting.',
        ]);

    $firstResponse->assertRedirect(route('counsellor.availability.index'));

    $secondResponse = $this
        ->actingAs($counsellor)
        ->from(route('counsellor.availability.index'))
        ->post(route('counsellor.availability.blocked-slots.store'), [
            'blocked_date' => $date,
            'is_full_day' => '0',
            'start_time' => '10:30',
            'end_time' => '11:30',
            'reason' => 'Overlap',
        ]);

    $secondResponse
        ->assertRedirect(route('counsellor.availability.index'))
        ->assertSessionHasErrors('blocked_date');

    expect(
        CounsellorBlockedSlot::query()
            ->where('counsellor_profile_id', $counsellor->counsellorProfile->id)
            ->whereDate('blocked_date', $date)
            ->count()
    )->toBe(1);
});

it('allows a counsellor to create leave days and rejects overlapping leave days', function (): void {
    $counsellor = createCounsellorUserForAvailabilityModule();

    $date = now()->addDays(20)->toDateString();

    $firstResponse = $this
        ->actingAs($counsellor)
        ->post(route('counsellor.availability.leave-days.store'), [
            'leave_date' => $date,
            'is_full_day' => '0',
            'start_time' => '14:00',
            'end_time' => '16:00',
            'reason' => 'Training',
        ]);

    $firstResponse->assertRedirect(route('counsellor.availability.index'));

    $secondResponse = $this
        ->actingAs($counsellor)
        ->from(route('counsellor.availability.index'))
        ->post(route('counsellor.availability.leave-days.store'), [
            'leave_date' => $date,
            'is_full_day' => '0',
            'start_time' => '15:00',
            'end_time' => '17:00',
            'reason' => 'Overlap',
        ]);

    $secondResponse
        ->assertRedirect(route('counsellor.availability.index'))
        ->assertSessionHasErrors('leave_date');

    expect(
        CounsellorLeaveDay::query()
            ->where('counsellor_profile_id', $counsellor->counsellorProfile->id)
            ->whereDate('leave_date', $date)
            ->count()
    )->toBe(1);
});

it('prevents a counsellor from deleting another counsellors availability rule', function (): void {
    $owner = createCounsellorUserForAvailabilityModule([
        'email' => 'owner.availability@example.com',
    ]);

    $other = createCounsellorUserForAvailabilityModule([
        'email' => 'other.availability@example.com',
    ]);

    $rule = CounsellorAvailabilityRule::factory()
        ->for($owner->counsellorProfile)
        ->create();

    $this
        ->actingAs($other)
        ->delete(route('counsellor.availability.rules.destroy', $rule))
        ->assertNotFound();

    $this->assertDatabaseHas('counsellor_availability_rules', [
        'id' => $rule->id,
    ]);
});
