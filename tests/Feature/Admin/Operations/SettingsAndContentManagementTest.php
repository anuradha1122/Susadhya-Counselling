<?php

use App\Models\ContentSnippet;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    app(PermissionRegistrar::class)
        ->forgetCachedPermissions();
});

function settingsAdmin(): User
{
    $permissions = collect([
        'settings.view',
        'settings.update',
        'admin.content.manage',
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

it('updates predefined system settings', function (): void {
    $admin = settingsAdmin();

    SystemSetting::factory()->create([
        'key' => 'operations.support_email',
        'label' => 'Support email',
        'type' => SystemSetting::TYPE_EMAIL,
        'value' => 'old@example.com',
    ]);

    $this
        ->actingAs($admin)
        ->patch(
            route('admin.settings.update'),
            [
                'settings' => [
                    'operations.support_email' => 'support@example.com',
                ],
            ]
        )
        ->assertRedirect();

    $this->assertDatabaseHas(
        'system_settings',
        [
            'key' => 'operations.support_email',
            'value' => 'support@example.com',
            'updated_by' => $admin->id,
        ]
    );
});

it('rejects unknown system setting keys', function (): void {
    $admin = settingsAdmin();

    $this
        ->actingAs($admin)
        ->patch(
            route('admin.settings.update'),
            [
                'settings' => [
                    'secret.payment.private_key' => 'definitely-nope',
                ],
            ]
        )
        ->assertSessionHasErrors(
            'settings.secret.payment.private_key'
        );
});

it('creates and updates content snippets', function (): void {
    $admin = settingsAdmin();

    $this
        ->actingAs($admin)
        ->post(
            route('admin.content-snippets.store'),
            [
                'key' => 'operations.test_notice',
                'title' => 'Test notice',
                'body' => 'Operational information only.',
                'placement' => 'admin_operations',
                'status' => ContentSnippet::STATUS_DRAFT,
            ]
        )
        ->assertRedirect();

    $snippet = ContentSnippet::query()
        ->where('key', 'operations.test_notice')
        ->firstOrFail();

    $this
        ->actingAs($admin)
        ->patch(
            route(
                'admin.content-snippets.update',
                $snippet
            ),
            [
                'title' => 'Updated notice',
                'body' => 'Updated operational information.',
                'placement' => 'admin_operations',
                'status' => ContentSnippet::STATUS_PUBLISHED,
            ]
        )
        ->assertRedirect();

    $snippet->refresh();

    expect($snippet->status)
        ->toBe(ContentSnippet::STATUS_PUBLISHED);

    expect($snippet->published_at)
        ->not->toBeNull();
});
