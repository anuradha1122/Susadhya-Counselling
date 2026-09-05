import InputError from "@/Components/InputError";
import ComplianceLayout from "@/Layouts/ComplianceLayout";
import { Head, Link, router, useForm } from "@inertiajs/react";

function humanize(value) {
    return value
        ? String(value)
              .replaceAll("_", " ")
              .replace(/\b\w/g, (character) => character.toUpperCase())
        : "Not provided";
}

function formatDate(value) {
    if (!value) return "Not provided";

    return new Intl.DateTimeFormat("en-LK", {
        dateStyle: "medium",
        timeStyle: "short",
    }).format(new Date(value));
}

export default function Show({ privacyRequest }) {
    const verifyForm = useForm({
        notes: "",
    });

    const decisionForm = useForm({
        decision: "approved",
        review_notes: "",
        legal_basis: "",
    });

    const completionForm = useForm({
        execution_notes: "",
        deletion_strategy:
            privacyRequest.type === "deletion"
                ? "retained_due_to_legal_obligation"
                : "not_applicable",
    });

    const verify = (event) => {
        event.preventDefault();

        verifyForm.post(
            route("compliance.privacy-requests.verify-identity", privacyRequest.uuid),
        );
    };

    const decide = (event) => {
        event.preventDefault();

        decisionForm.patch(
            route("compliance.privacy-requests.decision", privacyRequest.uuid),
        );
    };

    const complete = (event) => {
        event.preventDefault();

        completionForm.patch(
            route("compliance.privacy-requests.complete", privacyRequest.uuid),
        );
    };

    return (
        <ComplianceLayout title="Privacy Request Review">
            <Head title="Privacy Request Review" />

            <div className="mx-auto max-w-7xl space-y-6">
                <Link
                    href={route("compliance.privacy-requests.index")}
                    className="text-sm font-medium text-indigo-600"
                >
                    ← Back to privacy requests
                </Link>

                <div className="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div className="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                        <div>
                            <h2 className="text-xl font-semibold text-slate-900">
                                {humanize(privacyRequest.type)} request
                            </h2>

                            <p className="mt-1 text-sm text-slate-500">
                                {privacyRequest.uuid}
                            </p>

                            <p className="mt-4 text-sm text-slate-700">
                                Client:{" "}
                                <strong>
                                    {privacyRequest.subject?.name ??
                                        "Unknown client"}
                                </strong>
                            </p>

                            <p className="text-sm text-slate-500">
                                {privacyRequest.subject?.email}
                            </p>
                        </div>

                        <span className="rounded-full bg-indigo-50 px-3 py-1 text-sm font-medium text-indigo-700">
                            {humanize(privacyRequest.status)}
                        </span>
                    </div>

                    <div className="mt-6 rounded-lg bg-slate-50 p-4">
                        <p className="text-xs font-semibold uppercase tracking-wide text-slate-500">
                            Request details
                        </p>
                        <p className="mt-2 whitespace-pre-wrap text-sm leading-6 text-slate-700">
                            {privacyRequest.request_details}
                        </p>
                    </div>
                </div>

                {privacyRequest.status === "submitted" && (
                    <form
                        onSubmit={verify}
                        className="rounded-xl border border-slate-200 bg-white p-6 shadow-sm"
                    >
                        <h3 className="font-semibold text-slate-900">
                            Identity verification
                        </h3>

                        <p className="mt-1 text-sm text-slate-500">
                            Record how identity/ownership was verified. Do not
                            place passwords or secret authentication material here.
                        </p>

                        <textarea
                            rows="4"
                            value={verifyForm.data.notes}
                            onChange={(event) =>
                                verifyForm.setData("notes", event.target.value)
                            }
                            className="mt-4 block w-full rounded-md border-slate-300"
                            placeholder="Verification notes"
                        />

                        <InputError
                            message={verifyForm.errors.notes}
                            className="mt-2"
                        />

                        <button
                            disabled={verifyForm.processing}
                            className="mt-4 rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white disabled:opacity-50"
                        >
                            Confirm identity
                        </button>
                    </form>
                )}

                {privacyRequest.status === "identity_verified" && (
                    <div className="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                        <h3 className="font-semibold text-slate-900">
                            Begin compliance review
                        </h3>

                        <button
                            type="button"
                            onClick={() =>
                                router.post(
                                    route(
                                        "compliance.privacy-requests.begin-review",
                                        privacyRequest.uuid,
                                    ),
                                )
                            }
                            className="mt-4 rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white"
                        >
                            Begin review
                        </button>
                    </div>
                )}

                {["identity_verified", "under_review"].includes(
                    privacyRequest.status,
                ) && (
                    <form
                        onSubmit={decide}
                        className="rounded-xl border border-slate-200 bg-white p-6 shadow-sm"
                    >
                        <h3 className="font-semibold text-slate-900">
                            Compliance decision
                        </h3>

                        <div className="mt-4 grid gap-4 md:grid-cols-2">
                            <select
                                value={decisionForm.data.decision}
                                onChange={(event) =>
                                    decisionForm.setData(
                                        "decision",
                                        event.target.value,
                                    )
                                }
                                className="rounded-md border-slate-300"
                            >
                                <option value="approved">Approve</option>
                                <option value="rejected">Reject</option>
                            </select>

                            <input
                                value={decisionForm.data.legal_basis}
                                onChange={(event) =>
                                    decisionForm.setData(
                                        "legal_basis",
                                        event.target.value,
                                    )
                                }
                                placeholder="Legal / retention basis if applicable"
                                className="rounded-md border-slate-300"
                            />
                        </div>

                        <textarea
                            rows="5"
                            value={decisionForm.data.review_notes}
                            onChange={(event) =>
                                decisionForm.setData(
                                    "review_notes",
                                    event.target.value,
                                )
                            }
                            placeholder="Required review notes"
                            className="mt-4 block w-full rounded-md border-slate-300"
                        />

                        <InputError
                            message={decisionForm.errors.review_notes}
                            className="mt-2"
                        />

                        <button
                            disabled={decisionForm.processing}
                            className="mt-4 rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white"
                        >
                            Record decision
                        </button>
                    </form>
                )}

                {privacyRequest.status === "approved" &&
                    ["access", "export"].includes(privacyRequest.type) && (
                        <div className="rounded-xl border border-indigo-200 bg-indigo-50 p-6">
                            <h3 className="font-semibold text-indigo-950">
                                Prepare reviewed data export
                            </h3>

                            <p className="mt-2 text-sm text-indigo-800">
                                The automated JSON package uses a whitelist and
                                excludes raw clinical narratives and provider
                                secrets.
                            </p>

                            <button
                                type="button"
                                onClick={() =>
                                    router.post(
                                        route(
                                            "compliance.privacy-requests.prepare-export",
                                            privacyRequest.uuid,
                                        ),
                                    )
                                }
                                className="mt-4 rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white"
                            >
                                Prepare export
                            </button>
                        </div>
                    )}

                {privacyRequest.export_prepared_at && (
                    <div className="rounded-xl border border-emerald-200 bg-emerald-50 p-6">
                        <h3 className="font-semibold text-emerald-900">
                            Export prepared
                        </h3>

                        <p className="mt-2 break-all text-xs text-emerald-800">
                            SHA-256: {privacyRequest.export_checksum}
                        </p>

                        <a
                            href={route(
                                "compliance.privacy-requests.download-export",
                                privacyRequest.uuid,
                            )}
                            className="mt-4 inline-flex rounded-md bg-emerald-700 px-4 py-2 text-sm font-medium text-white"
                        >
                            Download export
                        </a>
                    </div>
                )}

                {["approved", "export_ready"].includes(privacyRequest.status) && (
                    <form
                        onSubmit={complete}
                        className="rounded-xl border border-slate-200 bg-white p-6 shadow-sm"
                    >
                        <h3 className="font-semibold text-slate-900">
                            Complete request
                        </h3>

                        <p className="mt-1 text-sm text-slate-500">
                            Deletion requests must document what was retained,
                            anonymised or manually removed. This form does not
                            perform a blind cascade delete.
                        </p>

                        <select
                            value={completionForm.data.deletion_strategy}
                            onChange={(event) =>
                                completionForm.setData(
                                    "deletion_strategy",
                                    event.target.value,
                                )
                            }
                            className="mt-4 block w-full rounded-md border-slate-300"
                        >
                            <option value="not_applicable">
                                Not applicable
                            </option>
                            <option value="retained_due_to_legal_obligation">
                                Retained due to legal obligation
                            </option>
                            <option value="manual_anonymisation">
                                Manual anonymisation
                            </option>
                            <option value="manual_deletion">
                                Manual deletion
                            </option>
                            <option value="partial_deletion">
                                Partial deletion
                            </option>
                            <option value="correction_completed">
                                Correction completed
                            </option>
                        </select>

                        <textarea
                            rows="5"
                            value={completionForm.data.execution_notes}
                            onChange={(event) =>
                                completionForm.setData(
                                    "execution_notes",
                                    event.target.value,
                                )
                            }
                            placeholder="Required execution notes"
                            className="mt-4 block w-full rounded-md border-slate-300"
                        />

                        <InputError
                            message={completionForm.errors.execution_notes}
                            className="mt-2"
                        />

                        <button
                            disabled={completionForm.processing}
                            className="mt-4 rounded-md bg-emerald-700 px-4 py-2 text-sm font-medium text-white"
                        >
                            Complete request
                        </button>
                    </form>
                )}

                <div className="rounded-xl border border-slate-200 bg-white shadow-sm">
                    <div className="border-b border-slate-200 px-6 py-4">
                        <h3 className="font-semibold text-slate-900">
                            Request timeline
                        </h3>
                    </div>

                    <div className="divide-y divide-slate-100">
                        {privacyRequest.events.map((event) => (
                            <div key={event.uuid} className="px-6 py-4">
                                <div className="flex justify-between gap-4">
                                    <div>
                                        <p className="font-medium text-slate-900">
                                            {humanize(event.event)}
                                        </p>

                                        <p className="mt-1 text-sm text-slate-500">
                                            {event.actor?.name ?? "System"}
                                        </p>

                                        {event.notes && (
                                            <p className="mt-2 whitespace-pre-wrap text-sm text-slate-700">
                                                {event.notes}
                                            </p>
                                        )}
                                    </div>

                                    <p className="text-xs text-slate-500">
                                        {formatDate(event.created_at)}
                                    </p>
                                </div>
                            </div>
                        ))}
                    </div>
                </div>
            </div>
        </ComplianceLayout>
    );
}
