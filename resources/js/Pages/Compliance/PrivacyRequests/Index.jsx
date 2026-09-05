import Pagination from "@/Components/Pagination";
import ComplianceLayout from "@/Layouts/ComplianceLayout";
import { Head, Link, router } from "@inertiajs/react";
import { useState } from "react";

function formatDate(value) {
    if (!value) return "Not provided";

    return new Intl.DateTimeFormat("en-LK", {
        dateStyle: "medium",
        timeStyle: "short",
    }).format(new Date(value));
}

function humanize(value) {
    return String(value ?? "")
        .replaceAll("_", " ")
        .replace(/\b\w/g, (character) => character.toUpperCase());
}

export default function Index({
    privacyRequests,
    filters,
    types,
    statuses,
}) {
    const [form, setForm] = useState(filters);

    const submit = (event) => {
        event.preventDefault();

        router.get(route("compliance.privacy-requests.index"), form, {
            preserveState: true,
            replace: true,
        });
    };

    return (
        <ComplianceLayout title="Privacy Requests">
            <Head title="Privacy Requests" />

            <div className="mx-auto max-w-7xl space-y-6">
                <div>
                    <h2 className="text-xl font-semibold text-slate-900">
                        Privacy Requests
                    </h2>
                    <p className="mt-1 text-sm text-slate-500">
                        Identity verification, review, decision, export and
                        completion are kept as traceable workflow steps.
                    </p>
                </div>

                <form
                    onSubmit={submit}
                    className="grid gap-4 rounded-xl border border-slate-200 bg-white p-5 shadow-sm md:grid-cols-4"
                >
                    <input
                        value={form.search}
                        onChange={(event) =>
                            setForm({ ...form, search: event.target.value })
                        }
                        placeholder="Client, email or request UUID"
                        className="rounded-md border-slate-300 text-sm md:col-span-2"
                    />

                    <select
                        value={form.type}
                        onChange={(event) =>
                            setForm({ ...form, type: event.target.value })
                        }
                        className="rounded-md border-slate-300 text-sm"
                    >
                        <option value="">All types</option>
                        {types.map((type) => (
                            <option key={type} value={type}>
                                {humanize(type)}
                            </option>
                        ))}
                    </select>

                    <select
                        value={form.status}
                        onChange={(event) =>
                            setForm({ ...form, status: event.target.value })
                        }
                        className="rounded-md border-slate-300 text-sm"
                    >
                        <option value="">All statuses</option>
                        {statuses.map((status) => (
                            <option key={status} value={status}>
                                {humanize(status)}
                            </option>
                        ))}
                    </select>

                    <div className="flex gap-3 md:col-span-4">
                        <button
                            type="submit"
                            className="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white"
                        >
                            Apply filters
                        </button>

                        <Link
                            href={route("compliance.privacy-requests.index")}
                            className="rounded-md border border-slate-300 px-4 py-2 text-sm text-slate-700"
                        >
                            Reset
                        </Link>
                    </div>
                </form>

                <div className="space-y-3">
                    {privacyRequests.data.map((item) => (
                        <Link
                            key={item.uuid}
                            href={route(
                                "compliance.privacy-requests.show",
                                item.uuid,
                            )}
                            className="block rounded-xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-indigo-300"
                        >
                            <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <p className="font-semibold text-slate-900">
                                        {item.subject?.name ?? "Unknown client"}
                                    </p>

                                    <p className="mt-1 text-sm text-slate-500">
                                        {item.subject?.email ?? "No email"}
                                    </p>

                                    <div className="mt-3 flex flex-wrap gap-2">
                                        <span className="rounded-full bg-indigo-50 px-3 py-1 text-xs font-medium text-indigo-700">
                                            {humanize(item.type)}
                                        </span>

                                        <span className="rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-700">
                                            {humanize(item.status)}
                                        </span>
                                    </div>
                                </div>

                                <div className="text-sm text-slate-500 sm:text-right">
                                    <p>{formatDate(item.submitted_at)}</p>
                                    <p className="mt-1 text-xs">
                                        {item.uuid}
                                    </p>
                                </div>
                            </div>
                        </Link>
                    ))}

                    {privacyRequests.data.length === 0 && (
                        <div className="rounded-xl border border-slate-200 bg-white p-8 text-center text-sm text-slate-500">
                            No privacy requests found.
                        </div>
                    )}
                </div>

                <Pagination links={privacyRequests.links} />
            </div>
        </ComplianceLayout>
    );
}
