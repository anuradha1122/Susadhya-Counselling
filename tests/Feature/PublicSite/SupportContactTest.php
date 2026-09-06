<?php

use App\Models\SupportTicket;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->withoutVite();
});

it(
    'allows a public visitor to submit a support request',
    function (): void {
        $response =
            $this->post(
                route(
                    'public.contact.store'
                ),
                [
                    'name' => 'Public Visitor',

                    'email' => 'visitor@example.test',

                    'category' => SupportTicket::CATEGORY_TECHNICAL,

                    'subject' => 'Cannot access the login page',

                    'message' => 'I am having trouble accessing the login page from my browser.',

                    'privacy_acknowledged' => true,

                    'website' => '',
                ]
            );

        $response
            ->assertSessionHasNoErrors();

        $ticket =
            SupportTicket::query()
                ->firstOrFail();

        expect(
            $ticket->requester_id
        )->toBeNull();

        expect(
            $ticket->guest_name
        )->toBe(
            'Public Visitor'
        );

        expect(
            $ticket->status
        )->toBe(
            SupportTicket::STATUS_OPEN
        );

        expect(
            $ticket
                ->histories()
                ->count()
        )->toBe(1);
    }
);

it(
    'rejects the public contact honeypot',
    function (): void {
        $this->post(
            route(
                'public.contact.store'
            ),
            [
                'name' => 'Bot',
                'email' => 'bot@example.test',
                'category' => 'general',
                'subject' => 'Spam message',
                'message' => 'This message is intentionally long enough.',
                'privacy_acknowledged' => true,
                'website' => 'https://spam.test',
            ]
        )
            ->assertSessionHasErrors(
                'website'
            );

        expect(
            SupportTicket::count()
        )->toBe(0);
    }
);
