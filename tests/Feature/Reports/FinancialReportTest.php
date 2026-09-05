<?php

use App\Models\Appointment;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    app(PermissionRegistrar::class)
        ->forgetCachedPermissions();
});

function financeReportUser(): User
{
    $permissions = collect([
        'reports.finance.view',
        'reports.finance.export',
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

it('reconciles gross revenue to paid payment source records', function (): void {
    $finance = financeReportUser();

    $appointmentA =
        Appointment::factory()
            ->withCounsellingService()
            ->create();

    $appointmentB =
        Appointment::factory()
            ->withCounsellingService()
            ->create();

    Payment::factory()->create([
        'appointment_id' => $appointmentA->id,
        'amount' => 5000,
        'currency' => 'LKR',
        'status' => 'paid',
        'reconciliation_status' => 'matched',
        'paid_at' => now(),
    ]);

    Payment::factory()->create([
        'appointment_id' => $appointmentB->id,
        'amount' => 7000,
        'currency' => 'LKR',
        'status' => 'paid',
        'reconciliation_status' => 'matched',
        'paid_at' => now(),
    ]);

    $response = $this
        ->actingAs($finance)
        ->get(
            route(
                'finance.reports.index',
                [
                    'from' => now()->toDateString(),
                    'to' => now()->toDateString(),
                ]
            )
        );

    $response
        ->assertOk()
        ->assertInertia(
            fn ($page) => $page
                ->component(
                    'Finance/Reports/Index'
                )
                ->where(
                    'summary.payment_count',
                    2
                )
                ->where(
                    'summary.currency_totals.0.currency',
                    'LKR'
                )
                ->where(
                    'summary.currency_totals.0.gross_amount',
                    12000
                )
        );
});

it('prevents ordinary admin style user without finance report permission', function (): void {
    $user = User::factory()->create([
        'is_active' => true,
    ]);

    $this
        ->actingAs($user)
        ->get(
            route(
                'finance.reports.index'
            )
        )
        ->assertForbidden();
});
