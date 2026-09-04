<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Inertia\Inertia;
use Inertia\Response;

class NotificationController extends Controller
{
    public function index(Request $request): Response
    {
        $notifications = $request
            ->user()
            ->notifications()
            ->latest()
            ->paginate(20)
            ->withQueryString()
            ->through(
                fn (DatabaseNotification $notification) => [
                    'id' => $notification->id,
                    'title' => data_get(
                        $notification->data,
                        'title',
                        'Notification'
                    ),
                    'body' => data_get(
                        $notification->data,
                        'body'
                    ),
                    'url' => data_get(
                        $notification->data,
                        'url'
                    ),
                    'event_type' => data_get(
                        $notification->data,
                        'event_type'
                    ),
                    'read_at' => $notification
                        ->read_at
                        ?->toIso8601String(),
                    'created_at' => $notification
                        ->created_at
                        ?->toIso8601String(),
                ]
            );

        return Inertia::render(
            'Notifications/Index',
            [
                'notifications' => $notifications,
                'unreadCount' => $request
                    ->user()
                    ->unreadNotifications()
                    ->count(),
            ]
        );
    }

    public function summary(
        Request $request
    ): JsonResponse {
        $notifications = $request
            ->user()
            ->unreadNotifications()
            ->latest()
            ->limit(5)
            ->get()
            ->map(
                fn (DatabaseNotification $notification) => [
                    'id' => $notification->id,
                    'title' => data_get(
                        $notification->data,
                        'title',
                        'Notification'
                    ),
                    'body' => data_get(
                        $notification->data,
                        'body'
                    ),
                    'url' => data_get(
                        $notification->data,
                        'url'
                    ),
                    'created_at' => $notification
                        ->created_at
                        ?->toIso8601String(),
                ]
            )
            ->values();

        return response()->json([
            'unread_count' => $request
                ->user()
                ->unreadNotifications()
                ->count(),

            'notifications' => $notifications,
        ]);
    }

    public function markAsRead(
        Request $request,
        string $notification
    ): RedirectResponse {
        $record = $this->ownedNotification(
            $request->user(),
            $notification
        );

        $record->markAsRead();

        return back();
    }

    public function markAllAsRead(
        Request $request
    ): RedirectResponse {
        $request
            ->user()
            ->unreadNotifications
            ->markAsRead();

        return back()->with(
            'success',
            'All notifications marked as read.'
        );
    }

    private function ownedNotification(
        User $user,
        string $notificationId
    ): DatabaseNotification {
        return DatabaseNotification::query()
            ->where('id', $notificationId)
            ->where('notifiable_type', User::class)
            ->where('notifiable_id', $user->id)
            ->firstOrFail();
    }
}
