<?php

use App\Models\SupportTicket;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\SupportPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->withoutVite();

    $this->seed(
        RolePermissionSeeder::class
    );

    $this->seed(
        SupportPermissionSeeder::class
    );

    app(
        PermissionRegistrar::class
    )->forgetCachedPermissions();
});

function m20Admin(): User
{
    $admin =
        User::factory()->create([
            'is_active' => true,
        ]);

    $admin->assignRole(
        'admin'
    );

    return $admin;
}

it(
    'allows an admin to assign and manage a support request',
    function (): void {
        $admin =
            m20Admin();

        $owner =
            m20Admin();

        $ticket =
            SupportTicket::factory()
                ->guest()
                ->create();

        $this
            ->actingAs($admin)
            ->patch(
                route(
                    'admin.support.update',
                    $ticket
                ),
                [
                    'status' => SupportTicket::STATUS_IN_PROGRESS,

                    'priority' => SupportTicket::PRIORITY_HIGH,

                    'owner_id' => $owner->id,

                    'resolution_note' => null,
                ]
            )
            ->assertSessionHasNoErrors();

        $ticket->refresh();

        expect(
            $ticket->status
        )->toBe(
            SupportTicket::STATUS_IN_PROGRESS
        );

        expect(
            $ticket->priority
        )->toBe(
            SupportTicket::PRIORITY_HIGH
        );

        expect(
            $ticket->owner_id
        )->toBe(
            $owner->id
        );

        expect(
            $ticket
                ->histories()
                ->count()
        )->toBeGreaterThan(0);
    }
);

it(
    'requires a resolution note when resolving a request',
    function (): void {
        $admin =
            m20Admin();

        $ticket =
            SupportTicket::factory()
                ->create();

        $this
            ->actingAs($admin)
            ->patch(
                route(
                    'admin.support.update',
                    $ticket
                ),
                [
                    'status' => SupportTicket::STATUS_RESOLVED,

                    'priority' => SupportTicket::PRIORITY_NORMAL,

                    'owner_id' => $admin->id,

                    'resolution_note' => '',
                ]
            )
            ->assertSessionHasErrors(
                'resolution_note'
            );
    }
);

it(
    'keeps internal notes hidden from the client view',
    function (): void {
        $admin =
            m20Admin();

        $client =
            User::factory()->create([
                'is_active' => true,
            ]);

        $client->assignRole(
            'client'
        );

        $ticket =
            SupportTicket::factory()
                ->create([
                    'requester_id' => $client->id,
                ]);

        $this
            ->actingAs($admin)
            ->post(
                route(
                    'admin.support.replies.store',
                    $ticket
                ),
                [
                    'body' => 'Internal administrative note.',

                    'is_internal' => true,
                ]
            )
            ->assertSessionHasNoErrors();

        $this
            ->actingAs($client)
            ->get(
                route(
                    'client.support.show',
                    $ticket
                )
            )
            ->assertOk()
            ->assertInertia(
                fn ($page) => $page
                    ->where(
                        'ticket.replies',
                        []
                    )
            );
    }
);
