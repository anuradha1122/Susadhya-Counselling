<?php

use App\Models\Appointment;
use App\Models\ClientProfile;
use App\Models\CounsellorProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    app(PermissionRegistrar::class)
        ->forgetCachedPermissions();
});

function operationalReportAdmin(): User
{
    $permissions = collect([
        'reports.operational.view',
        'reports.operational.export',
    ])->map(
        fn (string $name) => Permission::firstOrCreate([
            'name' => $name,
            'guard_name' => 'web',
        ])
    );

    $user = User::factory()->create([
        'is_active' => true,
    ]);

    $user->givePermissionTo(
        $permissions
    );

    return $user;
}

function operationalReportAppointment(
    string $status
): Appointment {
    $client = ClientProfile::factory()->create();

    $counsellor =
        CounsellorProfile::factory()->create();

    return Appointment::factory()->create([
        'client_profile_id' => $client->id,
        'counsellor_profile_id' => $counsellor->id,
        'appointment_date' => now()->toDateString(),
        'status' => $status,
    ]);
}

it('shows operational report totals from appointment source records', function (): void {
    $admin = operationalReportAdmin();

    operationalReportAppointment(
        Appointment::STATUS_COMPLETED
    );

    operationalReportAppointment(
        Appointment::STATUS_COMPLETED
    );

    operationalReportAppointment(
        Appointment::STATUS_NO_SHOW
    );

    operationalReportAppointment(
        Appointment::STATUS_CANCELLED
    );

    $this
        ->actingAs($admin)
        ->get(
            route(
                'admin.reports.index',
                [
                    'from' => now()->toDateString(),
                    'to' => now()->toDateString(),
                ]
            )
        )
        ->assertOk()
        ->assertInertia(
            fn ($page) => $page
                ->component(
                    'Admin/Reports/Index'
                )
                ->where(
                    'summary.total',
                    4
                )
                ->where(
                    'summary.completed',
                    2
                )
                ->where(
                    'summary.no_show',
                    1
                )
                ->where(
                    'summary.cancelled',
                    1
                )
                ->where(
                    'summary.utilisation_rate',
                    66.67
                )
        );
});

it('prevents users without operational report permission from viewing reports', function (): void {
    $user = User::factory()->create([
        'is_active' => true,
    ]);

    $this
        ->actingAs($user)
        ->get(
            route(
                'admin.reports.index'
            )
        )
        ->assertForbidden();
});
