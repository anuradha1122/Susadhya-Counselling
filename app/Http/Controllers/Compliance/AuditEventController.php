<?php

namespace App\Http\Controllers\Compliance;

use App\Http\Controllers\Controller;
use App\Models\AuditEvent;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AuditEventController extends Controller
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

            'category' => [
                'nullable',
                'string',
                'max:80',
            ],

            'result' => [
                'nullable',
                'string',
                'max:32',
            ],

            'from' => [
                'nullable',
                'date',
            ],

            'to' => [
                'nullable',
                'date',
                'after_or_equal:from',
            ],
        ]);

        $query = AuditEvent::query()
            ->with('actor:id,name,email');

        if (
            isset($filters['search'])
            && $filters['search'] !== ''
        ) {
            $search = $filters['search'];

            $query->where(
                function ($query) use ($search): void {
                    $query
                        ->where(
                            'event',
                            'like',
                            "%{$search}%"
                        )
                        ->orWhere(
                            'action',
                            'like',
                            "%{$search}%"
                        )
                        ->orWhere(
                            'subject_uuid',
                            'like',
                            "%{$search}%"
                        )
                        ->orWhereHas(
                            'actor',
                            function ($actorQuery) use ($search): void {
                                $actorQuery
                                    ->where(
                                        'name',
                                        'like',
                                        "%{$search}%"
                                    )
                                    ->orWhere(
                                        'email',
                                        'like',
                                        "%{$search}%"
                                    );
                            }
                        );
                }
            );
        }

        if (
            isset($filters['category'])
            && $filters['category'] !== ''
        ) {
            $query->where(
                'category',
                $filters['category']
            );
        }

        if (
            isset($filters['result'])
            && $filters['result'] !== ''
        ) {
            $query->where(
                'result',
                $filters['result']
            );
        }

        if (
            isset($filters['from'])
            && $filters['from'] !== ''
        ) {
            $query->whereDate(
                'occurred_at',
                '>=',
                $filters['from']
            );
        }

        if (
            isset($filters['to'])
            && $filters['to'] !== ''
        ) {
            $query->whereDate(
                'occurred_at',
                '<=',
                $filters['to']
            );
        }

        $events = $query
            ->latest('occurred_at')
            ->paginate(25)
            ->withQueryString()
            ->through(
                fn (AuditEvent $event): array => [
                    'uuid' => $event->uuid,

                    'category' => $event->category,

                    'event' => $event->event,

                    'action' => $event->action,

                    'result' => $event->result,

                    'purpose_code' => $event->purpose_code,

                    'subject_type' => $event->subject_type,

                    'subject_id' => $event->subject_id,

                    'subject_uuid' => $event->subject_uuid,

                    'actor' => $event->actor
                        ? [
                            'name' => $event->actor->name,

                            'email' => $event->actor->email,
                        ]
                        : null,

                    'ip_address' => $event->ip_address,

                    'request_id' => $event->request_id,

                    'metadata' => $event->metadata ?? [],

                    'occurred_at' => $event->occurred_at
                        ?->toIso8601String(),
                ]
            );

        return Inertia::render(
            'Compliance/AuditEvents/Index',
            [
                'events' => $events,

                'filters' => [
                    'search' => $filters['search'] ?? '',

                    'category' => $filters['category'] ?? '',

                    'result' => $filters['result'] ?? '',

                    'from' => $filters['from'] ?? '',

                    'to' => $filters['to'] ?? '',
                ],

                'categories' => AuditEvent::query()
                    ->select('category')
                    ->distinct()
                    ->orderBy('category')
                    ->pluck('category'),
            ]
        );
    }
}
