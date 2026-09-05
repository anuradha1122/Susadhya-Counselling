import InputError from "@/Components/InputError";
import ComplianceLayout from "@/Layouts/ComplianceLayout";
import { Head, Link, useForm } from "@inertiajs/react";

function humanize(value) {
    return String(value ?? "")
        .replaceAll("_", " ")
        .replace(/\b\w/g, (character) => character.toUpperCase());
}

export default function Show({
    breach,
    statuses,
    severities,
    assignees,
}) {
    const form = useForm({
        title: breach.title,
        severity: breach.severity,
        status: breach.status,
        assigned_to: breach.assigned_to?.id ?? "",
        detected_at: breach.detected_at ?? "",
        occurred_at: breach.occurred_at ?? "",
        reported_to_authority_at:
            breach.reported_to_authority_at ?? "",
        affected_subject_count:
            breach.affected_subject_count ?? "",
        data_categories_text:
            (breach.data_categories ?? []).join(", "),
        systems_affected_text:
            (breach.systems_affected ?? []).join(", "),
        summary: breach.summary ?? "",
        containment_actions: breach.containment_actions ?? "",
        notification_decision: breach.notification_decision ?? "",
        authority_reference: breach.authority_reference ?? "",
    });

    const submit = (event) => {
        event.preventDefault();

        form.transform((data) => ({
            title: data.title,
            severity: data.severity,
            status: data.status,
            assigned_to: data.assigned_to || null,
            detected_at: data.detected_at,
            occurred_at: data.occurred_at || null,
            reported_to_authority_at:
                data.reported_to_authority_at || null,
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
            containment_actions: data.containment_actions || null,
            notification_decision: data.notification_decision || null,
            authority_reference: data.authority_reference || null,
        }));

        form.patch(route("compliance.breaches.update", breach.uuid));
    };

    return (
        <ComplianceLayout title="Breach Review">
            <Head title={`Breach ${breach.reference}`} />

            <div className="mx-auto max-w-5xl space-y-6">
                <Link
                    href={route("compliance.breaches.index")}
                    className="text-sm font-medium text-indigo-600"
                >
                    ← Back to breach register
                </Link>

                <div className="rounded-xl border border-rose-200 bg-rose-50 p-5">
                    <p className="text-xs font-semibold uppercase tracking-wide text-rose-700">
                        {breach.reference}
                    </p>

                    <h2 className="mt-1 text-xl font-semibold text-rose-950">
                        {breach.title}
                    </h2>

                    <p className="mt-2 text-sm text-rose-800">
                        Reported by: {breach.reported_by?.name ?? "Unknown"}
                    </p>
                </div>

                <form
                    onSubmit={submit}
                    className="rounded-xl border border-slate-200 bg-white p-6 shadow-sm"
                >
                    <div className="grid gap-4 md:grid-cols-2">
                        <div>
                            <label className="text-sm font-medium">Title</label>
                            <input
                                value={form.data.title}
                                onChange={(event) =>
                                    form.setData("title", event.target.value)
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
                            <label className="text-sm font-medium">Status</label>
                            <select
                                value={form.data.status}
                                onChange={(event) =>
                                    form.setData("status", event.target.value)
                                }
                                className="mt-1 block w-full rounded-md border-slate-300"
                            >
                                {statuses.map((status) => (
                                    <option key={status} value={status}>
                                        {humanize(status)}
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
                                Occurred at
                            </label>
                            <input
                                type="datetime-local"
                                value={form.data.occurred_at}
                                onChange={(event) =>
                                    form.setData(
                                        "occurred_at",
                                        event.target.value,
                                    )
                                }
                                className="mt-1 block w-full rounded-md border-slate-300"
                            />
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
                                className="mt-1 block w-full rounded-md border-slate-300"
                            />
                        </div>

                        <div>
                            <label className="text-sm font-medium">
                                Authority reference
                            </label>
                            <input
                                value={form.data.authority_reference}
                                onChange={(event) =>
                                    form.setData(
                                        "authority_reference",
                                        event.target.value,
                                    )
                                }
                                className="mt-1 block w-full rounded-md border-slate-300"
                            />
                        </div>

                        <div>
                            <label className="text-sm font-medium">
                                Reported to authority at
                            </label>
                            <input
                                type="datetime-local"
                                value={form.data.reported_to_authority_at}
                                onChange={(event) =>
                                    form.setData(
                                        "reported_to_authority_at",
                                        event.target.value,
                                    )
                                }
                                className="mt-1 block w-full rounded-md border-slate-300"
                            />
                        </div>
                    </div>

                    <div className="mt-4">
                        <label className="text-sm font-medium">Summary</label>
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

                    <div className="mt-4">
                        <label className="text-sm font-medium">
                            Containment actions
                        </label>
                        <textarea
                            rows="5"
                            value={form.data.containment_actions}
                            onChange={(event) =>
                                form.setData(
                                    "containment_actions",
                                    event.target.value,
                                )
                            }
                            className="mt-1 block w-full rounded-md border-slate-300"
                        />
                    </div>

                    <div className="mt-4">
                        <label className="text-sm font-medium">
                            Notification decision
                        </label>
                        <textarea
                            rows="5"
                            value={form.data.notification_decision}
                            onChange={(event) =>
                                form.setData(
                                    "notification_decision",
                                    event.target.value,
                                )
                            }
                            className="mt-1 block w-full rounded-md border-slate-300"
                        />
                    </div>

                    <button
                        disabled={form.processing}
                        className="mt-5 rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white disabled:opacity-50"
                    >
                        Save breach review
                    </button>
                </form>
            </div>
        </ComplianceLayout>
    );
}
