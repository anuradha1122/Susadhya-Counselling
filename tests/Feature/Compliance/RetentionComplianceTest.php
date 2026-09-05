<?php

use App\Models\RetentionPolicy;
use App\Models\User;
use App\Services\Compliance\RetentionService;
use Database\Seeders\CompliancePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
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

    config()->set(
        'compliance.retention.automatic_categories',
        [
            RetentionPolicy::CATEGORY_NOTIFICATIONS,
        ]
    );
});

it(
    'can perform a dry run without modifying records',
    function (): void {
        $policy =
            RetentionPolicy::factory()
                ->create([
                    'category' => RetentionPolicy::CATEGORY_OPERATIONAL,

                    'name' => 'Operational test retention',

                    'retention_days' => 365,

                    'action' => RetentionPolicy::ACTION_REVIEW,

                    'enabled' => true,

                    'automatic_execution' => false,

                    'legal_basis' => 'Test retention basis.',
                ]);

        $run =
            app(
                RetentionService::class
            )->run(
                policy: $policy,
                actor: null,
                execute: false,
            );

        expect(
            $run->mode
        )->toBe('dry_run');

        expect(
            $run->status
        )->toBe('completed');

        expect(
            $run->processed_count
        )->toBe(0);

        expect(
            $run->summary['dry_run']
        )->toBeTrue();
    }
);

it(
    'blocks automatic execution for protected clinical records',
    function (): void {
        $policy =
            RetentionPolicy::factory()
                ->create([
                    'category' => RetentionPolicy::CATEGORY_CLINICAL,

                    'name' => 'Clinical retention',

                    'retention_days' => 365,

                    'action' => RetentionPolicy::ACTION_DELETE,

                    'enabled' => true,

                    'automatic_execution' => true,

                    'legal_basis' => 'Test only.',
                ]);

        $run =
            app(
                RetentionService::class
            )->run(
                policy: $policy,
                actor: null,
                execute: true,
            );

        expect(
            $run->status
        )->toBe('blocked');

        expect(
            $run->processed_count
        )->toBe(0);

        $this->assertDatabaseHas(
            'audit_events',
            [
                'event' => 'retention.execution_blocked',

                'result' => 'blocked',
            ]
        );
    }
);

it(
    'rejects automatic retention configuration for protected categories',
    function (): void {
        $officer =
            User::factory()->create([
                'is_active' => true,
            ]);

        $officer->assignRole(
            'privacy_officer'
        );

        $policy =
            RetentionPolicy::factory()
                ->create([
                    'category' => RetentionPolicy::CATEGORY_FINANCIAL,

                    'name' => 'Financial records',

                    'enabled' => false,

                    'automatic_execution' => false,
                ]);

        $this
            ->actingAs($officer)
            ->patch(
                route(
                    'compliance.retention.update',
                    $policy
                ),
                [
                    'retention_days' => 3650,

                    'action' => RetentionPolicy::ACTION_DELETE,

                    'enabled' => true,

                    'automatic_execution' => true,

                    'legal_basis' => 'Test legal basis.',
                ]
            )
            ->assertSessionHasErrors(
                'automatic_execution'
            );

        expect(
            $policy
                ->refresh()
                ->automatic_execution
        )->toBeFalse();
    }
);

it(
    'does not allow disabled policies to run',
    function (): void {
        $policy =
            RetentionPolicy::factory()
                ->create([
                    'category' => RetentionPolicy::CATEGORY_DOCUMENTS,

                    'name' => 'Disabled document policy',

                    'retention_days' => 365,

                    'enabled' => false,
                ]);

        expect(
            fn () => app(
                RetentionService::class
            )->run(
                policy: $policy,
                actor: null,
                execute: false,
            )
        )->toThrow(
            ValidationException::class
        );
    }
);
