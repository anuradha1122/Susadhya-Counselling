import {
    Head,
    Link,
    router,
    useForm,
} from "@inertiajs/react";
import {
    LockKeyhole,
    ShieldAlert,
} from "lucide-react";

import CounsellorLayout from "@/Layouts/CounsellorLayout";
import InputError from "@/Components/InputError";
import PrimaryButton from "@/Components/PrimaryButton";
import SecondaryButton from "@/Components/SecondaryButton";

const Section = ({ title, description = null, children }) => {
    return (
        <section className="overflow-hidden bg-white shadow-sm sm:rounded-lg">
            <div className="p-6">
                <h2 className="text-lg font-semibold text-slate-900">
                    {title}
                </h2>

                {description && (
                    <p className="mt-1 text-sm text-slate-500">
                        {description}
                    </p>
                )}

                <div className="mt-4">
                    {children}
                </div>
            </div>
        </section>
    );
};

export default function Show({
    caseRecord,
    sessions,
    caseStatuses,
    goalStatuses,
    followUpStatuses,
    riskLevels,
}) {
    const isClosed =
        caseRecord.status === "closed";

    const caseForm = useForm({
        status:
            caseRecord.status === "closed"
                ? "open"
                : caseRecord.status,
        summary:
            caseRecord.summary ?? "",
        formulation:
            caseRecord.formulation ?? "",
        risk_level:
            caseRecord.risk_level ?? "low",
        risk_flag:
            Boolean(caseRecord.risk_flag),
        risk_notes:
            caseRecord.risk_notes ?? "",
    });

    const goalForm = useForm({
        description: "",
        target_date: "",
    });

    const followUpForm = useForm({
        description: "",
        due_at: "",
    });

    const noteForm = useForm({
        counselling_session_id: "",
        title: "",
        note: "",
        formulation: "",
        intervention: "",
        risk_assessment: "",
        plan: "",
        risk_level:
            caseRecord.risk_level ?? "low",
    });

    const updateCase = (event) => {
        event.preventDefault();

        caseForm.patch(
            route(
                "counsellor.cases.update",
                caseRecord.id,
            ),
            {
                preserveScroll: true,
            },
        );
    };

    const addGoal = (event) => {
        event.preventDefault();

        goalForm.post(
            route(
                "counsellor.cases.goals.store",
                caseRecord.id,
            ),
            {
                preserveScroll: true,
                onSuccess: () =>
                    goalForm.reset(),
            },
        );
    };

    const addFollowUp = (event) => {
        event.preventDefault();

        followUpForm.post(
            route(
                "counsellor.cases.follow-ups.store",
                caseRecord.id,
            ),
            {
                preserveScroll: true,
                onSuccess: () =>
                    followUpForm.reset(),
            },
        );
    };

    const addClinicalNote = (event) => {
        event.preventDefault();

        noteForm.post(
            route(
                "counsellor.cases.notes.store",
                caseRecord.id,
            ),
            {
                preserveScroll: true,
                onSuccess: () =>
                    noteForm.reset(),
            },
        );
    };

    const closeCase = () => {
        if (
            ! window.confirm(
                "Close this clinical case? The case will become read-only.",
            )
        ) {
            return;
        }

        router.patch(
            route(
                "counsellor.cases.close",
                caseRecord.id,
            ),
            {},
            {
                preserveScroll: true,
            },
        );
    };

    return (
        <CounsellorLayout>
            <Head title="Clinical Case" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                    <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <div className="flex flex-wrap items-start justify-between gap-4 p-6">
                            <div>
                                <Link
                                    href={route(
                                        "counsellor.cases.index",
                                    )}
                                    className="text-sm font-medium text-indigo-600 hover:text-indigo-700"
                                >
                                    ← Clinical cases
                                </Link>

                                <h1 className="mt-2 text-2xl font-semibold text-slate-900">
                                    {caseRecord
                                        .client_profile
                                        ?.user
                                        ?.name ??
                                        "Client"}
                                </h1>

                                <p className="mt-1 text-sm text-slate-500">
                                    Confidential case and
                                    clinical record
                                </p>

                                <div className="mt-3 flex flex-wrap gap-2 text-xs">
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

                            {caseRecord.risk_flag && (
                                <div className="flex items-center gap-2 rounded-lg bg-red-50 px-4 py-3 text-sm font-medium text-red-700">
                                    <ShieldAlert className="h-4 w-4" />

                                    Active risk flag
                                </div>
                            )}
                        </div>
                    </div>

                    {isClosed && (
                        <div className="flex items-start gap-3 rounded-lg border border-slate-200 bg-slate-50 p-4 text-sm text-slate-700">
                            <LockKeyhole className="mt-0.5 h-4 w-4 shrink-0" />

                            <div>
                                <p className="font-medium">
                                    Case closed
                                </p>

                                <p className="mt-1">
                                    Historical clinical
                                    records remain available,
                                    but no new changes are
                                    permitted.
                                </p>
                            </div>
                        </div>
                    )}

                    <Section
                        title="Case summary & risk"
                        description="Maintain the current case formulation and case-level risk assessment."
                    >
                        <form
                            onSubmit={updateCase}
                            className="grid gap-4 md:grid-cols-2"
                        >
                            <div>
                                <label className="text-sm font-medium text-slate-700">
                                    Status
                                </label>

                                <select
                                    disabled={isClosed}
                                    value={
                                        caseForm.data
                                            .status
                                    }
                                    onChange={(
                                        event,
                                    ) =>
                                        caseForm.setData(
                                            "status",
                                            event.target
                                                .value,
                                        )
                                    }
                                    className="mt-1 w-full rounded-md border-slate-300 shadow-sm disabled:bg-slate-100"
                                >
                                    {caseStatuses.map(
                                        (status) => (
                                            <option
                                                key={
                                                    status
                                                }
                                                value={
                                                    status
                                                }
                                            >
                                                {status.replaceAll(
                                                    "_",
                                                    " ",
                                                )}
                                            </option>
                                        ),
                                    )}
                                </select>

                                <InputError
                                    className="mt-2"
                                    message={
                                        caseForm.errors
                                            .status
                                    }
                                />
                            </div>

                            <div>
                                <label className="text-sm font-medium text-slate-700">
                                    Risk level
                                </label>

                                <select
                                    disabled={isClosed}
                                    value={
                                        caseForm.data
                                            .risk_level
                                    }
                                    onChange={(
                                        event,
                                    ) =>
                                        caseForm.setData(
                                            "risk_level",
                                            event.target
                                                .value,
                                        )
                                    }
                                    className="mt-1 w-full rounded-md border-slate-300 shadow-sm disabled:bg-slate-100"
                                >
                                    {riskLevels.map(
                                        (risk) => (
                                            <option
                                                key={
                                                    risk
                                                }
                                                value={
                                                    risk
                                                }
                                            >
                                                {risk}
                                            </option>
                                        ),
                                    )}
                                </select>

                                <InputError
                                    className="mt-2"
                                    message={
                                        caseForm.errors
                                            .risk_level
                                    }
                                />
                            </div>

                            <div className="md:col-span-2">
                                <label className="text-sm font-medium text-slate-700">
                                    Case summary
                                </label>

                                <textarea
                                    disabled={isClosed}
                                    rows="4"
                                    value={
                                        caseForm.data
                                            .summary
                                    }
                                    onChange={(
                                        event,
                                    ) =>
                                        caseForm.setData(
                                            "summary",
                                            event.target
                                                .value,
                                        )
                                    }
                                    className="mt-1 w-full rounded-md border-slate-300 shadow-sm disabled:bg-slate-100"
                                    placeholder="Summarise the current case."
                                />

                                <InputError
                                    className="mt-2"
                                    message={
                                        caseForm.errors
                                            .summary
                                    }
                                />
                            </div>

                            <div className="md:col-span-2">
                                <label className="text-sm font-medium text-slate-700">
                                    Clinical formulation
                                </label>

                                <textarea
                                    disabled={isClosed}
                                    rows="4"
                                    value={
                                        caseForm.data
                                            .formulation
                                    }
                                    onChange={(
                                        event,
                                    ) =>
                                        caseForm.setData(
                                            "formulation",
                                            event.target
                                                .value,
                                        )
                                    }
                                    className="mt-1 w-full rounded-md border-slate-300 shadow-sm disabled:bg-slate-100"
                                    placeholder="Current formulation"
                                />

                                <InputError
                                    className="mt-2"
                                    message={
                                        caseForm.errors
                                            .formulation
                                    }
                                />
                            </div>

                            <label className="flex items-center gap-2 text-sm text-slate-700">
                                <input
                                    disabled={isClosed}
                                    type="checkbox"
                                    checked={
                                        caseForm.data
                                            .risk_flag
                                    }
                                    onChange={(
                                        event,
                                    ) =>
                                        caseForm.setData(
                                            "risk_flag",
                                            event.target
                                                .checked,
                                        )
                                    }
                                    className="rounded border-slate-300"
                                />

                                Active risk flag
                            </label>

                            <div>
                                <label className="text-sm font-medium text-slate-700">
                                    Risk notes
                                </label>

                                <input
                                    disabled={isClosed}
                                    value={
                                        caseForm.data
                                            .risk_notes
                                    }
                                    onChange={(
                                        event,
                                    ) =>
                                        caseForm.setData(
                                            "risk_notes",
                                            event.target
                                                .value,
                                        )
                                    }
                                    className="mt-1 w-full rounded-md border-slate-300 shadow-sm disabled:bg-slate-100"
                                    placeholder="Risk monitoring notes"
                                />

                                <InputError
                                    className="mt-2"
                                    message={
                                        caseForm.errors
                                            .risk_notes
                                    }
                                />
                            </div>

                            {!isClosed && (
                                <div className="flex flex-wrap gap-2 md:col-span-2">
                                    <PrimaryButton
                                        disabled={
                                            caseForm.processing
                                        }
                                    >
                                        Save
                                    </PrimaryButton>

                                    <SecondaryButton
                                        type="button"
                                        onClick={
                                            closeCase
                                        }
                                    >
                                        Close case
                                    </SecondaryButton>
                                </div>
                            )}
                        </form>
                    </Section>

                    <Section
                        title="Goals"
                        description="Define and monitor therapeutic or care goals."
                    >
                        {!isClosed && (
                            <form
                                onSubmit={addGoal}
                                className="mb-6 grid gap-3 md:grid-cols-[1fr_180px_auto]"
                            >
                                <div>
                                    <input
                                        value={
                                            goalForm.data
                                                .description
                                        }
                                        onChange={(
                                            event,
                                        ) =>
                                            goalForm.setData(
                                                "description",
                                                event
                                                    .target
                                                    .value,
                                            )
                                        }
                                        className="w-full rounded-md border-slate-300 shadow-sm"
                                        placeholder="Goal description"
                                    />

                                    <InputError
                                        className="mt-2"
                                        message={
                                            goalForm
                                                .errors
                                                .description
                                        }
                                    />
                                </div>

                                <input
                                    type="date"
                                    value={
                                        goalForm.data
                                            .target_date
                                    }
                                    onChange={(
                                        event,
                                    ) =>
                                        goalForm.setData(
                                            "target_date",
                                            event.target
                                                .value,
                                        )
                                    }
                                    className="rounded-md border-slate-300 shadow-sm"
                                />

                                <PrimaryButton
                                    disabled={
                                        goalForm.processing
                                    }
                                >
                                    Add goal
                                </PrimaryButton>
                            </form>
                        )}

                        <div className="space-y-3">
                            {caseRecord.goals
                                ?.length === 0 && (
                                <p className="text-sm text-slate-500">
                                    No goals recorded.
                                </p>
                            )}

                            {caseRecord.goals?.map(
                                (goal) => (
                                    <GoalCard
                                        key={
                                            goal.id
                                        }
                                        goal={
                                            goal
                                        }
                                        caseId={
                                            caseRecord.id
                                        }
                                        statuses={
                                            goalStatuses
                                        }
                                        readOnly={
                                            isClosed
                                        }
                                    />
                                ),
                            )}
                        </div>
                    </Section>

                    <Section
                        title="Follow-up plan"
                        description="Track required clinical and administrative follow-up actions."
                    >
                        {!isClosed && (
                            <form
                                onSubmit={
                                    addFollowUp
                                }
                                className="mb-6 grid gap-3 md:grid-cols-[1fr_220px_auto]"
                            >
                                <div>
                                    <input
                                        value={
                                            followUpForm
                                                .data
                                                .description
                                        }
                                        onChange={(
                                            event,
                                        ) =>
                                            followUpForm.setData(
                                                "description",
                                                event
                                                    .target
                                                    .value,
                                            )
                                        }
                                        className="w-full rounded-md border-slate-300 shadow-sm"
                                        placeholder="Follow-up action"
                                    />

                                    <InputError
                                        className="mt-2"
                                        message={
                                            followUpForm
                                                .errors
                                                .description
                                        }
                                    />
                                </div>

                                <input
                                    type="datetime-local"
                                    value={
                                        followUpForm
                                            .data.due_at
                                    }
                                    onChange={(
                                        event,
                                    ) =>
                                        followUpForm.setData(
                                            "due_at",
                                            event.target
                                                .value,
                                        )
                                    }
                                    className="rounded-md border-slate-300 shadow-sm"
                                />

                                <PrimaryButton
                                    disabled={
                                        followUpForm.processing
                                    }
                                >
                                    Add follow-up
                                </PrimaryButton>
                            </form>
                        )}

                        <div className="space-y-3">
                            {caseRecord.follow_ups
                                ?.length === 0 && (
                                <p className="text-sm text-slate-500">
                                    No follow-up
                                    actions recorded.
                                </p>
                            )}

                            {caseRecord.follow_ups?.map(
                                (followUp) => (
                                    <FollowUpCard
                                        key={
                                            followUp.id
                                        }
                                        followUp={
                                            followUp
                                        }
                                        caseId={
                                            caseRecord.id
                                        }
                                        statuses={
                                            followUpStatuses
                                        }
                                        readOnly={
                                            isClosed
                                        }
                                    />
                                ),
                            )}
                        </div>
                    </Section>

                    <Section
                        title="Clinical notes"
                        description="Formal clinical documentation is versioned. Signing a note permanently locks it."
                    >
                        {!isClosed && (
                            <form
                                onSubmit={
                                    addClinicalNote
                                }
                                className="mb-8 space-y-4 rounded-lg border border-indigo-100 bg-indigo-50 p-4"
                            >
                                <div className="grid gap-4 md:grid-cols-2">
                                    <div>
                                        <label className="text-sm font-medium text-slate-700">
                                            Related
                                            session
                                        </label>

                                        <select
                                            value={
                                                noteForm
                                                    .data
                                                    .counselling_session_id
                                            }
                                            onChange={(
                                                event,
                                            ) =>
                                                noteForm.setData(
                                                    "counselling_session_id",
                                                    event
                                                        .target
                                                        .value,
                                                )
                                            }
                                            className="mt-1 w-full rounded-md border-slate-300 shadow-sm"
                                        >
                                            <option value="">
                                                No
                                                specific
                                                session
                                            </option>

                                            {sessions.map(
                                                (
                                                    session,
                                                ) => (
                                                    <option
                                                        key={
                                                            session.id
                                                        }
                                                        value={
                                                            session.id
                                                        }
                                                    >
                                                        Session
                                                        #
                                                        {
                                                            session.id
                                                        }{" "}
                                                        —{" "}
                                                        {
                                                            session.status
                                                        }
                                                    </option>
                                                ),
                                            )}
                                        </select>

                                        <InputError
                                            className="mt-2"
                                            message={
                                                noteForm
                                                    .errors
                                                    .counselling_session_id
                                            }
                                        />
                                    </div>

                                    <div>
                                        <label className="text-sm font-medium text-slate-700">
                                            Risk level
                                        </label>

                                        <select
                                            value={
                                                noteForm
                                                    .data
                                                    .risk_level
                                            }
                                            onChange={(
                                                event,
                                            ) =>
                                                noteForm.setData(
                                                    "risk_level",
                                                    event
                                                        .target
                                                        .value,
                                                )
                                            }
                                            className="mt-1 w-full rounded-md border-slate-300 shadow-sm"
                                        >
                                            {riskLevels.map(
                                                (
                                                    risk,
                                                ) => (
                                                    <option
                                                        key={
                                                            risk
                                                        }
                                                        value={
                                                            risk
                                                        }
                                                    >
                                                        {
                                                            risk
                                                        }
                                                    </option>
                                                ),
                                            )}
                                        </select>
                                    </div>
                                </div>

                                <div>
                                    <label className="text-sm font-medium text-slate-700">
                                        Title
                                    </label>

                                    <input
                                        value={
                                            noteForm.data
                                                .title
                                        }
                                        onChange={(
                                            event,
                                        ) =>
                                            noteForm.setData(
                                                "title",
                                                event.target
                                                    .value,
                                            )
                                        }
                                        className="mt-1 w-full rounded-md border-slate-300 shadow-sm"
                                        placeholder="Clinical note title"
                                    />
                                </div>

                                <ClinicalTextarea
                                    label="Clinical note"
                                    rows={6}
                                    value={
                                        noteForm.data.note
                                    }
                                    onChange={(
                                        value,
                                    ) =>
                                        noteForm.setData(
                                            "note",
                                            value,
                                        )
                                    }
                                    error={
                                        noteForm.errors.note
                                    }
                                />

                                <ClinicalTextarea
                                    label="Formulation"
                                    value={
                                        noteForm.data
                                            .formulation
                                    }
                                    onChange={(
                                        value,
                                    ) =>
                                        noteForm.setData(
                                            "formulation",
                                            value,
                                        )
                                    }
                                    error={
                                        noteForm.errors
                                            .formulation
                                    }
                                />

                                <ClinicalTextarea
                                    label="Intervention"
                                    value={
                                        noteForm.data
                                            .intervention
                                    }
                                    onChange={(
                                        value,
                                    ) =>
                                        noteForm.setData(
                                            "intervention",
                                            value,
                                        )
                                    }
                                    error={
                                        noteForm.errors
                                            .intervention
                                    }
                                />

                                <ClinicalTextarea
                                    label="Risk assessment"
                                    value={
                                        noteForm.data
                                            .risk_assessment
                                    }
                                    onChange={(
                                        value,
                                    ) =>
                                        noteForm.setData(
                                            "risk_assessment",
                                            value,
                                        )
                                    }
                                    error={
                                        noteForm.errors
                                            .risk_assessment
                                    }
                                />

                                <ClinicalTextarea
                                    label="Plan"
                                    value={
                                        noteForm.data.plan
                                    }
                                    onChange={(
                                        value,
                                    ) =>
                                        noteForm.setData(
                                            "plan",
                                            value,
                                        )
                                    }
                                    error={
                                        noteForm.errors.plan
                                    }
                                />

                                <PrimaryButton
                                    disabled={
                                        noteForm.processing
                                    }
                                >
                                    Save draft
                                </PrimaryButton>
                            </form>
                        )}

                        <div className="space-y-4">
                            {caseRecord
                                .clinical_notes
                                ?.length === 0 && (
                                <p className="text-sm text-slate-500">
                                    No formal clinical
                                    notes recorded.
                                </p>
                            )}

                            {caseRecord.clinical_notes?.map(
                                (note) => (
                                    <ClinicalNoteCard
                                        key={
                                            note.id
                                        }
                                        note={
                                            note
                                        }
                                        caseId={
                                            caseRecord.id
                                        }
                                        riskLevels={
                                            riskLevels
                                        }
                                        caseReadOnly={
                                            isClosed
                                        }
                                    />
                                ),
                            )}
                        </div>
                    </Section>
                </div>
            </div>
        </CounsellorLayout>
    );
}

function ClinicalTextarea({
    label,
    value,
    onChange,
    error = null,
    rows = 3,
}) {
    return (
        <div>
            <label className="text-sm font-medium text-slate-700">
                {label}
            </label>

            <textarea
                rows={rows}
                value={value}
                onChange={(event) =>
                    onChange(event.target.value)
                }
                className="mt-1 w-full rounded-md border-slate-300 shadow-sm"
            />

            <InputError
                className="mt-2"
                message={error}
            />
        </div>
    );
}

function GoalCard({
    goal,
    caseId,
    statuses,
    readOnly,
}) {
    const form = useForm({
        description:
            goal.description ?? "",
        target_date:
            goal.target_date ?? "",
        status:
            goal.status ?? "active",
        outcome_note:
            goal.outcome_note ?? "",
    });

    const submit = (event) => {
        event.preventDefault();

        form.patch(
            route(
                "counsellor.cases.goals.update",
                [
                    caseId,
                    goal.id,
                ],
            ),
            {
                preserveScroll: true,
            },
        );
    };

    return (
        <form
            onSubmit={submit}
            className="grid gap-3 rounded-lg border border-slate-200 p-4 md:grid-cols-4"
        >
            <div className="md:col-span-2">
                <label className="text-xs font-medium uppercase tracking-wide text-slate-500">
                    Goal
                </label>

                <input
                    disabled={readOnly}
                    value={
                        form.data.description
                    }
                    onChange={(event) =>
                        form.setData(
                            "description",
                            event.target.value,
                        )
                    }
                    className="mt-1 w-full rounded-md border-slate-300 shadow-sm disabled:bg-slate-100"
                />
            </div>

            <div>
                <label className="text-xs font-medium uppercase tracking-wide text-slate-500">
                    Target date
                </label>

                <input
                    disabled={readOnly}
                    type="date"
                    value={
                        form.data.target_date
                    }
                    onChange={(event) =>
                        form.setData(
                            "target_date",
                            event.target.value,
                        )
                    }
                    className="mt-1 w-full rounded-md border-slate-300 shadow-sm disabled:bg-slate-100"
                />
            </div>

            <div>
                <label className="text-xs font-medium uppercase tracking-wide text-slate-500">
                    Status
                </label>

                <select
                    disabled={readOnly}
                    value={
                        form.data.status
                    }
                    onChange={(event) =>
                        form.setData(
                            "status",
                            event.target.value,
                        )
                    }
                    className="mt-1 w-full rounded-md border-slate-300 shadow-sm disabled:bg-slate-100"
                >
                    {statuses.map(
                        (status) => (
                            <option
                                key={status}
                                value={status}
                            >
                                {status.replaceAll(
                                    "_",
                                    " ",
                                )}
                            </option>
                        ),
                    )}
                </select>
            </div>

            <div className="md:col-span-3">
                <label className="text-xs font-medium uppercase tracking-wide text-slate-500">
                    Outcome note
                </label>

                <input
                    disabled={readOnly}
                    value={
                        form.data.outcome_note
                    }
                    onChange={(event) =>
                        form.setData(
                            "outcome_note",
                            event.target.value,
                        )
                    }
                    className="mt-1 w-full rounded-md border-slate-300 shadow-sm disabled:bg-slate-100"
                    placeholder="Not provided"
                />
            </div>

            {!readOnly && (
                <div className="flex items-end">
                    <PrimaryButton
                        disabled={
                            form.processing
                        }
                    >
                        Save
                    </PrimaryButton>
                </div>
            )}
        </form>
    );
}

function FollowUpCard({
    followUp,
    caseId,
    statuses,
    readOnly,
}) {
    const form = useForm({
        description:
            followUp.description ?? "",
        due_at:
            followUp.due_at
                ? String(
                      followUp.due_at,
                  ).slice(0, 16)
                : "",
        status:
            followUp.status ?? "pending",
    });

    const submit = (event) => {
        event.preventDefault();

        form.patch(
            route(
                "counsellor.cases.follow-ups.update",
                [
                    caseId,
                    followUp.id,
                ],
            ),
            {
                preserveScroll: true,
            },
        );
    };

    return (
        <form
            onSubmit={submit}
            className="grid gap-3 rounded-lg border border-slate-200 p-4 md:grid-cols-4"
        >
            <div className="md:col-span-2">
                <label className="text-xs font-medium uppercase tracking-wide text-slate-500">
                    Follow-up action
                </label>

                <input
                    disabled={readOnly}
                    value={
                        form.data.description
                    }
                    onChange={(event) =>
                        form.setData(
                            "description",
                            event.target.value,
                        )
                    }
                    className="mt-1 w-full rounded-md border-slate-300 shadow-sm disabled:bg-slate-100"
                />
            </div>

            <div>
                <label className="text-xs font-medium uppercase tracking-wide text-slate-500">
                    Due
                </label>

                <input
                    disabled={readOnly}
                    type="datetime-local"
                    value={form.data.due_at}
                    onChange={(event) =>
                        form.setData(
                            "due_at",
                            event.target.value,
                        )
                    }
                    className="mt-1 w-full rounded-md border-slate-300 shadow-sm disabled:bg-slate-100"
                />
            </div>

            <div>
                <label className="text-xs font-medium uppercase tracking-wide text-slate-500">
                    Status
                </label>

                <select
                    disabled={readOnly}
                    value={
                        form.data.status
                    }
                    onChange={(event) =>
                        form.setData(
                            "status",
                            event.target.value,
                        )
                    }
                    className="mt-1 w-full rounded-md border-slate-300 shadow-sm disabled:bg-slate-100"
                >
                    {statuses.map(
                        (status) => (
                            <option
                                key={status}
                                value={status}
                            >
                                {status.replaceAll(
                                    "_",
                                    " ",
                                )}
                            </option>
                        ),
                    )}
                </select>
            </div>

            {!readOnly && (
                <div className="md:col-span-4">
                    <PrimaryButton
                        disabled={
                            form.processing
                        }
                    >
                        Save
                    </PrimaryButton>
                </div>
            )}
        </form>
    );
}

function ClinicalNoteCard({
    note,
    caseId,
    riskLevels,
    caseReadOnly,
}) {
    const isLocked =
        Boolean(note.locked_at) ||
        note.status === "signed";

    const form = useForm({
        title:
            note.title ?? "",
        note:
            note.note ?? "",
        formulation:
            note.formulation ?? "",
        intervention:
            note.intervention ?? "",
        risk_assessment:
            note.risk_assessment ?? "",
        plan:
            note.plan ?? "",
        risk_level:
            note.risk_level ?? "low",
        change_reason: "",
    });

    const submit = (event) => {
        event.preventDefault();

        form.patch(
            route(
                "counsellor.cases.notes.update",
                [
                    caseId,
                    note.id,
                ],
            ),
            {
                preserveScroll: true,
            },
        );
    };

    const signAndLock = () => {
        if (
            ! window.confirm(
                "Sign and permanently lock this clinical note? It cannot be edited afterwards.",
            )
        ) {
            return;
        }

        router.patch(
            route(
                "counsellor.cases.notes.sign",
                [
                    caseId,
                    note.id,
                ],
            ),
            {},
            {
                preserveScroll: true,
            },
        );
    };

    return (
        <article className="rounded-lg border border-slate-200 p-5">
            <div className="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h3 className="font-semibold text-slate-900">
                        {note.title ||
                            `Clinical note #${note.id}`}
                    </h3>

                    <p className="mt-1 text-xs text-slate-500">
                        Version{" "}
                        {note.version} ·{" "}
                        {note.status} · Author:{" "}
                        {note.author?.name ??
                            "Not provided"}
                    </p>

                    {note.signed_at && (
                        <p className="mt-1 text-xs text-slate-500">
                            Signed:{" "}
                            {note.signed_at}
                        </p>
                    )}
                </div>

                {isLocked && (
                    <span className="inline-flex items-center gap-1 rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-700">
                        <LockKeyhole className="h-3.5 w-3.5" />
                        Locked
                    </span>
                )}
            </div>

            <form
                onSubmit={submit}
                className="mt-5 space-y-4"
            >
                <div>
                    <label className="text-xs font-semibold uppercase tracking-wide text-slate-500">
                        Title
                    </label>

                    <input
                        disabled={
                            isLocked ||
                            caseReadOnly
                        }
                        value={
                            form.data.title
                        }
                        onChange={(event) =>
                            form.setData(
                                "title",
                                event.target.value,
                            )
                        }
                        className="mt-1 w-full rounded-md border-slate-300 shadow-sm disabled:bg-slate-100"
                    />
                </div>

                <EditableNoteField
                    label="Clinical note"
                    rows={5}
                    disabled={
                        isLocked ||
                        caseReadOnly
                    }
                    value={form.data.note}
                    onChange={(value) =>
                        form.setData(
                            "note",
                            value,
                        )
                    }
                />

                <EditableNoteField
                    label="Formulation"
                    disabled={
                        isLocked ||
                        caseReadOnly
                    }
                    value={
                        form.data.formulation
                    }
                    onChange={(value) =>
                        form.setData(
                            "formulation",
                            value,
                        )
                    }
                />

                <EditableNoteField
                    label="Intervention"
                    disabled={
                        isLocked ||
                        caseReadOnly
                    }
                    value={
                        form.data.intervention
                    }
                    onChange={(value) =>
                        form.setData(
                            "intervention",
                            value,
                        )
                    }
                />

                <EditableNoteField
                    label="Risk assessment"
                    disabled={
                        isLocked ||
                        caseReadOnly
                    }
                    value={
                        form.data
                            .risk_assessment
                    }
                    onChange={(value) =>
                        form.setData(
                            "risk_assessment",
                            value,
                        )
                    }
                />

                <EditableNoteField
                    label="Plan"
                    disabled={
                        isLocked ||
                        caseReadOnly
                    }
                    value={form.data.plan}
                    onChange={(value) =>
                        form.setData(
                            "plan",
                            value,
                        )
                    }
                />

                <div className="grid gap-3 md:grid-cols-2">
                    <div>
                        <label className="text-xs font-semibold uppercase tracking-wide text-slate-500">
                            Risk level
                        </label>

                        <select
                            disabled={
                                isLocked ||
                                caseReadOnly
                            }
                            value={
                                form.data
                                    .risk_level
                            }
                            onChange={(
                                event,
                            ) =>
                                form.setData(
                                    "risk_level",
                                    event.target
                                        .value,
                                )
                            }
                            className="mt-1 w-full rounded-md border-slate-300 shadow-sm disabled:bg-slate-100"
                        >
                            {riskLevels.map(
                                (risk) => (
                                    <option
                                        key={risk}
                                        value={risk}
                                    >
                                        {risk}
                                    </option>
                                ),
                            )}
                        </select>
                    </div>

                    <div>
                        <label className="text-xs font-semibold uppercase tracking-wide text-slate-500">
                            Change reason
                        </label>

                        <input
                            disabled={
                                isLocked ||
                                caseReadOnly
                            }
                            value={
                                form.data
                                    .change_reason
                            }
                            onChange={(
                                event,
                            ) =>
                                form.setData(
                                    "change_reason",
                                    event.target
                                        .value,
                                )
                            }
                            className="mt-1 w-full rounded-md border-slate-300 shadow-sm disabled:bg-slate-100"
                            placeholder="Recommended when editing"
                        />
                    </div>
                </div>

                <div className="flex flex-wrap gap-2">
                    {!isLocked &&
                        !caseReadOnly && (
                            <>
                                <PrimaryButton
                                    disabled={
                                        form.processing
                                    }
                                >
                                    Save new
                                    version
                                </PrimaryButton>

                                <SecondaryButton
                                    type="button"
                                    onClick={
                                        signAndLock
                                    }
                                >
                                    Sign & lock
                                </SecondaryButton>
                            </>
                        )}

                    <Link
                        href={route(
                            "counsellor.cases.notes.versions",
                            [
                                caseId,
                                note.id,
                            ],
                        )}
                        className="inline-flex items-center rounded-md border border-slate-300 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-slate-700 shadow-sm transition hover:bg-slate-50"
                    >
                        Version history
                    </Link>
                </div>
            </form>
        </article>
    );
}

function EditableNoteField({
    label,
    value,
    onChange,
    disabled,
    rows = 3,
}) {
    return (
        <div>
            <label className="text-xs font-semibold uppercase tracking-wide text-slate-500">
                {label}
            </label>

            <textarea
                disabled={disabled}
                rows={rows}
                value={value}
                onChange={(event) =>
                    onChange(event.target.value)
                }
                className="mt-1 w-full rounded-md border-slate-300 shadow-sm disabled:bg-slate-100"
            />
        </div>
    );
}
