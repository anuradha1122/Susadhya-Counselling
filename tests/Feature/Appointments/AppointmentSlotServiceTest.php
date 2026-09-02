<?php

use App\Models\Appointment;
use App\Models\ClientProfile;
use App\Models\CounsellorAvailabilityBreak;
use App\Models\CounsellorAvailabilityRule;
use App\Models\CounsellorBlockedSlot;
use App\Models\CounsellorLeaveDay;
use App\Models\CounsellorProfile;
use App\Models\User;
use App\Services\Appointments\AppointmentConflictService;
use App\Services\Appointments\AppointmentSlotService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    app(PermissionRegistrar::class)->forgetCachedPermissions();

    $this->seed(RolePermissionSeeder::class);
});

function futureDateForAppointmentSlotServiceModule(int $dayOfWeek): string
{
    $date = now()->startOfDay()->addDay();

    while ((int) $date->dayOfWeek !== $dayOfWeek) {
        $date = $date->addDay();
    }

    return $date->toDateString();
}

function createSlotServiceClientProfile(): ClientProfile
{
    $user = User::factory()->create([
        'name' => 'Slot Service Client',
        'email' => fake()->unique()->safeEmail(),
        'is_active' => true,
    ]);

    $user->assignRole('client');

    return ClientProfile::factory()->create([
        'user_id' => $user->id,
    ]);
}

function createSlotServiceCounsellorProfile(): CounsellorProfile
{
    $user = User::factory()->create([
        'name' => 'Slot Service Counsellor',
        'email' => fake()->unique()->safeEmail(),
        'is_active' => true,
    ]);

    $user->assignRole('counsellor');

    return CounsellorProfile::factory()->create([
        'user_id' => $user->id,
        'status' => 'active',
    ]);
}

function createSlotServiceRule(
    CounsellorProfile $counsellorProfile,
    int $dayOfWeek,
    string $startTime = '09:00',
    string $endTime = '12:00',
    string $mode = CounsellorAvailabilityRule::MODE_BOTH
): CounsellorAvailabilityRule {
    return CounsellorAvailabilityRule::factory()
        ->for($counsellorProfile)
        ->create([
            'day_of_week' => $dayOfWeek,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'mode' => $mode,
            'slot_duration_minutes' => 60,
            'buffer_minutes' => 0,
            'capacity_per_slot' => 1,
            'timezone' => 'Asia/Colombo',
            'is_active' => true,
        ]);
}

function createSlotServiceAppointment(
    ClientProfile $clientProfile,
    CounsellorProfile $counsellorProfile,
    string $date,
    string $startTime,
    string $endTime,
    string $status = Appointment::STATUS_PENDING
): Appointment {
    return Appointment::query()->create([
        'client_profile_id' => $clientProfile->id,
        'counsellor_profile_id' => $counsellorProfile->id,
        'appointment_date' => $date,
        'start_time' => $startTime,
        'end_time' => $endTime,
        'timezone' => 'Asia/Colombo',
        'mode' => Appointment::MODE_ONLINE,
        'status' => $status,
    ]);
}

it('generates available slots from active counsellor availability rules', function (): void {
    $counsellorProfile = createSlotServiceCounsellorProfile();
    $date = futureDateForAppointmentSlotServiceModule(CounsellorAvailabilityRule::MONDAY);

    createSlotServiceRule(
        counsellorProfile: $counsellorProfile,
        dayOfWeek: CounsellorAvailabilityRule::MONDAY,
        startTime: '09:00',
        endTime: '12:00'
    );

    $slots = app(AppointmentSlotService::class)
        ->availableSlotsForDate($counsellorProfile, $date);

    expect($slots->pluck('start_time')->all())->toBe([
        '09:00',
        '10:00',
        '11:00',
    ])->and($slots->pluck('end_time')->all())->toBe([
        '10:00',
        '11:00',
        '12:00',
    ]);
});

it('excludes slots that overlap availability breaks', function (): void {
    $counsellorProfile = createSlotServiceCounsellorProfile();
    $date = futureDateForAppointmentSlotServiceModule(CounsellorAvailabilityRule::MONDAY);

    $rule = createSlotServiceRule(
        counsellorProfile: $counsellorProfile,
        dayOfWeek: CounsellorAvailabilityRule::MONDAY,
        startTime: '09:00',
        endTime: '12:00'
    );

    CounsellorAvailabilityBreak::factory()
        ->for($rule, 'availabilityRule')
        ->create([
            'title' => 'Morning Break',
            'start_time' => '10:00',
            'end_time' => '11:00',
            'is_active' => true,
        ]);

    $slots = app(AppointmentSlotService::class)
        ->availableSlotsForDate($counsellorProfile, $date);

    expect($slots->pluck('start_time')->all())->toBe([
        '09:00',
        '11:00',
    ]);
});

it('excludes slots that overlap counsellor blocked slots', function (): void {
    $counsellorProfile = createSlotServiceCounsellorProfile();
    $date = futureDateForAppointmentSlotServiceModule(CounsellorAvailabilityRule::MONDAY);

    createSlotServiceRule(
        counsellorProfile: $counsellorProfile,
        dayOfWeek: CounsellorAvailabilityRule::MONDAY,
        startTime: '09:00',
        endTime: '12:00'
    );

    CounsellorBlockedSlot::factory()
        ->for($counsellorProfile)
        ->create([
            'blocked_date' => $date,
            'start_time' => '09:30',
            'end_time' => '10:30',
            'is_full_day' => false,
        ]);

    $slots = app(AppointmentSlotService::class)
        ->availableSlotsForDate($counsellorProfile, $date);

    expect($slots->pluck('start_time')->all())->toBe([
        '11:00',
    ]);
});

it('returns no slots when counsellor has full-day blocked slot', function (): void {
    $counsellorProfile = createSlotServiceCounsellorProfile();
    $date = futureDateForAppointmentSlotServiceModule(CounsellorAvailabilityRule::MONDAY);

    createSlotServiceRule(
        counsellorProfile: $counsellorProfile,
        dayOfWeek: CounsellorAvailabilityRule::MONDAY,
        startTime: '09:00',
        endTime: '12:00'
    );

    CounsellorBlockedSlot::factory()
        ->for($counsellorProfile)
        ->fullDay()
        ->create([
            'blocked_date' => $date,
        ]);

    $slots = app(AppointmentSlotService::class)
        ->availableSlotsForDate($counsellorProfile, $date);

    expect($slots)->toBeEmpty();
});

it('excludes slots that overlap counsellor leave days', function (): void {
    $counsellorProfile = createSlotServiceCounsellorProfile();
    $date = futureDateForAppointmentSlotServiceModule(CounsellorAvailabilityRule::MONDAY);

    createSlotServiceRule(
        counsellorProfile: $counsellorProfile,
        dayOfWeek: CounsellorAvailabilityRule::MONDAY,
        startTime: '09:00',
        endTime: '12:00'
    );

    CounsellorLeaveDay::factory()
        ->for($counsellorProfile)
        ->partialDay()
        ->create([
            'leave_date' => $date,
            'start_time' => '10:00',
            'end_time' => '11:00',
            'is_full_day' => false,
        ]);

    $slots = app(AppointmentSlotService::class)
        ->availableSlotsForDate($counsellorProfile, $date);

    expect($slots->pluck('start_time')->all())->toBe([
        '09:00',
        '11:00',
    ]);
});

it('returns no slots when counsellor has full-day leave', function (): void {
    $counsellorProfile = createSlotServiceCounsellorProfile();
    $date = futureDateForAppointmentSlotServiceModule(CounsellorAvailabilityRule::MONDAY);

    createSlotServiceRule(
        counsellorProfile: $counsellorProfile,
        dayOfWeek: CounsellorAvailabilityRule::MONDAY,
        startTime: '09:00',
        endTime: '12:00'
    );

    CounsellorLeaveDay::factory()
        ->for($counsellorProfile)
        ->fullDay()
        ->create([
            'leave_date' => $date,
        ]);

    $slots = app(AppointmentSlotService::class)
        ->availableSlotsForDate($counsellorProfile, $date);

    expect($slots)->toBeEmpty();
});

it('excludes slots already booked by counsellor', function (): void {
    $clientProfile = createSlotServiceClientProfile();
    $counsellorProfile = createSlotServiceCounsellorProfile();
    $date = futureDateForAppointmentSlotServiceModule(CounsellorAvailabilityRule::MONDAY);

    createSlotServiceRule(
        counsellorProfile: $counsellorProfile,
        dayOfWeek: CounsellorAvailabilityRule::MONDAY,
        startTime: '09:00',
        endTime: '12:00'
    );

    createSlotServiceAppointment(
        clientProfile: $clientProfile,
        counsellorProfile: $counsellorProfile,
        date: $date,
        startTime: '10:00',
        endTime: '11:00',
        status: Appointment::STATUS_CONFIRMED
    );

    $slots = app(AppointmentSlotService::class)
        ->availableSlotsForDate($counsellorProfile, $date);

    expect($slots->pluck('start_time')->all())->toBe([
        '09:00',
        '11:00',
    ]);
});

it('excludes slots that conflict with the selected client existing booking', function (): void {
    $clientProfile = createSlotServiceClientProfile();
    $firstCounsellorProfile = createSlotServiceCounsellorProfile();
    $secondCounsellorProfile = createSlotServiceCounsellorProfile();
    $date = futureDateForAppointmentSlotServiceModule(CounsellorAvailabilityRule::MONDAY);

    createSlotServiceRule(
        counsellorProfile: $secondCounsellorProfile,
        dayOfWeek: CounsellorAvailabilityRule::MONDAY,
        startTime: '09:00',
        endTime: '12:00'
    );

    createSlotServiceAppointment(
        clientProfile: $clientProfile,
        counsellorProfile: $firstCounsellorProfile,
        date: $date,
        startTime: '10:00',
        endTime: '11:00',
        status: Appointment::STATUS_PENDING
    );

    $slots = app(AppointmentSlotService::class)
        ->availableSlotsForDate(
            counsellorProfile: $secondCounsellorProfile,
            date: $date,
            clientProfile: $clientProfile
        );

    expect($slots->pluck('start_time')->all())->toBe([
        '09:00',
        '11:00',
    ]);
});

it('detects counsellor and client booking conflicts', function (): void {
    $clientProfile = createSlotServiceClientProfile();
    $counsellorProfile = createSlotServiceCounsellorProfile();
    $date = futureDateForAppointmentSlotServiceModule(CounsellorAvailabilityRule::MONDAY);

    createSlotServiceAppointment(
        clientProfile: $clientProfile,
        counsellorProfile: $counsellorProfile,
        date: $date,
        startTime: '10:00',
        endTime: '11:00',
        status: Appointment::STATUS_CONFIRMED
    );

    $service = app(AppointmentConflictService::class);

    expect($service->counsellorHasConflict(
        counsellorProfile: $counsellorProfile,
        date: $date,
        startTime: '10:30',
        endTime: '11:30'
    ))->toBeTrue()
        ->and($service->clientHasConflict(
            clientProfile: $clientProfile,
            date: $date,
            startTime: '10:30',
            endTime: '11:30'
        ))->toBeTrue()
        ->and($service->hasAnyConflict(
            clientProfile: $clientProfile,
            counsellorProfile: $counsellorProfile,
            date: $date,
            startTime: '10:30',
            endTime: '11:30'
        ))->toBeTrue();
});

it('ignores cancelled appointments when generating available slots', function (): void {
    $clientProfile = createSlotServiceClientProfile();
    $counsellorProfile = createSlotServiceCounsellorProfile();
    $date = futureDateForAppointmentSlotServiceModule(CounsellorAvailabilityRule::MONDAY);

    createSlotServiceRule(
        counsellorProfile: $counsellorProfile,
        dayOfWeek: CounsellorAvailabilityRule::MONDAY,
        startTime: '09:00',
        endTime: '12:00'
    );

    createSlotServiceAppointment(
        clientProfile: $clientProfile,
        counsellorProfile: $counsellorProfile,
        date: $date,
        startTime: '10:00',
        endTime: '11:00',
        status: Appointment::STATUS_CANCELLED
    );

    $slots = app(AppointmentSlotService::class)
        ->availableSlotsForDate($counsellorProfile, $date);

    expect($slots->pluck('start_time')->all())->toBe([
        '09:00',
        '10:00',
        '11:00',
    ]);
});
