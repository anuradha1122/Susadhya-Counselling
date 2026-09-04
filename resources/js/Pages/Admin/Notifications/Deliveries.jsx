import Pagination from "@/Components/Pagination";
import AdminLayout from "@/Layouts/AdminLayout";
import {
    Head,
    router,
    usePage,
} from "@inertiajs/react";
import { useState } from "react";

function formatDate(value) {
    if (!value) {
        return "—";
    }

    return new Intl.DateTimeFormat("en-LK", {
        dateStyle: "medium",
        timeStyle: "short",
    }).format(new Date(value));
}

export default function Deliveries({
    deliveries,
    filters,
}) {
    const { auth } = usePage().props;

    const [search, setSearch] = useState(
        filters.search ?? "",
    );

    const [channel, setChannel] = useState(
        filters.channel ?? "",
    );

    const [status, setStatus] = useState(
        filters.status ?? "",
    );

    const submit = (event) => {
        event.preventDefault();

        router.get(
            route(
                "admin.notifications.deliveries.index",
            ),
            {
                search: search || undefined,
                channel: channel || undefined,
                status: status || undefined,
            },
            {
                preserveState: true,
                replace: true,
            },
        );
    };

    return (
        <AdminLayout
            user={auth.user}
            header="Notification Delivery Log"
        >
            <Head title="Notification Delivery Log" />

            <div className="py-6">
                <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div className="mb-6">
                        <h1 className="text-2xl font-semibold text-slate-900">
                            Notification delivery log
                        </h1>

                        <p className="mt-1 text-sm text-slate-500">
                            Trace in-app, email and SMS
                            notification delivery attempts.
                        </p>
                    </div>

                    <form
                        onSubmit={submit}
                        className="mb-6 grid gap-3 rounded-lg bg-white p-4 shadow-sm sm:grid-cols-4"
                    >
                        <input
                            type="search"
                            value={search}
                            onChange={(event) =>
                                setSearch(
                                    event.target.value,
                                )
                            }
                            placeholder="User name or email"
                            className="rounded-md border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        />

                        <select
                            value={channel}
                            onChange={(event) =>
                                setChannel(
                                    event.target.value,
                                )
                            }
                            className="rounded-md border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        >
                            <option value="">
                                All channels
                            </option>
                            <option value="database">
                                In-app
                            </option>
                            <option value="mail">
                                Email
                            </option>
                            <option value="sms">
                                SMS
                            </option>
                        </select>

                        <select
                            value={status}
                            onChange={(event) =>
                                setStatus(
                                    event.target.value,
                                )
                            }
                            className="rounded-md border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        >
                            <option value="">
                                All statuses
                            </option>
                            <option value="pending">
                                Pending
                            </option>
                            <option value="sending">
                                Sending
                            </option>
                            <option value="sent">
                                Sent
                            </option>
                            <option value="failed">
                                Failed
                            </option>
                            <option value="skipped">
                                Skipped
                            </option>
                            <option value="unavailable">
                                Unavailable
                            </option>
                        </select>

                        <button
                            type="submit"
                            className="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-indigo-700"
                        >
                            Filter
                        </button>
                    </form>

                    <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <div className="overflow-x-auto">
                            <table className="min-w-full divide-y divide-slate-200">
                                <thead className="bg-slate-50">
                                    <tr>
                                        <Header>
                                            User
                                        </Header>
                                        <Header>
                                            Event
                                        </Header>
                                        <Header>
                                            Channel
                                        </Header>
                                        <Header>
                                            Status
                                        </Header>
                                        <Header>
                                            Attempted
                                        </Header>
                                        <Header>
                                            Error
                                        </Header>
                                    </tr>
                                </thead>

                                <tbody className="divide-y divide-slate-100 bg-white">
                                    {deliveries.data.map(
                                        (delivery) => (
                                            <tr
                                                key={
                                                    delivery.id
                                                }
                                            >
                                                <Cell>
                                                    <div className="font-medium text-slate-900">
                                                        {delivery
                                                            .user
                                                            ?.name ??
                                                            "Unknown"}
                                                    </div>

                                                    <div className="text-xs text-slate-500">
                                                        {delivery
                                                            .user
                                                            ?.email ??
                                                            "—"}
                                                    </div>
                                                </Cell>

                                                <Cell>
                                                    <code className="text-xs text-slate-600">
                                                        {
                                                            delivery.event_type
                                                        }
                                                    </code>
                                                </Cell>

                                                <Cell>
                                                    <span className="capitalize">
                                                        {delivery.channel ===
                                                        "database"
                                                            ? "In-app"
                                                            : delivery.channel}
                                                    </span>
                                                </Cell>

                                                <Cell>
                                                    <StatusBadge
                                                        status={
                                                            delivery.status
                                                        }
                                                    />
                                                </Cell>

                                                <Cell>
                                                    {formatDate(
                                                        delivery.attempted_at,
                                                    )}
                                                </Cell>

                                                <Cell>
                                                    <span className="block max-w-xs truncate text-xs text-rose-600">
                                                        {delivery.error_message ??
                                                            "—"}
                                                    </span>
                                                </Cell>
                                            </tr>
                                        ),
                                    )}

                                    {deliveries.data.length ===
                                        0 && (
                                        <tr>
                                            <td
                                                colSpan="6"
                                                className="px-6 py-10 text-center text-sm text-slate-500"
                                            >
                                                No delivery
                                                records found.
                                            </td>
                                        </tr>
                                    )}
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {deliveries.links?.length >
                        3 && (
                        <div className="mt-6">
                            <Pagination
                                links={
                                    deliveries.links
                                }
                            />
                        </div>
                    )}
                </div>
            </div>
        </AdminLayout>
    );
}

function Header({ children }) {
    return (
        <th className="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
            {children}
        </th>
    );
}

function Cell({ children }) {
    return (
        <td className="whitespace-nowrap px-6 py-4 text-sm text-slate-600">
            {children}
        </td>
    );
}

function StatusBadge({ status }) {
    const classes = {
        sent: "bg-emerald-50 text-emerald-700",
        delivered:
            "bg-emerald-50 text-emerald-700",
        failed: "bg-rose-50 text-rose-700",
        unavailable:
            "bg-amber-50 text-amber-700",
        skipped: "bg-slate-100 text-slate-600",
        pending: "bg-blue-50 text-blue-700",
        sending: "bg-blue-50 text-blue-700",
    };

    return (
        <span
            className={`inline-flex rounded-full px-2.5 py-1 text-xs font-medium ${
                classes[status] ??
                "bg-slate-100 text-slate-600"
            }`}
        >
            {status}
        </span>
    );
}