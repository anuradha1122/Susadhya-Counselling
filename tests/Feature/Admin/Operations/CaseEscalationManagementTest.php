<?php

use App\Models\CaseEscalation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    app(PermissionRegistrar::class)
        ->forgetCachedPermissions();
});

function caseEscalationAdmin(): User
{
    $permission = Permission::firstOrCreate([
        'name' => 'admin.case-escalations.manage',
        'guard_name' => 'web',
    ]);

    $user = User::factory()->create([
        'is_active' => true,
    ]);

    $user->givePermissionTo($permission);

    return $user;
}

it('creates privacy safe case escalation metadata', function (): void {
    $admin = caseEscalationAdmin();

    $this
        ->actingAs($admin)
        ->post(
            route('admin.case-escalations.store'),
            [
                'case_reference' => 'CASE-TEST-001',
                'reason_code' => CaseEscalation::REASON_COORDINATION,
                'priority' => CaseEscalation::PRIORITY_HIGH,
                'admin_note' => 'Supervisor routing required.',
            ]
        )
        ->assertRedirect();

    $this->assertDatabaseHas(
        'case_escalations',
        [
            'case_reference' => 'CASE-TEST-001',
            'reason_code' => CaseEscalation::REASON_COORDINATION,
            'created_by' => $admin->id,
        ]
    );
});

it('resolves case escalation metadata', function (): void {
    $admin = caseEscalationAdmin();

    $escalation = CaseEscalation::factory()->create([
        'created_by' => $admin->id,
    ]);

    $this
        ->actingAs($admin)
        ->patch(
            route(
                'admin.case-escalations.update',
                $escalation
            ),
            [
                'status' => CaseEscalation::STATUS_RESOLVED,
                'priority' => CaseEscalation::PRIORITY_MEDIUM,
                'assigned_to' => $admin->id,
                'admin_note' => 'Administrative routing only.',
                'resolution_notes' => 'Routing completed.',
            ]
        )
        ->assertRedirect();

    $escalation->refresh();

    expect($escalation->resolved_by)
        ->toBe($admin->id);

    expect($escalation->resolved_at)
        ->not->toBeNull();
});
