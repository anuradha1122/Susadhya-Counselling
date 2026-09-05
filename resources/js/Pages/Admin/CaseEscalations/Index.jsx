import Pagination from "@/Components/Pagination";
import AdminLayout from "@/Layouts/AdminLayout";
import { Head, router, useForm } from "@inertiajs/react";
import { ShieldAlert } from "lucide-react";

const label = (value) =>
    String(value ?? "")
        .replaceAll("_", " ")
        .replace(/\b\w/g, (letter) => letter.toUpperCase());

function EscalationCard({ item, options, assignees }) {
    const form = useForm({
        status: item.status,
        priority: item.priority,
        assigned_to: item.assigned_to ?? "",
        due_at: item.due_at
            ? item.due_at.slice(0, 16)
            : "",
        admin_note: item.admin_note ?? "",
        resolution_notes: item.resolution_notes ?? "",
    });

    const submit = (event) => {
        event.preventDefault();

        form.patch(
            route(
                "admin.case-escalations.update",
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
            <div className="flex flex-wrap items-center gap-2">
                <span className="font-semibold text-slate-900">
                    {item.case_reference}
                </span>

                <span className="rounded-full bg-slate-100 px-3 py-1 text-xs text-slate-700">
                    {label(item.reason_code)}
                </span>

                <span className="rounded-full bg-amber-50 px-3 py-1 text-xs text-amber-700">
                    {label(item.priority)}
                </span>
            </div>

            <div className="mt-5 grid gap-4 md:grid-cols-3">
                <select
                    value={form.data.status}
                    onChange={(event) =>
                        form.setData(
                            "status",
                            event.target.value,
                        )
                    }
                    className="rounded-lg border-slate-300"
                >
                    {options.statuses.map((status) => (
                        <option key={status} value={status}>
                            {label(status)}
                        </option>
                    ))}
                </select>

                <select
                    value={form.data.priority}
                    onChange={(event) =>
                        form.setData(
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

                <select
                    value={form.data.assigned_to}
                    onChange={(event) =>
                        form.setData(
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
                    value={form.data.due_at}
                    onChange={(event) =>
                        form.setData(
                            "due_at",
                            event.target.value,
                        )
                    }
                    className="rounded-lg border-slate-300"
                />

                <textarea
                    rows="2"
                    value={form.data.admin_note}
                    onChange={(event) =>
                        form.setData(
                            "admin_note",
                            event.target.value,
                        )
                    }
                    placeholder="Administrative metadata only"
                    className="rounded-lg border-slate-300 md:col-span-2"
                />

                <textarea
                    rows="2"
                    value={form.data.resolution_notes}
                    onChange={(event) =>
                        form.setData(
                            "resolution_notes",
                            event.target.value,
                        )
                    }
                    placeholder="Resolution notes"
                    className="rounded-lg border-slate-300 md:col-span-3"
                />
            </div>

            <div className="mt-4 flex justify-end">
                <button
                    type="submit"
                    disabled={form.processing}
                    className="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white"
                >
                    Save escalation
                </button>
            </div>
        </form>
    );
}

export default function Index({
    escalations,
    filters,
    assignees,
    options,
}) {
    const createForm = useForm({
        case_reference: "",
        reason_code: options.reasons[0] ?? "other",
        priority: "medium",
        assigned_to: "",
        due_at: "",
        admin_note: "",
    });

    const filterForm = useForm({
        search: filters.search ?? "",
        status: filters.status ?? "",
        priority: filters.priority ?? "",
    });

    return (
        <AdminLayout title="Case Escalations">
            <Head title="Case Escalations" />

            <div className="mx-auto max-w-7xl space-y-6">
                <section className="rounded-xl border border-rose-200 bg-rose-50 p-6">
                    <div className="flex gap-3">
                        <ShieldAlert className="mt-0.5 h-5 w-5 text-rose-600" />

                        <div>
                            <h2 className="font-semibold text-rose-900">
                                Operational metadata only
                            </h2>

                            <p className="mt-1 text-sm leading-6 text-rose-800">
                                Do not enter clinical notes, diagnoses,
                                intake answers, screening details,
                                presenting concerns, risk narratives,
                                or session content here.
                            </p>
                        </div>
                    </div>
                </section>

                <section className="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h3 className="text-lg font-semibold text-slate-900">
                        Create escalation
                    </h3>

                    <form
                        onSubmit={(event) => {
                            event.preventDefault();

                            createForm.post(
                                route(
                                    "admin.case-escalations.store",
                                ),
                                {
                                    preserveScroll: true,
                                    onSuccess: () =>
                                        createForm.reset(),
                                },
                            );
                        }}
                        className="mt-5 grid gap-4 md:grid-cols-2"
                    >
                        <input
                            value={createForm.data.case_reference}
                            onChange={(event) =>
                                createForm.setData(
                                    "case_reference",
                                    event.target.value,
                                )
                            }
                            placeholder="Case reference"
                            className="rounded-lg border-slate-300"
                        />

                        <select
                            value={createForm.data.reason_code}
                            onChange={(event) =>
                                createForm.setData(
                                    "reason_code",
                                    event.target.value,
                                )
                            }
                            className="rounded-lg border-slate-300"
                        >
                            {options.reasons.map((reason) => (
                                <option key={reason} value={reason}>
                                    {label(reason)}
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

                        <textarea
                            rows="3"
                            value={createForm.data.admin_note}
                            onChange={(event) =>
                                createForm.setData(
                                    "admin_note",
                                    event.target.value,
                                )
                            }
                            placeholder="Administrative routing note only"
                            className="rounded-lg border-slate-300"
                        />

                        <div className="md:col-span-2 flex justify-end">
                            <button
                                type="submit"
                                className="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white"
                            >
                                Create escalation
                            </button>
                        </div>
                    </form>
                </section>

                <section className="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                    <form
                        onSubmit={(event) => {
                            event.preventDefault();

                            router.get(
                                route(
                                    "admin.case-escalations.index",
                                ),
                                filterForm.data,
                                {
                                    preserveState: true,
                                    replace: true,
                                },
                            );
                        }}
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
                            placeholder="Case reference"
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

                        <div className="md:col-span-4 flex justify-end">
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
                    {escalations.data.map((item) => (
                        <EscalationCard
                            key={item.uuid}
                            item={item}
                            options={options}
                            assignees={assignees}
                        />
                    ))}

                    {escalations.data.length === 0 && (
                        <div className="rounded-xl border border-slate-200 bg-white p-8 text-center text-sm text-slate-500">
                            No escalation metadata found.
                        </div>
                    )}

                    <Pagination links={escalations.links} />
                </section>
            </div>
        </AdminLayout>
    );
}