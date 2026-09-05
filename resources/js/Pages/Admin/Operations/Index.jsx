import Pagination from "@/Components/Pagination";
import AdminLayout from "@/Layouts/AdminLayout";
import { Head, router, useForm } from "@inertiajs/react";
import {
    AlertTriangle,
    BellRing,
    CalendarDays,
    CheckCircle2,
    Clock3,
    Siren,
} from "lucide-react";

const label = (value) =>
    String(value ?? "")
        .replaceAll("_", " ")
        .replace(/\b\w/g, (letter) => letter.toUpperCase());

function MetricCard({ title, value, icon: Icon }) {
    return (
        <div className="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <div className="flex items-center justify-between gap-4">
                <div>
                    <p className="text-sm text-slate-500">{title}</p>
                    <p className="mt-2 text-2xl font-semibold text-slate-900">
                        {value}
                    </p>
                </div>

                <div className="rounded-xl bg-slate-100 p-3 text-slate-600">
                    <Icon className="h-5 w-5" />
                </div>
            </div>
        </div>
    );
}

function ExceptionCard({ item, options, assignees }) {
    const form = useForm({
        status: item.status,
        priority: item.priority,
        assigned_to: item.assigned_to ?? "",
        due_at: item.due_at
            ? item.due_at.slice(0, 16)
            : "",
        resolution_notes: item.resolution_notes ?? "",
    });

    const submit = (event) => {
        event.preventDefault();

        form.patch(
            route(
                "admin.operations.exceptions.update",
                item.uuid,
            ),
            {
                preserveScroll: true,
            },
        );
    };

    return (
        <form
            onSubmit={submit}
            className="rounded-xl border border-slate-200 bg-white p-5 shadow-sm"
        >
            <div className="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <div className="flex flex-wrap gap-2">
                        <span className="rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-700">
                            {label(item.type)}
                        </span>

                        <span className="rounded-full bg-amber-50 px-3 py-1 text-xs font-medium text-amber-700">
                            {label(item.priority)}
                        </span>
                    </div>

                    <h3 className="mt-3 font-semibold text-slate-900">
                        {item.title}
                    </h3>

                    {item.description && (
                        <p className="mt-2 text-sm leading-6 text-slate-600">
                            {item.description}
                        </p>
                    )}

                    {item.source_reference && (
                        <p className="mt-2 text-xs text-slate-500">
                            Reference: {item.source_reference}
                        </p>
                    )}
                </div>
            </div>

            <div className="mt-5 grid gap-4 md:grid-cols-3">
                <div>
                    <label className="text-sm font-medium text-slate-700">
                        Status
                    </label>
                    <select
                        value={form.data.status}
                        onChange={(event) =>
                            form.setData(
                                "status",
                                event.target.value,
                            )
                        }
                        className="mt-1 block w-full rounded-lg border-slate-300"
                    >
                        {options.statuses.map((status) => (
                            <option key={status} value={status}>
                                {label(status)}
                            </option>
                        ))}
                    </select>
                </div>

                <div>
                    <label className="text-sm font-medium text-slate-700">
                        Priority
                    </label>
                    <select
                        value={form.data.priority}
                        onChange={(event) =>
                            form.setData(
                                "priority",
                                event.target.value,
                            )
                        }
                        className="mt-1 block w-full rounded-lg border-slate-300"
                    >
                        {options.priorities.map((priority) => (
                            <option key={priority} value={priority}>
                                {label(priority)}
                            </option>
                        ))}
                    </select>
                </div>

                <div>
                    <label className="text-sm font-medium text-slate-700">
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
                        className="mt-1 block w-full rounded-lg border-slate-300"
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
                    <label className="text-sm font-medium text-slate-700">
                        Due at
                    </label>
                    <input
                        type="datetime-local"
                        value={form.data.due_at}
                        onChange={(event) =>
                            form.setData(
                                "due_at",
                                event.target.value,
                            )
                        }
                        className="mt-1 block w-full rounded-lg border-slate-300"
                    />
                </div>

                <div className="md:col-span-2">
                    <label className="text-sm font-medium text-slate-700">
                        Resolution notes
                    </label>
                    <textarea
                        rows="2"
                        value={form.data.resolution_notes}
                        onChange={(event) =>
                            form.setData(
                                "resolution_notes",
                                event.target.value,
                            )
                        }
                        className="mt-1 block w-full rounded-lg border-slate-300"
                    />
                </div>
            </div>

            <div className="mt-4 flex justify-end">
                <button
                    type="submit"
                    disabled={form.processing}
                    className="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 disabled:opacity-50"
                >
                    Save exception
                </button>
            </div>
        </form>
    );
}

export default function Index({
    metrics,
    exceptions,
    filters,
    assignees,
    options,
}) {
    const createForm = useForm({
        type: options.types[0] ?? "other",
        priority: "medium",
        source_type: "",
        source_reference: "",
        title: "",
        description: "",
        assigned_to: "",
        due_at: "",
    });

    const filterForm = useForm({
        search: filters.search ?? "",
        status: filters.status ?? "",
        priority: filters.priority ?? "",
    });

    const applyFilters = (event) => {
        event.preventDefault();

        router.get(
            route("admin.operations.index"),
            filterForm.data,
            {
                preserveState: true,
                replace: true,
            },
        );
    };

    const createException = (event) => {
        event.preventDefault();

        createForm.post(
            route("admin.operations.exceptions.store"),
            {
                preserveScroll: true,
                onSuccess: () => createForm.reset(),
            },
        );
    };

    return (
        <AdminLayout title="Admin Operations">
            <Head title="Admin Operations" />

            <div className="mx-auto max-w-7xl space-y-6">
                <section className="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                    <p className="text-sm font-medium text-indigo-600">
                        M16 · Admin Operations
                    </p>

                    <h2 className="mt-1 text-2xl font-semibold text-slate-900">
                        Operational command centre
                    </h2>

                    <p className="mt-2 max-w-3xl text-sm leading-6 text-slate-600">
                        Review scheduling and platform exceptions
                        without directly editing the database.
                        Clinical information is intentionally excluded
                        from this workspace.
                    </p>
                </section>

                <section className="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                    <MetricCard
                        title="Today's appointments"
                        value={metrics.todayAppointments}
                        icon={CalendarDays}
                    />
                    <MetricCard
                        title="Pending appointments"
                        value={metrics.pendingAppointments}
                        icon={Clock3}
                    />
                    <MetricCard
                        title="Open exceptions"
                        value={metrics.openExceptions}
                        icon={AlertTriangle}
                    />
                    <MetricCard
                        title="Overdue exceptions"
                        value={metrics.overdueExceptions}
                        icon={Siren}
                    />
                    <MetricCard
                        title="Open case escalations"
                        value={metrics.openEscalations}
                        icon={CheckCircle2}
                    />
                    <MetricCard
                        title="Notification failures · 24h"
                        value={metrics.recentNotificationFailures}
                        icon={BellRing}
                    />
                </section>

                <section className="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h3 className="text-lg font-semibold text-slate-900">
                        Record operational exception
                    </h3>

                    <form
                        onSubmit={createException}
                        className="mt-5 grid gap-4 md:grid-cols-2"
                    >
                        <select
                            value={createForm.data.type}
                            onChange={(event) =>
                                createForm.setData(
                                    "type",
                                    event.target.value,
                                )
                            }
                            className="rounded-lg border-slate-300"
                        >
                            {options.types.map((type) => (
                                <option key={type} value={type}>
                                    {label(type)}
                                </option>
                            ))}
                        </select>

                        <select
                            value={createForm.data.priority}
                            onChange={(event) =>
                                createForm.setData(
                                    "priority",
                                    event.target.value,
                                )
                            }
                            className="rounded-lg border-slate-300"
                        >
                            {options.priorities.map((priority) => (
                                <option key={priority} value={priority}>
                                    {label(priority)}
                                </option>
                            ))}
                        </select>

                        <input
                            value={createForm.data.title}
                            onChange={(event) =>
                                createForm.setData(
                                    "title",
                                    event.target.value,
                                )
                            }
                            placeholder="Exception title"
                            className="rounded-lg border-slate-300 md:col-span-2"
                        />

                        <select
                            value={createForm.data.source_type}
                            onChange={(event) =>
                                createForm.setData(
                                    "source_type",
                                    event.target.value,
                                )
                            }
                            className="rounded-lg border-slate-300"
                        >
                            <option value="">No source type</option>
                            <option value="appointment">Appointment</option>
                            <option value="availability">Availability</option>
                            <option value="notification">Notification</option>
                            <option value="account">Account</option>
                            <option value="content">Content</option>
                            <option value="other">Other</option>
                        </select>

                        <input
                            value={createForm.data.source_reference}
                            onChange={(event) =>
                                createForm.setData(
                                    "source_reference",
                                    event.target.value,
                                )
                            }
                            placeholder="UUID / external reference"
                            className="rounded-lg border-slate-300"
                        />

                        <textarea
                            rows="3"
                            value={createForm.data.description}
                            onChange={(event) =>
                                createForm.setData(
                                    "description",
                                    event.target.value,
                                )
                            }
                            placeholder="Non-clinical operational description"
                            className="rounded-lg border-slate-300 md:col-span-2"
                        />

                        <select
                            value={createForm.data.assigned_to}
                            onChange={(event) =>
                                createForm.setData(
                                    "assigned_to",
                                    event.target.value,
                                )
                            }
                            className="rounded-lg border-slate-300"
                        >
                            <option value="">Unassigned</option>
                            {assignees.map((user) => (
                                <option key={user.id} value={user.id}>
                                    {user.name}
                                </option>
                            ))}
                        </select>

                        <input
                            type="datetime-local"
                            value={createForm.data.due_at}
                            onChange={(event) =>
                                createForm.setData(
                                    "due_at",
                                    event.target.value,
                                )
                            }
                            className="rounded-lg border-slate-300"
                        />

                        <div className="md:col-span-2 flex justify-end">
                            <button
                                type="submit"
                                disabled={createForm.processing}
                                className="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 disabled:opacity-50"
                            >
                                Create exception
                            </button>
                        </div>
                    </form>
                </section>

                <section className="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                    <form
                        onSubmit={applyFilters}
                        className="grid gap-4 md:grid-cols-4"
                    >
                        <input
                            value={filterForm.data.search}
                            onChange={(event) =>
                                filterForm.setData(
                                    "search",
                                    event.target.value,
                                )
                            }
                            placeholder="Search exceptions"
                            className="rounded-lg border-slate-300 md:col-span-2"
                        />

                        <select
                            value={filterForm.data.status}
                            onChange={(event) =>
                                filterForm.setData(
                                    "status",
                                    event.target.value,
                                )
                            }
                            className="rounded-lg border-slate-300"
                        >
                            <option value="">All statuses</option>
                            {options.statuses.map((status) => (
                                <option key={status} value={status}>
                                    {label(status)}
                                </option>
                            ))}
                        </select>

                        <select
                            value={filterForm.data.priority}
                            onChange={(event) =>
                                filterForm.setData(
                                    "priority",
                                    event.target.value,
                                )
                            }
                            className="rounded-lg border-slate-300"
                        >
                            <option value="">All priorities</option>
                            {options.priorities.map((priority) => (
                                <option key={priority} value={priority}>
                                    {label(priority)}
                                </option>
                            ))}
                        </select>

                        <div className="md:col-span-4 flex justify-end gap-2">
                            <button
                                type="button"
                                onClick={() =>
                                    router.get(
                                        route("admin.operations.index"),
                                    )
                                }
                                className="rounded-lg border border-slate-300 px-4 py-2 text-sm"
                            >
                                Reset
                            </button>

                            <button
                                type="submit"
                                className="rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white"
                            >
                                Apply filters
                            </button>
                        </div>
                    </form>
                </section>

                <section className="space-y-4">
                    {exceptions.data.length === 0 ? (
                        <div className="rounded-xl border border-slate-200 bg-white p-8 text-center text-sm text-slate-500 shadow-sm">
                            No operational exceptions found.
                        </div>
                    ) : (
                        exceptions.data.map((item) => (
                            <ExceptionCard
                                key={item.uuid}
                                item={item}
                                options={options}
                                assignees={assignees}
                            />
                        ))
                    )}

                    <Pagination links={exceptions.links} />
                </section>
            </div>
        </AdminLayout>
    );
}