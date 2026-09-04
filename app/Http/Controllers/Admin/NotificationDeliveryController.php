<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NotificationDelivery;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class NotificationDeliveryController extends Controller
{
    public function index(Request $request): Response
    {
        $filters = $request->validate([
            'search' => [
                'nullable',
                'string',
                'max:100',
            ],

            'event_type' => [
                'nullable',
                'string',
                'max:100',
            ],

            'channel' => [
                'nullable',
                'in:database,mail,sms',
            ],

            'status' => [
                'nullable',
                'in:pending,sending,sent,delivered,failed,skipped,unavailable',
            ],
        ]);

        $deliveries = NotificationDelivery::query()
            ->with([
                'dispatch.user:id,name,email',
            ])
            ->when(
                $filters['search'] ?? null,
                function ($query, string $search): void {
                    $query->whereHas(
                        'dispatch.user',
                        function ($userQuery) use ($search): void {
                            $userQuery
                                ->where('name', 'like', "%{$search}%")
                                ->orWhere(
                                    'email',
                                    'like',
                                    "%{$search}%"
                                );
                        }
                    );
                }
            )
            ->when(
                $filters['event_type'] ?? null,
                fn ($query, string $eventType) => $query
                    ->whereHas(
                        'dispatch',
                        fn ($dispatchQuery) => $dispatchQuery
                            ->where(
                                'event_type',
                                $eventType
                            )
                    )
            )
            ->when(
                $filters['channel'] ?? null,
                fn ($query, string $channel) => $query
                    ->where('channel', $channel)
            )
            ->when(
                $filters['status'] ?? null,
                fn ($query, string $status) => $query
                    ->where('status', $status)
            )
            ->latest('id')
            ->paginate(30)
            ->withQueryString()
            ->through(
                fn (NotificationDelivery $delivery) => [
                    'id' => $delivery->id,
                    'channel' => $delivery->channel,
                    'status' => $delivery->status,
                    'attempt' => $delivery->attempt,

                    'event_type' => $delivery
                        ->dispatch
                        ?->event_type,

                    'template_key' => $delivery
                        ->dispatch
                        ?->template_key,

                    'user' => $delivery
                        ->dispatch
                        ?->user
                        ? [
                            'name' => $delivery
                                ->dispatch
                                ->user
                                ->name,

                            'email' => $delivery
                                ->dispatch
                                ->user
                                ->email,
                        ]
                        : null,

                    'attempted_at' => $delivery
                        ->attempted_at
                        ?->toIso8601String(),

                    'sent_at' => $delivery
                        ->sent_at
                        ?->toIso8601String(),

                    'failed_at' => $delivery
                        ->failed_at
                        ?->toIso8601String(),

                    'error_message' => $delivery
                        ->error_message,
                ]
            );

        return Inertia::render(
            'Admin/Notifications/Deliveries',
            [
                'deliveries' => $deliveries,
                'filters' => $filters,
            ]
        );
    }
}
