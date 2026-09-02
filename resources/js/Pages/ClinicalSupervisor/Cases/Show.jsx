import {
    Head,
    Link,
} from "@inertiajs/react";
import {
    FileClock,
    LockKeyhole,
    ShieldAlert,
    ShieldCheck,
} from "lucide-react";

import AdminLayout from "@/Layouts/AdminLayout";

export default function Show({
    caseRecord,
}) {
    return (
        <AdminLayout>
            <Head title="Clinical Case Review" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                    <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <div className="p-6">
                            <Link
                                href={route(
                                    "clinical-supervisor.cases.index",
                                )}
                                className="text-sm font-medium text-indigo-600 hover:text-indigo-700"
                            >
                                ← Clinical supervision
                            </Link>

                            <div className="mt-3 flex flex-wrap items-start justify-between gap-4">
                                <div>
                                    <h1 className="text-2xl font-semibold text-slate-900">
                                        {caseRecord
                                            .client_profile
                                            ?.user
                                            ?.name ??
                                            "Client"}
                                    </h1>

                                    <p className="mt-1 text-sm text-slate-500">
                                        Counsellor:{" "}
                                        {caseRecord
                                            .counsellor_profile
                                            ?.user
                                            ?.name ??
                                            "Not provided"}
                                    </p>
                                </div>

                                <div className="flex flex-wrap gap-2 text-xs">
                                    <span className="rounded-full bg-slate-100 px-3 py-1 font-medium text-slate-700">
                                        {caseRecord.status.replaceAll(
                                            "_",
                                            " ",
                                        )}
                                    </span>

                                    <span className="rounded-full bg-indigo-50 px-3 py-1 font-medium text-indigo-700">
                                        {
                                            caseRecord.risk_level
                                        }{" "}
                                        risk
                                    </span>
                                </div>
                            </div>

                            <div className="mt-4 flex items-start gap-2 rounded-lg border border-indigo-100 bg-indigo-50 p-4 text-sm text-indigo-800">
                                <ShieldCheck className="mt-0.5 h-4 w-4 shrink-0" />

                                <p>
                                    Clinical
                                    supervisor review
                                    is read-only. This
                                    access has been
                                    recorded in the
                                    clinical access log.
                                </p>
                            </div>
                        </div>
                    </div>

                    {caseRecord.risk_flag && (
                        <div className="flex items-start gap-3 rounded-lg border border-red-100 bg-red-50 p-4 text-sm text-red-700">
                            <ShieldAlert className="mt-0.5 h-5 w-5 shrink-0" />

                            <div>
                                <p className="font-semibold">
                                    Active risk
                                    flag
                                </p>

                                <p className="mt-1 whitespace-pre-wrap">
                                    {caseRecord.risk_notes ||
                                        "No additional risk notes were provided."}
                                </p>
                            </div>
                        </div>
                    )}

                    <ReadSection title="Case summary">
                        {caseRecord.summary ||
                            "Not provided"}
                    </ReadSection>

                    <ReadSection title="Clinical formulation">
                        {caseRecord.formulation ||
                            "Not provided"}
                    </ReadSection>

                    <ReadSection title="Risk assessment">
                        <div className="grid gap-4 sm:grid-cols-3">
                            <InfoItem
                                label="Risk level"
                                value={
                                    caseRecord.risk_level
                                }
                            />

                            <InfoItem
                                label="Active flag"
                                value={
                                    caseRecord.risk_flag
                                        ? "Yes"
                                        : "No"
                                }
                            />

                            <InfoItem
                                label="Status"
                                value={caseRecord.status.replaceAll(
                                    "_",
                                    " ",
                                )}
                            />
                        </div>

                        <div className="mt-4">
                            <p className="text-xs font-semibold uppercase tracking-wide text-slate-500">
                                Risk notes
                            </p>

                            <p className="mt-1 whitespace-pre-wrap text-sm leading-6 text-slate-700">
                                {caseRecord.risk_notes ||
                                    "Not provided"}
                            </p>
                        </div>
                    </ReadSection>

                    <ReadSection title="Goals">
                        {caseRecord.goals
                            ?.length === 0 && (
                            <p className="text-sm text-slate-500">
                                No goals
                                recorded.
                            </p>
                        )}

                        <div className="space-y-3">
                            {caseRecord.goals?.map(
                                (goal) => (
                                    <div
                                        key={
                                            goal.id
                                        }
                                        className="rounded-lg border border-slate-200 p-4"
                                    >
                                        <div className="flex flex-wrap items-start justify-between gap-3">
                                            <p className="font-medium text-slate-800">
                                                {
                                                    goal.description
                                                }
                                            </p>

                                            <span className="rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-700">
                                                {goal.status.replaceAll(
                                                    "_",
                                                    " ",
                                                )}
                                            </span>
                                        </div>

                                        <div className="mt-3 grid gap-3 text-xs text-slate-500 sm:grid-cols-2">
                                            <div>
                                                Target:{" "}
                                                {goal.target_date ||
                                                    "Not provided"}
                                            </div>

                                            <div>
                                                Completed:{" "}
                                                {goal.completed_at ||
                                                    "Not provided"}
                                            </div>
                                        </div>

                                        {goal.outcome_note && (
                                            <p className="mt-3 whitespace-pre-wrap text-sm text-slate-600">
                                                {
                                                    goal.outcome_note
                                                }
                                            </p>
                                        )}
                                    </div>
                                ),
                            )}
                        </div>
                    </ReadSection>

                    <ReadSection title="Follow-up plan">
                        {caseRecord.follow_ups
                            ?.length === 0 && (
                            <p className="text-sm text-slate-500">
                                No follow-up
                                actions
                                recorded.
                            </p>
                        )}

                        <div className="space-y-3">
                            {caseRecord.follow_ups?.map(
                                (
                                    followUp,
                                ) => (
                                    <div
                                        key={
                                            followUp.id
                                        }
                                        className="rounded-lg border border-slate-200 p-4"
                                    >
                                        <div className="flex flex-wrap items-start justify-between gap-3">
                                            <p className="font-medium text-slate-800">
                                                {
                                                    followUp.description
                                                }
                                            </p>

                                            <span className="rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-700">
                                                {followUp.status.replaceAll(
                                                    "_",
                                                    " ",
                                                )}
                                            </span>
                                        </div>

                                        <div className="mt-3 grid gap-3 text-xs text-slate-500 sm:grid-cols-2">
                                            <div>
                                                Due:{" "}
                                                {followUp.due_at ||
                                                    "Not provided"}
                                            </div>

                                            <div>
                                                Completed:{" "}
                                                {followUp.completed_at ||
                                                    "Not provided"}
                                            </div>
                                        </div>
                                    </div>
                                ),
                            )}
                        </div>
                    </ReadSection>

                    <ReadSection title="Clinical notes">
                        {caseRecord
                            .clinical_notes
                            ?.length === 0 && (
                            <p className="text-sm text-slate-500">
                                No formal
                                clinical notes
                                recorded.
                            </p>
                        )}

                        <div className="space-y-5">
                            {caseRecord.clinical_notes?.map(
                                (note) => (
                                    <article
                                        key={
                                            note.id
                                        }
                                        className="rounded-lg border border-slate-200 p-5"
                                    >
                                        <div className="flex flex-wrap items-start justify-between gap-4">
                                            <div>
                                                <h3 className="font-semibold text-slate-900">
                                                    {note.title ||
                                                        `Clinical note #${note.id}`}
                                                </h3>

                                                <p className="mt-1 text-xs text-slate-500">
                                                    Version{" "}
                                                    {
                                                        note.version
                                                    }{" "}
                                                    ·{" "}
                                                    {
                                                        note.status
                                                    }{" "}
                                                    ·
                                                    Author:{" "}
                                                    {note
                                                        .author
                                                        ?.name ??
                                                        "Not provided"}
                                                </p>

                                                {note
                                                    .signer
                                                    ?.name && (
                                                    <p className="mt-1 text-xs text-slate-500">
                                                        Signed
                                                        by:{" "}
                                                        {
                                                            note
                                                                .signer
                                                                .name
                                                        }
                                                    </p>
                                                )}
                                            </div>

                                            {note.locked_at && (
                                                <span className="inline-flex items-center gap-1 rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-700">
                                                    <LockKeyhole className="h-3.5 w-3.5" />
                                                    Locked
                                                </span>
                                            )}
                                        </div>

                                        <div className="mt-5 space-y-4">
                                            <ClinicalReadField
                                                label="Clinical note"
                                                value={
                                                    note.note
                                                }
                                            />

                                            <ClinicalReadField
                                                label="Formulation"
                                                value={
                                                    note.formulation
                                                }
                                            />

                                            <ClinicalReadField
                                                label="Intervention"
                                                value={
                                                    note.intervention
                                                }
                                            />

                                            <ClinicalReadField
                                                label="Risk assessment"
                                                value={
                                                    note.risk_assessment
                                                }
                                            />

                                            <ClinicalReadField
                                                label="Plan"
                                                value={
                                                    note.plan
                                                }
                                            />

                                            <div className="grid gap-4 sm:grid-cols-3">
                                                <InfoItem
                                                    label="Risk level"
                                                    value={
                                                        note.risk_level
                                                    }
                                                />

                                                <InfoItem
                                                    label="Status"
                                                    value={
                                                        note.status
                                                    }
                                                />

                                                <InfoItem
                                                    label="Signed"
                                                    value={
                                                        note.signed_at ??
                                                        "Not provided"
                                                    }
                                                />
                                            </div>
                                        </div>

                                        <Link
                                            href={route(
                                                "clinical-supervisor.cases.notes.versions",
                                                [
                                                    caseRecord.id,
                                                    note.id,
                                                ],
                                            )}
                                            className="mt-5 inline-flex items-center gap-2 text-sm font-medium text-indigo-600 hover:text-indigo-700"
                                        >
                                            <FileClock className="h-4 w-4" />

                                            View
                                            version
                                            history
                                        </Link>
                                    </article>
                                ),
                            )}
                        </div>
                    </ReadSection>
                </div>
            </div>
        </AdminLayout>
    );
}

function ReadSection({
    title,
    children,
}) {
    return (
        <section className="overflow-hidden bg-white shadow-sm sm:rounded-lg">
            <div className="p-6">
                <h2 className="text-lg font-semibold text-slate-900">
                    {title}
                </h2>

                <div className="mt-4 text-sm text-slate-700">
                    {children}
                </div>
            </div>
        </section>
    );
}

function ClinicalReadField({
    label,
    value,
}) {
    return (
        <div>
            <p className="text-xs font-semibold uppercase tracking-wide text-slate-500">
                {label}
            </p>

            <p className="mt-1 whitespace-pre-wrap text-sm leading-6 text-slate-700">
                {value || "Not provided"}
            </p>
        </div>
    );
}

function InfoItem({
    label,
    value,
}) {
    return (
        <div className="rounded-md bg-slate-50 p-3">
            <p className="text-xs font-medium uppercase tracking-wide text-slate-500">
                {label}
            </p>

            <p className="mt-1 text-sm font-medium text-slate-800">
                {value || "Not provided"}
            </p>
        </div>
    );
}
