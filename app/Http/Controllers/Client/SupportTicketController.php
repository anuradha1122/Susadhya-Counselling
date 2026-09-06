<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Client\Support\StoreSupportTicketRequest;
use App\Models\SupportTicket;
use App\Services\Support\SupportTicketService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SupportTicketController extends Controller
{
    public function index(
        Request $request
    ): Response {
        $filters = $request->validate([
            'search' => [
                'nullable',
                'string',
                'max:150',
            ],

            'status' => [
                'nullable',
                'string',
                'max:40',
            ],

            'category' => [
                'nullable',
                'string',
                'max:40',
            ],
        ]);

        $query =
            SupportTicket::query()
                ->where(
                    'requester_id',
                    $request->user()->id
                )
                ->with(
                    'owner:id,name'
                );

        if (
            filled(
                $filters['search']
                ?? null
            )
        ) {
            $search =
                $filters['search'];

            $query->where(
                function ($query) use (
                    $search
                ): void {
                    $query
                        ->where(
                            'subject',
                            'like',
                            "%{$search}%"
                        )
                        ->orWhere(
                            'uuid',
                            'like',
                            "%{$search}%"
                        );
                }
            );
        }

        if (
            filled(
                $filters['status']
                ?? null
            )
            && in_array(
                $filters['status'],
                SupportTicket::statuses(),
                true
            )
        ) {
            $query->where(
                'status',
                $filters['status']
            );
        }

        if (
            filled(
                $filters['category']
                ?? null
            )
            && in_array(
                $filters['category'],
                SupportTicket::categories(),
                true
            )
        ) {
            $query->where(
                'category',
                $filters['category']
            );
        }

        $tickets = $query
            ->orderByDesc(
                'last_activity_at'
            )
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString()
            ->through(
                fn (
                    SupportTicket $ticket
                ): array => [
                    'uuid' => $ticket->uuid,
                    'subject' => $ticket->subject,
                    'category' => $ticket->category,
                    'priority' => $ticket->priority,
                    'status' => $ticket->status,
                    'owner' => $ticket->owner
                        ? [
                            'name' => $ticket
                                ->owner
                                ->name,
                        ]
                        : null,
                    'last_activity_at' => $ticket
                        ->last_activity_at
                        ?->toISOString(),
                    'created_at' => $ticket
                        ->created_at
                        ?->toISOString(),
                ]
            );

        return Inertia::render(
            'Client/Support/Index',
            [
                'tickets' => $tickets,

                'filters' => [
                    'search' => $filters['search']
                        ?? '',
                    'status' => $filters['status']
                        ?? '',
                    'category' => $filters['category']
                        ?? '',
                ],

                'statuses' => SupportTicket::statuses(),

                'categories' => SupportTicket::categories(),
            ]
        );
    }

    public function create(): Response
    {
        return Inertia::render(
            'Client/Support/Create',
            [
                'categories' => SupportTicket::categories(),
            ]
        );
    }

    public function store(
        StoreSupportTicketRequest $request,
        SupportTicketService $support
    ): RedirectResponse {
        $ticket =
            $support->createForClient(
                $request->user(),
                $request->validated()
            );

        return to_route(
            'client.support.show',
            $ticket
        )->with(
            'success',
            'Your support request has been submitted.'
        );
    }

    public function show(
        Request $request,
        SupportTicket $ticket
    ): Response {
        abort_unless(
            (int) $ticket->requester_id
                === (int) $request
                    ->user()
                    ->id,
            404
        );

        $ticket->load([
            'owner:id,name',

            'replies' => function (
                $query
            ): void {
                $query
                    ->where(
                        'is_internal',
                        false
                    )
                    ->with(
                        'user:id,name'
                    )
                    ->orderBy(
                        'created_at'
                    );
            },

            'histories' => function (
                $query
            ): void {
                $query
                    ->orderBy(
                        'created_at'
                    )
                    ->orderBy('id');
            },
        ]);

        return Inertia::render(
            'Client/Support/Show',
            [
                'ticket' => [
                    'uuid' => $ticket->uuid,
                    'subject' => $ticket->subject,
                    'category' => $ticket->category,
                    'priority' => $ticket->priority,
                    'status' => $ticket->status,
                    'description' => $ticket->description,
                    'owner' => $ticket->owner
                        ? [
                            'name' => $ticket
                                ->owner
                                ->name,
                        ]
                        : null,
                    'created_at' => $ticket
                        ->created_at
                        ?->toISOString(),
                    'last_activity_at' => $ticket
                        ->last_activity_at
                        ?->toISOString(),

                    'replies' => $ticket
                        ->replies
                        ->map(
                            fn ($reply): array => [
                                'uuid' => $reply->uuid,
                                'body' => $reply->body,
                                'author' => $reply->user
                                    ?->name
                                    ?? 'Support',
                                'is_mine' => (int) $reply->user_id
                                    === (int) $request
                                        ->user()
                                        ->id,
                                'created_at' => $reply
                                    ->created_at
                                    ?->toISOString(),
                            ]
                        )
                        ->values(),

                    /*
                     * Client-safe workflow history.
                     *
                     * Admin identities and internal notes are excluded.
                     */
                    'activity' => $ticket
                        ->histories
                        ->map(
                            fn ($history): array => [
                                'uuid' => $history->uuid,
                                'event' => $history->event,
                                'from_status' => $history
                                    ->from_status,
                                'to_status' => $history
                                    ->to_status,
                                'created_at' => $history
                                    ->created_at
                                    ?->toISOString(),
                            ]
                        )
                        ->values(),
                ],
            ]
        );
    }
}
