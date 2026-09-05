<?php

use App\Models\OperationalException;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    app(PermissionRegistrar::class)
        ->forgetCachedPermissions();
});

function operationsManager(): User
{
    $permissions = collect([
        'admin.operations.view',
        'admin.operations.manage',
    ])->map(
        fn (string $name) => Permission::firstOrCreate([
            'name' => $name,
            'guard_name' => 'web',
        ])
    );

    $user = User::factory()->create([
        'is_active' => true,
    ]);

    $user->givePermissionTo($permissions);

    return $user;
}

it('creates an operational exception', function (): void {
    $admin = operationsManager();

    $this
        ->actingAs($admin)
        ->post(
            route('admin.operations.exceptions.store'),
            [
                'type' => OperationalException::TYPE_BOOKING,
                'priority' => OperationalException::PRIORITY_HIGH,
                'source_type' => 'appointment',
                'source_reference' => 'appointment-reference',
                'title' => 'Meeting details missing',
                'description' => 'Operational scheduling correction required.',
            ]
        )
        ->assertRedirect();

    $this->assertDatabaseHas(
        'operational_exceptions',
        [
            'title' => 'Meeting details missing',
            'status' => OperationalException::STATUS_OPEN,
            'created_by' => $admin->id,
        ]
    );
});

it('resolves an operational exception with audit metadata', function (): void {
    $admin = operationsManager();

    $exception = OperationalException::factory()->create([
        'created_by' => $admin->id,
    ]);

    $this
        ->actingAs($admin)
        ->patch(
            route(
                'admin.operations.exceptions.update',
                $exception
            ),
            [
                'status' => OperationalException::STATUS_RESOLVED,
                'priority' => OperationalException::PRIORITY_MEDIUM,
                'assigned_to' => $admin->id,
                'due_at' => now()->addDay()->toDateTimeString(),
                'resolution_notes' => 'Corrected through normal administration.',
            ]
        )
        ->assertRedirect();

    $exception->refresh();

    expect($exception->status)
        ->toBe(OperationalException::STATUS_RESOLVED);

    expect($exception->resolved_by)
        ->toBe($admin->id);

    expect($exception->resolved_at)
        ->not->toBeNull();
});
