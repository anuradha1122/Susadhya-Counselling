<?php

use App\Models\ClientProfile;
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

function m20Client(): User
{
    $user =
        User::factory()->create([
            'is_active' => true,
        ]);

    $user->assignRole(
        'client'
    );

    ClientProfile::factory()->create([
        'user_id' => $user->id,
    ]);

    return $user;
}

it(
    'allows a client to create a support request',
    function (): void {
        $client =
            m20Client();

        $this
            ->actingAs($client)
            ->post(
                route(
                    'client.support.store'
                ),
                [
                    'category' => SupportTicket::CATEGORY_APPOINTMENT,

                    'subject' => 'Appointment help',

                    'description' => 'I need help understanding an appointment status.',
                ]
            )
            ->assertSessionHasNoErrors();

        $ticket =
            SupportTicket::query()
                ->firstOrFail();

        expect(
            $ticket->requester_id
        )->toBe(
            $client->id
        );

        expect(
            $ticket->status
        )->toBe(
            SupportTicket::STATUS_OPEN
        );
    }
);

it(
    'prevents one client from viewing another client support request',
    function (): void {
        $owner =
            m20Client();

        $other =
            m20Client();

        $ticket =
            SupportTicket::factory()
                ->create([
                    'requester_id' => $owner->id,
                ]);

        $this
            ->actingAs($other)
            ->get(
                route(
                    'client.support.show',
                    $ticket
                )
            )
            ->assertNotFound();
    }
);

it(
    'allows the owning client to reply',
    function (): void {
        $client =
            m20Client();

        $ticket =
            SupportTicket::factory()
                ->create([
                    'requester_id' => $client->id,
                ]);

        $this
            ->actingAs($client)
            ->post(
                route(
                    'client.support.replies.store',
                    $ticket
                ),
                [
                    'body' => 'Here is some additional information.',
                ]
            )
            ->assertSessionHasNoErrors();

        expect(
            $ticket
                ->replies()
                ->count()
        )->toBe(1);
    }
);
