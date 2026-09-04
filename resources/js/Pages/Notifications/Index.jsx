import Pagination from "@/Components/Pagination";
import SecondaryButton from "@/Components/SecondaryButton";
import NotificationLayout from "@/Layouts/NotificationLayout";
import { Head, Link, router, usePage } from "@inertiajs/react";
import {
    Bell,
    CheckCheck,
    Settings,
} from "lucide-react";

function formatDate(value) {
    if (!value) {
        return "";
    }

    return new Intl.DateTimeFormat("en-LK", {
        dateStyle: "medium",
        timeStyle: "short",
    }).format(new Date(value));
}

export default function Index({
    notifications,
    unreadCount,
}) {
    const { auth } = usePage().props;

    const markAsRead = (id) => {
        router.patch(
            route("notifications.read", id),
            {},
            {
                preserveScroll: true,
            },
        );
    };

    const markAllAsRead = () => {
        router.patch(
            route("notifications.read-all"),
            {},
            {
                preserveScroll: true,
            },
        );
    };

    return (
        <NotificationLayout
            user={auth.user}
            header="Notifications"
        >
            <Head title="Notifications" />

            <div className="py-6">
                <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div className="mb-6 flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
                        <div>
                            <h1 className="text-2xl font-semibold text-slate-900">
                                Notifications
                            </h1>

                            <p className="mt-1 text-sm text-slate-500">
                                {unreadCount > 0
                                    ? `${unreadCount} unread notification${
                                          unreadCount === 1
                                              ? ""
                                              : "s"
                                      }.`
                                    : "You have no unread notifications."}
                            </p>
                        </div>

                        <div className="flex gap-2">
                            <Link
                                href={route(
                                    "notifications.preferences.edit",
                                )}
                                className="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 shadow-sm transition hover:bg-slate-50"
                            >
                                <Settings className="h-4 w-4" />
                                Preferences
                            </Link>

                            {unreadCount > 0 && (
                                <SecondaryButton
                                    type="button"
                                    onClick={markAllAsRead}
                                >
                                    <CheckCheck className="mr-2 h-4 w-4" />
                                    Mark all read
                                </SecondaryButton>
                            )}
                        </div>
                    </div>

                    <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        {notifications.data.length === 0 ? (
                            <div className="p-10 text-center">
                                <Bell className="mx-auto h-10 w-10 text-slate-300" />

                                <h2 className="mt-4 font-medium text-slate-900">
                                    No notifications yet
                                </h2>

                                <p className="mt-1 text-sm text-slate-500">
                                    Booking, payment and other
                                    relevant updates will appear
                                    here.
                                </p>
                            </div>
                        ) : (
                            <div className="divide-y divide-slate-100">
                                {notifications.data.map(
                                    (notification) => (
                                        <div
                                            key={notification.id}
                                            className={`p-5 ${
                                                notification.read_at
                                                    ? "bg-white"
                                                    : "bg-indigo-50/40"
                                            }`}
                                        >
                                            <div className="flex gap-4">
                                                <div
                                                    className={`mt-1 h-2.5 w-2.5 flex-none rounded-full ${
                                                        notification.read_at
                                                            ? "bg-slate-200"
                                                            : "bg-indigo-600"
                                                    }`}
                                                />

                                                <div className="min-w-0 flex-1">
                                                    <div className="flex flex-col justify-between gap-2 sm:flex-row">
                                                        <div>
                                                            <h3 className="font-medium text-slate-900">
                                                                {
                                                                    notification.title
                                                                }
                                                            </h3>

                                                            <p className="mt-1 text-sm leading-6 text-slate-600">
                                                                {
                                                                    notification.body
                                                                }
                                                            </p>

                                                            <p className="mt-2 text-xs text-slate-400">
                                                                {formatDate(
                                                                    notification.created_at,
                                                                )}
                                                            </p>
                                                        </div>

                                                        <div className="flex flex-none items-start gap-2">
                                                            {!notification.read_at && (
                                                                <button
                                                                    type="button"
                                                                    onClick={() =>
                                                                        markAsRead(
                                                                            notification.id,
                                                                        )
                                                                    }
                                                                    className="rounded-md px-3 py-1.5 text-xs font-medium text-indigo-700 transition hover:bg-indigo-50"
                                                                >
                                                                    Mark read
                                                                </button>
                                                            )}

                                                            {notification.url && (
                                                                <Link
                                                                    href={
                                                                        notification.url
                                                                    }
                                                                    className="rounded-md bg-indigo-600 px-3 py-1.5 text-xs font-medium text-white transition hover:bg-indigo-700"
                                                                >
                                                                    Open
                                                                </Link>
                                                            )}
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    ),
                                )}
                            </div>
                        )}
                    </div>

                    {notifications.links?.length > 3 && (
                        <div className="mt-6">
                            <Pagination
                                links={notifications.links}
                            />
                        </div>
                    )}
                </div>
            </div>
        </NotificationLayout>
    );
}