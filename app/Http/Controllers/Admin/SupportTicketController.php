<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Support\UpdateSupportTicketRequest;
use App\Models\SupportTicket;
use App\Models\User;
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

            'priority' => [
                'nullable',
                'string',
                'max:20',
            ],

            'owner' => [
                'nullable',
                'string',
                'max:30',
            ],
        ]);

        $query =
            SupportTicket::query()
                ->with([
                    'requester:id,name,email',
                    'owner:id,name,email',
                ]);

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
                        )
                        ->orWhere(
                            'guest_name',
                            'like',
                            "%{$search}%"
                        )
                        ->orWhere(
                            'guest_email',
                            'like',
                            "%{$search}%"
                        );
                }
            );
        }

        foreach (
            [
                'status',
                'category',
                'priority',
            ] as $field
        ) {
            if (
                filled(
                    $filters[$field]
                    ?? null
                )
            ) {
                $query->where(
                    $field,
                    $filters[$field]
                );
            }
        }

        if (
            ($filters['owner'] ?? '')
            === 'unassigned'
        ) {
            $query->whereNull(
                'owner_id'
            );
        }

        if (
            ($filters['owner'] ?? '')
            === 'mine'
        ) {
            $query->where(
                'owner_id',
                $request->user()->id
            );
        }

        $tickets =
            $query
                ->orderByRaw(
                    "CASE
                        WHEN priority = 'high' THEN 1
                        WHEN priority = 'normal' THEN 2
                        ELSE 3
                    END"
                )
                ->orderByDesc(
                    'last_activity_at'
                )
                ->orderByDesc('id')
                ->paginate(20)
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

                        'requester' => $ticket
                            ->requester
                            ? [
                                'name' => $ticket
                                    ->requester
                                    ->name,
                                'email' => $ticket
                                    ->requester
                                    ->email,
                                'type' => 'client',
                            ]
                            : [
                                'name' => $ticket
                                    ->guest_name
                                    ?? 'Public visitor',
                                'email' => $ticket
                                    ->guest_email,
                                'type' => 'guest',
                            ],

                        'owner' => $ticket->owner
                            ? [
                                'id' => $ticket
                                    ->owner
                                    ->id,
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
            'Admin/Support/Index',
            [
                'tickets' => $tickets,

                'filters' => [
                    'search' => $filters['search']
                        ?? '',
                    'status' => $filters['status']
                        ?? '',
                    'category' => $filters['category']
                        ?? '',
                    'priority' => $filters['priority']
                        ?? '',
                    'owner' => $filters['owner']
                        ?? '',
                ],

                'statuses' => SupportTicket::statuses(),

                'categories' => SupportTicket::categories(),

                'priorities' => SupportTicket::priorities(),
            ]
        );
    }

    public function show(
        SupportTicket $ticket
    ): Response {
        $ticket->load([
            'requester:id,name,email',
            'owner:id,name,email',

            'replies' => function (
                $query
            ): void {
                $query
                    ->with(
                        'user:id,name,email'
                    )
                    ->orderBy(
                        'created_at'
                    );
            },

            'histories' => function (
                $query
            ): void {
                $query
                    ->with([
                        'actor:id,name',
                        'fromOwner:id,name',
                        'toOwner:id,name',
                    ])
                    ->orderBy(
                        'created_at'
                    )
                    ->orderBy('id');
            },
        ]);

        $owners =
            User::query()
                ->where(
                    'is_active',
                    true
                )
                ->role([
                    'admin',
                    'super_admin',
                ])
                ->orderBy('name')
                ->get([
                    'id',
                    'name',
                    'email',
                ]);

        return Inertia::render(
            'Admin/Support/Show',
            [
                'ticket' => [
                    'uuid' => $ticket->uuid,
                    'subject' => $ticket->subject,
                    'description' => $ticket->description,
                    'category' => $ticket->category,
                    'priority' => $ticket->priority,
                    'status' => $ticket->status,
                    'owner_id' => $ticket->owner_id,

                    'requester' => $ticket
                        ->requester
                        ? [
                            'name' => $ticket
                                ->requester
                                ->name,
                            'email' => $ticket
                                ->requester
                                ->email,
                            'type' => 'client',
                        ]
                        : [
                            'name' => $ticket
                                ->guest_name
                                ?? 'Public visitor',
                            'email' => $ticket
                                ->guest_email,
                            'type' => 'guest',
                        ],

                    'owner' => $ticket->owner
                        ? [
                            'id' => $ticket
                                ->owner
                                ->id,
                            'name' => $ticket
                                ->owner
                                ->name,
                        ]
                        : null,

                    'resolved_at' => $ticket
                        ->resolved_at
                        ?->toISOString(),

                    'closed_at' => $ticket
                        ->closed_at
                        ?->toISOString(),

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
                                'is_internal' => $reply
                                    ->is_internal,
                                'author' => $reply
                                    ->user
                                    ?->name
                                    ?? 'System',
                                'created_at' => $reply
                                    ->created_at
                                    ?->toISOString(),
                            ]
                        )
                        ->values(),

                    'history' => $ticket
                        ->histories
                        ->map(
                            fn ($history): array => [
                                'uuid' => $history->uuid,
                                'event' => $history->event,
                                'from_status' => $history
                                    ->from_status,
                                'to_status' => $history
                                    ->to_status,
                                'from_priority' => $history
                                    ->from_priority,
                                'to_priority' => $history
                                    ->to_priority,
                                'from_owner' => $history
                                    ->fromOwner
                                    ?->name,
                                'to_owner' => $history
                                    ->toOwner
                                    ?->name,
                                'actor' => $history
                                    ->actor
                                    ?->name,
                                'note' => $history->note,
                                'created_at' => $history
                                    ->created_at
                                    ?->toISOString(),
                            ]
                        )
                        ->values(),
                ],

                'owners' => $owners,

                'statuses' => SupportTicket::statuses(),

                'priorities' => SupportTicket::priorities(),
            ]
        );
    }

    public function update(
        UpdateSupportTicketRequest $request,
        SupportTicket $ticket,
        SupportTicketService $support
    ): RedirectResponse {
        $support->updateAdmin(
            ticket: $ticket,
            actor: $request->user(),
            data: $request->validated()
        );

        return back()->with(
            'success',
            'Support request updated.'
        );
    }
}
