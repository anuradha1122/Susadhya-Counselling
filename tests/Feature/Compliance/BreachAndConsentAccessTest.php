<?php

use App\Models\DataBreach;
use App\Models\User;
use Database\Seeders\CompliancePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use LogicException;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->withoutVite();

    $this->seed(
        CompliancePermissionSeeder::class
    );

    app(
        PermissionRegistrar::class
    )->forgetCachedPermissions();
});

it(
    'allows privacy officers to register and update breach records',
    function (): void {
        $officer =
            User::factory()->create([
                'is_active' => true,
            ]);

        $officer->assignRole(
            'privacy_officer'
        );

        $response =
            $this
                ->actingAs($officer)
                ->post(
                    route(
                        'compliance.breaches.store'
                    ),
                    [
                        'title' => 'Test privacy incident',

                        'severity' => DataBreach::SEVERITY_HIGH,

                        'detected_at' => now()
                            ->format(
                                'Y-m-d H:i:s'
                            ),

                        'affected_subject_count' => 2,

                        'data_categories' => [
                            'contact_data',
                        ],

                        'systems_affected' => [
                            'web_application',
                        ],

                        'summary' => 'Test breach record for automated compliance testing.',
                    ]
                );

        $response
            ->assertSessionHasNoErrors();

        $breach =
            DataBreach::query()
                ->firstOrFail();

        expect(
            $breach->status
        )->toBe(
            DataBreach::STATUS_OPEN
        );

        expect(
            $breach->reported_by
        )->toBe($officer->id);

        expect(
            $breach->summary
        )->toBe(
            'Test breach record for automated compliance testing.'
        );

        $this
            ->actingAs($officer)
            ->patch(
                route(
                    'compliance.breaches.update',
                    $breach
                ),
                [
                    'title' => $breach->title,

                    'severity' => DataBreach::SEVERITY_HIGH,

                    'status' => DataBreach::STATUS_CONTAINED,

                    'assigned_to' => $officer->id,

                    'detected_at' => $breach
                        ->detected_at
                        ->format(
                            'Y-m-d H:i:s'
                        ),

                    'affected_subject_count' => 2,

                    'data_categories' => [
                        'contact_data',
                    ],

                    'systems_affected' => [
                        'web_application',
                    ],

                    'summary' => 'Test breach record for automated compliance testing.',

                    'containment_actions' => 'Affected access disabled.',

                    'notification_decision' => 'Notification assessment recorded.',

                    'authority_reference' => null,
                ]
            )
            ->assertSessionHasNoErrors();

        $breach->refresh();

        expect(
            $breach->status
        )->toBe(
            DataBreach::STATUS_CONTAINED
        );

        expect(
            $breach->contained_at
        )->not->toBeNull();

        $this->assertDatabaseHas(
            'audit_events',
            [
                'event' => 'breach.updated',

                'actor_id' => $officer->id,
            ]
        );
    }
);

it(
    'does not allow breach records to be deleted through eloquent',
    function (): void {
        $breach =
            DataBreach::factory()
                ->create();

        expect(
            fn () => $breach->delete()
        )->toThrow(
            LogicException::class,
            'Breach records are compliance evidence and cannot be deleted.'
        );
    }
);

it(
    'allows privacy officers to view existing consent history',
    function (): void {
        $officer =
            User::factory()->create([
                'is_active' => true,
            ]);

        $officer->assignRole(
            'privacy_officer'
        );

        $this
            ->actingAs($officer)
            ->get(
                route(
                    'compliance.consents.index'
                )
            )
            ->assertOk();
    }
);

it(
    'does not automatically grant ordinary admins access to consent history',
    function (): void {
        Role::firstOrCreate([
            'name' => 'admin',
            'guard_name' => 'web',
        ]);

        $admin =
            User::factory()->create([
                'is_active' => true,
            ]);

        $admin->assignRole('admin');

        $this
            ->actingAs($admin)
            ->get(
                route(
                    'compliance.consents.index'
                )
            )
            ->assertForbidden();
    }
);

it(
    'redirects privacy officers to the compliance dashboard',
    function (): void {
        $officer =
            User::factory()->create([
                'is_active' => true,
            ]);

        $officer->assignRole(
            'privacy_officer'
        );

        $this
            ->actingAs($officer)
            ->get('/dashboard')
            ->assertRedirect(
                route(
                    'compliance.dashboard'
                )
            );
    }
);
