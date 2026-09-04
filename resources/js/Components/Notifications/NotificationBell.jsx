import { Link } from "@inertiajs/react";
import { Bell } from "lucide-react";
import { useEffect, useState } from "react";

export default function NotificationBell() {
    const [summary, setSummary] = useState({
        unread_count: 0,
        notifications: [],
    });

    useEffect(() => {
        let active = true;

        fetch(route("notifications.summary"), {
            headers: {
                Accept: "application/json",
                "X-Requested-With": "XMLHttpRequest",
            },
            credentials: "same-origin",
        })
            .then((response) => {
                if (!response.ok) {
                    throw new Error(
                        "Unable to load notifications.",
                    );
                }

                return response.json();
            })
            .then((data) => {
                if (active) {
                    setSummary(data);
                }
            })
            .catch(() => {
                // Notification failure should never break the main layout.
            });

        return () => {
            active = false;
        };
    }, []);

    return (
        <Link
            href={route("notifications.index")}
            className="relative inline-flex h-10 w-10 items-center justify-center rounded-lg text-slate-600 transition hover:bg-slate-100 hover:text-slate-900"
            aria-label={`Notifications${
                summary.unread_count > 0
                    ? ` (${summary.unread_count} unread)`
                    : ""
            }`}
        >
            <Bell className="h-5 w-5" />

            {summary.unread_count > 0 && (
                <span className="absolute -right-1 -top-1 inline-flex min-h-5 min-w-5 items-center justify-center rounded-full bg-indigo-600 px-1 text-[10px] font-semibold text-white">
                    {summary.unread_count > 99
                        ? "99+"
                        : summary.unread_count}
                </span>
            )}
        </Link>
    );
}