import Pagination from "@/Components/Pagination";
import ComplianceLayout from "@/Layouts/ComplianceLayout";
import { Head, router } from "@inertiajs/react";
import { useState } from "react";

function formatDate(value) {
    if (!value) return "Not provided";

    return new Intl.DateTimeFormat("en-LK", {
        dateStyle: "medium",
        timeStyle: "short",
    }).format(new Date(value));
}

function Badge({ children, tone = "slate" }) {
    const tones = {
        slate: "bg-slate-100 text-slate-700",
        emerald: "bg-emerald-50 text-emerald-700",
        rose: "bg-rose-50 text-rose-700",
        amber: "bg-amber-50 text-amber-700",
    };

    return (
        <span
            className={`rounded-full px-2.5 py-1 text-xs font-medium ${
                tones[tone] ?? tones.slate
            }`}
        >
            {children}
        </span>
    );
}

export default function Index({ events, filters, categories }) {
    const [form, setForm] = useState(filters);

    const submit = (event) => {
        event.preventDefault();

        router.get(
            route("compliance.audit-events.index"),
            form,
            {
                preserveState: true,
                replace: true,
            },
        );
    };

    const reset = () => {
        router.get(route("compliance.audit-events.index"));
    };

    return (
        <ComplianceLayout title="Audit Logs">
            <Head title="Audit Logs" />

            <div className="mx-auto max-w-7xl space-y-6">
                <div>
                    <h2 className="text-xl font-semibold text-slate-900">
                        Audit Logs
                    </h2>
                    <p className="mt-1 text-sm text-slate-500">
                        Append-oriented platform activity without raw clinical
                        narratives, passwords, tokens or payment secrets.
                    </p>
                </div>

                <form
                    onSubmit={submit}
                    className="grid gap-4 rounded-xl border border-slate-200 bg-white p-5 shadow-sm md:grid-cols-5"
                >
                    <input
                        type="text"
                        value={form.search}
                        onChange={(event) =>
                            setForm({ ...form, search: event.target.value })
                        }
                        placeholder="Event, actor or UUID"
                        className="rounded-md border-slate-300 text-sm"
                    />

                    <select
                        value={form.category}
                        onChange={(event) =>
                            setForm({ ...form, category: event.target.value })
                        }
                        className="rounded-md border-slate-300 text-sm"
                    >
                        <option value="">All categories</option>
                        {categories.map((category) => (
                            <option key={category} value={category}>
                                {category}
                            </option>
                        ))}
                    </select>

                    <select
                        value={form.result}
                        onChange={(event) =>
                            setForm({ ...form, result: event.target.value })
                        }
                        className="rounded-md border-slate-300 text-sm"
                    >
                        <option value="">All results</option>
                        <option value="success">Success</option>
                        <option value="failure">Failure</option>
                        <option value="blocked">Blocked</option>
                    </select>

                    <input
                        type="date"
                        value={form.from}
                        onChange={(event) =>
                            setForm({ ...form, from: event.target.value })
                        }
                        className="rounded-md border-slate-300 text-sm"
                    />

                    <input
                        type="date"
                        value={form.to}
                        onChange={(event) =>
                            setForm({ ...form, to: event.target.value })
                        }
                        className="rounded-md border-slate-300 text-sm"
                    />

                    <div className="flex gap-3 md:col-span-5">
                        <button
                            type="submit"
                            className="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700"
                        >
                            Apply filters
                        </button>

                        <button
                            type="button"
                            onClick={reset}
                            className="rounded-md border border-slate-300 px-4 py-2 text-sm text-slate-700"
                        >
                            Reset
                        </button>
                    </div>
                </form>

                <div className="space-y-3">
                    {events.data.map((event) => (
                        <div
                            key={event.uuid}
                            className="rounded-xl border border-slate-200 bg-white p-5 shadow-sm"
                        >
                            <div className="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                                <div>
                                    <div className="flex flex-wrap items-center gap-2">
                                        <p className="font-semibold text-slate-900">
                                            {event.event}
                                        </p>

                                        <Badge>{event.category}</Badge>

                                        <Badge
                                            tone={
                                                event.result === "success"
                                                    ? "emerald"
                                                    : event.result === "blocked"
                                                      ? "amber"
                                                      : "rose"
                                            }
                                        >
                                            {event.result}
                                        </Badge>
                                    </div>

                                    <p className="mt-2 text-sm text-slate-600">
                                        Action: {event.action}
                                    </p>

                                    <p className="mt-1 text-sm text-slate-500">
                                        Actor:{" "}
                                        {event.actor
                                            ? `${event.actor.name} (${event.actor.email})`
                                            : "System"}
                                    </p>

                                    {event.subject_uuid && (
                                        <p className="mt-1 break-all text-xs text-slate-500">
                                            Subject UUID: {event.subject_uuid}
                                        </p>
                                    )}

                                    {event.ip_address && (
                                        <p className="mt-1 text-xs text-slate-500">
                                            IP: {event.ip_address}
                                        </p>
                                    )}
                                </div>

                                <p className="text-xs text-slate-500">
                                    {formatDate(event.occurred_at)}
                                </p>
                            </div>

                            {Object.keys(event.metadata ?? {}).length > 0 && (
                                <details className="mt-4 rounded-lg bg-slate-50 p-3">
                                    <summary className="cursor-pointer text-sm font-medium text-slate-700">
                                        Metadata
                                    </summary>

                                    <pre className="mt-3 overflow-x-auto whitespace-pre-wrap break-words text-xs text-slate-600">
                                        {JSON.stringify(event.metadata, null, 2)}
                                    </pre>
                                </details>
                            )}
                        </div>
                    ))}

                    {events.data.length === 0 && (
                        <div className="rounded-xl border border-slate-200 bg-white p-8 text-center text-sm text-slate-500">
                            No audit events match the selected filters.
                        </div>
                    )}
                </div>

                <Pagination links={events.links} />
            </div>
        </ComplianceLayout>
    );
}
