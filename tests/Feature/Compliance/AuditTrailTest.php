<?php

use App\Models\AuditEvent;
use App\Models\User;
use App\Services\Compliance\AuditLogger;
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
    'records safe audit metadata while removing sensitive values',
    function (): void {
        $actor = User::factory()->create([
            'is_active' => true,
        ]);

        $event = app(
            AuditLogger::class
        )->record(
            category: 'compliance',
            event: 'test.audit',
            action: 'read',
            actor: $actor,
            metadata: [
                'safe_value' => 'allowed',
                'count' => 12,
                'password' => 'must-not-be-stored',
                'api_token' => 'must-not-be-stored',
                'clinical_note' => 'must-not-be-stored',
                'gateway_payload' => [
                    'secret' => 'must-not-be-stored',
                ],
            ],
        );

        expect($event->actor_id)
            ->toBe($actor->id);

        expect($event->metadata)
            ->toHaveKey(
                'safe_value',
                'allowed'
            )
            ->toHaveKey(
                'count',
                12
            )
            ->not
            ->toHaveKey('password')
            ->not
            ->toHaveKey('api_token')
            ->not
            ->toHaveKey('clinical_note')
            ->not
            ->toHaveKey('gateway_payload');
    }
);

it(
    'does not allow audit events to be updated',
    function (): void {
        $event =
            AuditEvent::factory()->create();

        expect(
            fn () => $event->update([
                'result' => 'changed',
            ])
        )->toThrow(
            LogicException::class,
            'Audit events are immutable.'
        );
    }
);

it(
    'does not allow audit events to be deleted through eloquent',
    function (): void {
        $event =
            AuditEvent::factory()->create();

        expect(
            fn () => $event->delete()
        )->toThrow(
            LogicException::class,
            'Audit events cannot be deleted through Eloquent.'
        );
    }
);

it(
    'allows privacy officers to view audit logs',
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
                    'compliance.audit-events.index'
                )
            )
            ->assertOk();
    }
);

it(
    'does not automatically allow ordinary admins to view compliance audit logs',
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
                    'compliance.audit-events.index'
                )
            )
            ->assertForbidden();
    }
);
