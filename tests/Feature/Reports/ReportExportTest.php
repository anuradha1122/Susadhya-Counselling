<?php

use App\Models\Appointment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    app(PermissionRegistrar::class)
        ->forgetCachedPermissions();
});

function reportExporter(): User
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

it('exports operational report as csv', function (): void {
    $admin = reportExporter();

    Appointment::factory()
        ->withCounsellingService()
        ->create([
            'appointment_date' => now()->toDateString(),
            'status' => Appointment::STATUS_COMPLETED,
        ]);

    $response = $this
        ->actingAs($admin)
        ->get(
            route(
                'admin.reports.csv',
                [
                    'from' => now()->toDateString(),
                    'to' => now()->toDateString(),
                ]
            )
        );

    $response->assertOk();

    expect(
        $response->headers->get(
            'content-type'
        )
    )->toContain('text/csv');
});

it('exports operational report as pdf', function (): void {
    $admin = reportExporter();

    Appointment::factory()
        ->withCounsellingService()
        ->create([
            'appointment_date' => now()->toDateString(),
            'status' => Appointment::STATUS_COMPLETED,
        ]);

    $response = $this
        ->actingAs($admin)
        ->get(
            route(
                'admin.reports.pdf',
                [
                    'from' => now()->toDateString(),
                    'to' => now()->toDateString(),
                ]
            )
        );

    $response->assertOk();

    expect(
        $response->headers->get(
            'content-type'
        )
    )->toBe('application/pdf');
});
