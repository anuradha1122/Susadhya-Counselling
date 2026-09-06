import Pagination from "@/Components/Pagination";
import ClientLayout from "@/Layouts/ClientLayout";
import {
    Head,
    Link,
    router,
} from "@inertiajs/react";
import {
    LifeBuoy,
    MessageSquarePlus,
    Star,
} from "lucide-react";
import { useState } from "react";

function humanize(value) {
    return String(value ?? "")
        .replaceAll("_", " ")
        .replace(/\b\w/g, (char) =>
            char.toUpperCase(),
        );
}

function badge(status) {
    const styles = {
        open: "bg-sky-50 text-sky-700 ring-sky-200",
        in_progress:
            "bg-indigo-50 text-indigo-700 ring-indigo-200",
        waiting_on_client:
            "bg-amber-50 text-amber-800 ring-amber-200",
        resolved:
            "bg-emerald-50 text-emerald-700 ring-emerald-200",
        closed:
            "bg-slate-100 text-slate-700 ring-slate-200",
    };

    return (
        styles[status] ??
        "bg-slate-100 text-slate-700 ring-slate-200"
    );
}

export default function Index({
    tickets,
    filters,
    statuses,
    categories,
}) {
    const [form, setForm] =
        useState(filters);

    const submit = (event) => {
        event.preventDefault();

        router.get(
            route(
                "client.support.index",
            ),
            form,
            {
                preserveState: true,
                preserveScroll: true,
                replace: true,
            },
        );
    };

    return (
        <ClientLayout>
            <Head title="Support" />

            <div className="mx-auto max-w-7xl space-y-6">
                <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <div className="flex items-center gap-2 text-sm font-semibold text-indigo-600">
                            <LifeBuoy className="h-4 w-4" />
                            Support & Feedback
                        </div>

                        <h1 className="mt-1 text-2xl font-bold text-slate-900">
                            My Support Requests
                        </h1>

                        <p className="mt-2 text-sm text-slate-600">
                            Track operational,
                            technical,
                            appointment and
                            account support
                            requests.
                        </p>
                    </div>

                    <div className="flex flex-wrap gap-3">
                        <Link
                            href={route(
                                "client.support.feedback.create",
                            )}
                            className="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50"
                        >
                            <Star className="h-4 w-4" />
                            Session Feedback
                        </Link>

                        <Link
                            href={route(
                                "client.support.create",
                            )}
                            className="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700"
                        >
                            <MessageSquarePlus className="h-4 w-4" />
                            New Request
                        </Link>
                    </div>
                </div>

                <form
                    onSubmit={submit}
                    className="grid gap-3 rounded-xl border border-slate-200 bg-white p-4 shadow-sm md:grid-cols-4"
                >
                    <input
                        value={
                            form.search ?? ""
                        }
                        onChange={(event) =>
                            setForm({
                                ...form,
                                search:
                                    event
                                        .target
                                        .value,
                            })
                        }
                        placeholder="Search tickets"
                        className="rounded-lg border-slate-300 text-sm"
                    />

                    <select
                        value={
                            form.status ?? ""
                        }
                        onChange={(event) =>
                            setForm({
                                ...form,
                                status:
                                    event
                                        .target
                                        .value,
                            })
                        }
                        className="rounded-lg border-slate-300 text-sm"
                    >
                        <option value="">
                            All statuses
                        </option>

                        {statuses.map(
                            (status) => (
                                <option
                                    key={
                                        status
                                    }
                                    value={
                                        status
                                    }
                                >
                                    {humanize(
                                        status,
                                    )}
                                </option>
                            ),
                        )}
                    </select>

                    <select
                        value={
                            form.category ??
                            ""
                        }
                        onChange={(event) =>
                            setForm({
                                ...form,
                                category:
                                    event
                                        .target
                                        .value,
                            })
                        }
                        className="rounded-lg border-slate-300 text-sm"
                    >
                        <option value="">
                            All categories
                        </option>

                        {categories.map(
                            (category) => (
                                <option
                                    key={
                                        category
                                    }
                                    value={
                                        category
                                    }
                                >
                                    {humanize(
                                        category,
                                    )}
                                </option>
                            ),
                        )}
                    </select>

                    <button
                        className="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800"
                        type="submit"
                    >
                        Filter
                    </button>
                </form>

                <div className="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                    {tickets.data.length ===
                    0 ? (
                        <div className="p-10 text-center">
                            <LifeBuoy className="mx-auto h-10 w-10 text-slate-300" />

                            <h2 className="mt-3 font-semibold text-slate-900">
                                No support
                                requests found
                            </h2>

                            <p className="mt-2 text-sm text-slate-500">
                                Create a request
                                whenever you need
                                help using the
                                platform.
                            </p>
                        </div>
                    ) : (
                        <div className="divide-y divide-slate-100">
                            {tickets.data.map(
                                (ticket) => (
                                    <Link
                                        key={
                                            ticket.uuid
                                        }
                                        href={route(
                                            "client.support.show",
                                            ticket.uuid,
                                        )}
                                        className="block p-5 hover:bg-slate-50"
                                    >
                                        <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                            <div>
                                                <div className="font-semibold text-slate-900">
                                                    {
                                                        ticket.subject
                                                    }
                                                </div>

                                                <div className="mt-1 text-xs text-slate-500">
                                                    {humanize(
                                                        ticket.category,
                                                    )}
                                                    {" · "}
                                                    {ticket.uuid.slice(
                                                        0,
                                                        8,
                                                    )}
                                                </div>
                                            </div>

                                            <span
                                                className={`inline-flex w-fit rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset ${badge(
                                                    ticket.status,
                                                )}`}
                                            >
                                                {humanize(
                                                    ticket.status,
                                                )}
                                            </span>
                                        </div>
                                    </Link>
                                ),
                            )}
                        </div>
                    )}
                </div>

                <Pagination
                    links={
                        tickets.links
                    }
                />
            </div>
        </ClientLayout>
    );
}
