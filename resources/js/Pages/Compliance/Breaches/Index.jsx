import InputError from "@/Components/InputError";
import Pagination from "@/Components/Pagination";
import ComplianceLayout from "@/Layouts/ComplianceLayout";
import { Head, Link, router, useForm } from "@inertiajs/react";
import { useState } from "react";

function humanize(value) {
    return String(value ?? "")
        .replaceAll("_", " ")
        .replace(/\b\w/g, (character) => character.toUpperCase());
}

function nowForInput() {
    const date = new Date();
    date.setMinutes(date.getMinutes() - date.getTimezoneOffset());
    return date.toISOString().slice(0, 16);
}

export default function Index({
    breaches,
    filters,
    severities,
    statuses,
    assignees,
}) {
    const [filterForm, setFilterForm] = useState(filters);

    const form = useForm({
        title: "",
        severity: "medium",
        assigned_to: "",
        detected_at: nowForInput(),
        occurred_at: "",
        affected_subject_count: "",
        data_categories_text: "",
        systems_affected_text: "",
        summary: "",
    });

    const submitFilters = (event) => {
        event.preventDefault();

        router.get(route("compliance.breaches.index"), filterForm, {
            preserveState: true,
            replace: true,
        });
    };

    const create = (event) => {
        event.preventDefault();

        form.transform((data) => ({
            title: data.title,
            severity: data.severity,
            assigned_to: data.assigned_to || null,
            detected_at: data.detected_at,
            occurred_at: data.occurred_at || null,
            affected_subject_count:
                data.affected_subject_count === ""
                    ? null
                    : Number(data.affected_subject_count),
            data_categories: data.data_categories_text
                .split(",")
                .map((value) => value.trim())
                .filter(Boolean),
            systems_affected: data.systems_affected_text
                .split(",")
                .map((value) => value.trim())
                .filter(Boolean),
            summary: data.summary,
        }));

        form.post(route("compliance.breaches.store"));
    };

    return (
        <ComplianceLayout title="Data Breach Register">
            <Head title="Data Breach Register" />

            <div className="mx-auto max-w-7xl space-y-6">
                <div className="rounded-xl border border-rose-200 bg-rose-50 p-5">
                    <h2 className="font-semibold text-rose-950">
                        Data Breach Register
                    </h2>

                    <p className="mt-2 text-sm leading-6 text-rose-800">
                        Record investigation metadata, affected categories,
                        containment and notification decisions without copying
                        unnecessary clinical narratives into the general register.
                    </p>
                </div>

                <form
                    onSubmit={create}
                    className="rounded-xl border border-slate-200 bg-white p-6 shadow-sm"
                >
                    <h3 className="font-semibold text-slate-900">
                        Register breach
                    </h3>

                    <div className="mt-4 grid gap-4 md:grid-cols-2">
                        <div>
                            <label className="text-sm font-medium">
                                Title
                            </label>
                            <input
                                value={form.data.title}
                                onChange={(event) =>
                                    form.setData("title", event.target.value)
                                }
                                className="mt-1 block w-full rounded-md border-slate-300"
                            />
                            <InputError
                                message={form.errors.title}
                                className="mt-2"
                            />
                        </div>

                        <div>
                            <label className="text-sm font-medium">
                                Severity
                            </label>
                            <select
                                value={form.data.severity}
                                onChange={(event) =>
                                    form.setData(
                                        "severity",
                                        event.target.value,
                                    )
                                }
                                className="mt-1 block w-full rounded-md border-slate-300"
                            >
                                {severities.map((severity) => (
                                    <option key={severity} value={severity}>
                                        {humanize(severity)}
                                    </option>
                                ))}
                            </select>
                        </div>

                        <div>
                            <label className="text-sm font-medium">
                                Detected at
                            </label>
                            <input
                                type="datetime-local"
                                value={form.data.detected_at}
                                onChange={(event) =>
                                    form.setData(
                                        "detected_at",
                                        event.target.value,
                                    )
                                }
                                className="mt-1 block w-full rounded-md border-slate-300"
                            />
                        </div>

                        <div>
                            <label className="text-sm font-medium">
                                Assigned to
                            </label>
                            <select
                                value={form.data.assigned_to}
                                onChange={(event) =>
                                    form.setData(
                                        "assigned_to",
                                        event.target.value,
                                    )
                                }
                                className="mt-1 block w-full rounded-md border-slate-300"
                            >
                                <option value="">Unassigned</option>
                                {assignees.map((user) => (
                                    <option key={user.id} value={user.id}>
                                        {user.name}
                                    </option>
                                ))}
                            </select>
                        </div>

                        <div>
                            <label className="text-sm font-medium">
                                Data categories
                            </label>
                            <input
                                value={form.data.data_categories_text}
                                onChange={(event) =>
                                    form.setData(
                                        "data_categories_text",
                                        event.target.value,
                                    )
                                }
                                placeholder="contact data, appointment metadata"
                                className="mt-1 block w-full rounded-md border-slate-300"
                            />
                        </div>

                        <div>
                            <label className="text-sm font-medium">
                                Systems affected
                            </label>
                            <input
                                value={form.data.systems_affected_text}
                                onChange={(event) =>
                                    form.setData(
                                        "systems_affected_text",
                                        event.target.value,
                                    )
                                }
                                placeholder="web application, storage"
                                className="mt-1 block w-full rounded-md border-slate-300"
                            />
                        </div>
                    </div>

                    <div className="mt-4">
                        <label className="text-sm font-medium">
                            Summary
                        </label>
                        <textarea
                            rows="5"
                            value={form.data.summary}
                            onChange={(event) =>
                                form.setData("summary", event.target.value)
                            }
                            className="mt-1 block w-full rounded-md border-slate-300"
                        />
                        <InputError
                            message={form.errors.summary}
                            className="mt-2"
                        />
                    </div>

                    <button
                        disabled={form.processing}
                        className="mt-4 rounded-md bg-rose-600 px-4 py-2 text-sm font-medium text-white"
                    >
                        Register breach
                    </button>
                </form>

                <form
                    onSubmit={submitFilters}
                    className="flex flex-col gap-3 rounded-xl border border-slate-200 bg-white p-5 shadow-sm md:flex-row"
                >
                    <input
                        value={filterForm.search}
                        onChange={(event) =>
                            setFilterForm({
                                ...filterForm,
                                search: event.target.value,
                            })
                        }
                        placeholder="Reference or title"
                        className="flex-1 rounded-md border-slate-300"
                    />

                    <select
                        value={filterForm.status}
                        onChange={(event) =>
                            setFilterForm({
                                ...filterForm,
                                status: event.target.value,
                            })
                        }
                        className="rounded-md border-slate-300"
                    >
                        <option value="">All statuses</option>
                        {statuses.map((status) => (
                            <option key={status} value={status}>
                                {humanize(status)}
                            </option>
                        ))}
                    </select>

                    <select
                        value={filterForm.severity}
                        onChange={(event) =>
                            setFilterForm({
                                ...filterForm,
                                severity: event.target.value,
                            })
                        }
                        className="rounded-md border-slate-300"
                    >
                        <option value="">All severities</option>
                        {severities.map((severity) => (
                            <option key={severity} value={severity}>
                                {humanize(severity)}
                            </option>
                        ))}
                    </select>

                    <button className="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white">
                        Apply filters
                    </button>
                </form>

                <div className="space-y-3">
                    {breaches.data.map((breach) => (
                        <Link
                            key={breach.uuid}
                            href={route("compliance.breaches.show", breach.uuid)}
                            className="block rounded-xl border border-slate-200 bg-white p-5 shadow-sm hover:border-rose-300"
                        >
                            <div className="flex flex-col gap-3 sm:flex-row sm:justify-between">
                                <div>
                                    <p className="font-semibold text-slate-900">
                                        {breach.reference} · {breach.title}
                                    </p>

                                    <p className="mt-2 text-sm text-slate-500">
                                        Assigned:{" "}
                                        {breach.assigned_to ?? "Unassigned"}
                                    </p>
                                </div>

                                <div className="flex gap-2">
                                    <span className="rounded-full bg-rose-50 px-3 py-1 text-xs font-medium text-rose-700">
                                        {humanize(breach.severity)}
                                    </span>

                                    <span className="rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-700">
                                        {humanize(breach.status)}
                                    </span>
                                </div>
                            </div>
                        </Link>
                    ))}
                </div>

                <Pagination links={breaches.links} />
            </div>
        </ComplianceLayout>
    );
}
