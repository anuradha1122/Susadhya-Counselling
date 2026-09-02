import InputError from "@/Components/InputError";
import Pagination from "@/Components/Pagination";
import PrimaryButton from "@/Components/PrimaryButton";
import SecondaryButton from "@/Components/SecondaryButton";
import CounsellorLayout from "@/Layouts/CounsellorLayout";
import { Head, router, useForm } from "@inertiajs/react";
import { useState } from "react";

function formatValue(value) {
    if (value === null || value === undefined || value === "") {
        return "Not provided";
    }

    return String(value)
        .replaceAll("_", " ")
        .replace(/\b\w/g, (character) => character.toUpperCase());
}

function badgeClasses(value, type = "status") {
    const statusClasses = {
        draft: "bg-gray-100 text-gray-700",
        in_progress: "bg-blue-50 text-blue-700",
        completed: "bg-green-50 text-green-700",
        cancelled: "bg-red-50 text-red-700",
    };

    const riskClasses = {
        low: "bg-green-50 text-green-700",
        moderate: "bg-amber-50 text-amber-700",
        high: "bg-red-50 text-red-700",
        urgent: "bg-rose-100 text-rose-800",
    };

    return type === "risk"
        ? (riskClasses[value] ?? "bg-gray-100 text-gray-700")
        : (statusClasses[value] ?? "bg-gray-100 text-gray-700");
}

function Badge({ value, type = "status" }) {
    return (
        <span
            className={`rounded-full px-3 py-1 text-xs font-semibold ${badgeClasses(
                value,
                type,
            )}`}
        >
            {formatValue(value)}
        </span>
    );
}

function Detail({ label, value }) {
    return (
        <div className="rounded-lg bg-gray-50 p-4">
            <p className="text-xs font-medium uppercase tracking-wide text-gray-500">
                {label}
            </p>
            <p className="mt-1 whitespace-pre-line text-sm font-semibold text-gray-900">
                {formatValue(value)}
            </p>
        </div>
    );
}

function ReadyAppointmentCard({ appointment }) {
    const { post, processing, errors } = useForm();

    const submit = () => {
        post(route("counsellor.sessions.start", appointment.id), {
            preserveScroll: true,
        });
    };

    return (
        <div className="rounded-lg border border-indigo-100 bg-white p-5 shadow-sm">
            <div className="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h4 className="font-semibold text-gray-900">
                        {appointment.client.name}
                    </h4>
                    <p className="mt-1 text-sm text-gray-500">
                        {appointment.appointment_date} ·{" "}
                        {appointment.start_time} - {appointment.end_time} ·{" "}
                        {formatValue(appointment.mode)}
                    </p>
                    <p className="mt-1 text-sm text-gray-500">
                        Service: {appointment.service?.name ?? "Not selected"}
                    </p>
                </div>

                <PrimaryButton
                    type="button"
                    disabled={processing}
                    onClick={submit}
                >
                    Start session
                </PrimaryButton>
            </div>

            <InputError message={errors.appointment} className="mt-3" />
        </div>
    );
}

function SessionNoteForm({ session, options }) {
    const [isOpen, setIsOpen] = useState(false);

    const { data, setData, post, processing, errors, reset } = useForm({
        note_type: "progress",
        visibility: "private",
        content: "",
    });

    const submit = (event) => {
        event.preventDefault();

        post(route("counsellor.sessions.notes.store", session.id), {
            preserveScroll: true,
            onSuccess: () => {
                reset("content");
                setIsOpen(false);
            },
        });
    };

    if (!session.can_be_edited) {
        return null;
    }

    if (!isOpen) {
        return (
            <SecondaryButton type="button" onClick={() => setIsOpen(true)}>
                Add note
            </SecondaryButton>
        );
    }

    return (
        <form
            onSubmit={submit}
            className="mt-4 rounded-lg border border-indigo-100 bg-white p-4"
        >
            <div className="grid gap-4 md:grid-cols-2">
                <div>
                    <label
                        htmlFor={`note_type_${session.id}`}
                        className="text-sm font-medium text-gray-700"
                    >
                        Note type
                    </label>

                    <select
                        id={`note_type_${session.id}`}
                        value={data.note_type}
                        onChange={(event) =>
                            setData("note_type", event.target.value)
                        }
                        className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >
                        {options.noteTypes.map((type) => (
                            <option key={type.value} value={type.value}>
                                {type.label}
                            </option>
                        ))}
                    </select>

                    <InputError message={errors.note_type} className="mt-2" />
                </div>

                <div>
                    <label
                        htmlFor={`visibility_${session.id}`}
                        className="text-sm font-medium text-gray-700"
                    >
                        Visibility
                    </label>

                    <select
                        id={`visibility_${session.id}`}
                        value={data.visibility}
                        onChange={(event) =>
                            setData("visibility", event.target.value)
                        }
                        className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >
                        {options.visibilities.map((visibility) => (
                            <option
                                key={visibility.value}
                                value={visibility.value}
                            >
                                {visibility.label}
                            </option>
                        ))}
                    </select>

                    <InputError message={errors.visibility} className="mt-2" />
                </div>
            </div>

            <div className="mt-4">
                <label
                    htmlFor={`content_${session.id}`}
                    className="text-sm font-medium text-gray-700"
                >
                    Note
                </label>

                <textarea
                    id={`content_${session.id}`}
                    rows="4"
                    value={data.content}
                    onChange={(event) => setData("content", event.target.value)}
                    className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                />

                <InputError message={errors.content} className="mt-2" />
                <InputError message={errors.session} className="mt-2" />
            </div>

            <div className="mt-4 flex justify-end gap-3">
                <SecondaryButton
                    type="button"
                    onClick={() => {
                        reset("content");
                        setIsOpen(false);
                    }}
                >
                    Cancel
                </SecondaryButton>

                <PrimaryButton disabled={processing}>Save note</PrimaryButton>
            </div>
        </form>
    );
}

function CompleteSessionForm({ session, options }) {
    const [isOpen, setIsOpen] = useState(false);

    const { data, setData, patch, processing, errors } = useForm({
        presenting_summary: session.presenting_summary ?? "",
        intervention_summary: session.intervention_summary ?? "",
        outcome_summary: session.outcome_summary ?? "",
        client_visible_summary: session.client_visible_summary ?? "",
        homework: session.homework ?? "",
        private_notes: session.private_notes ?? "",
        clinical_risk_level: session.clinical_risk_level ?? "low",
        follow_up_recommended: Boolean(session.follow_up_recommended),
        follow_up_notes: session.follow_up_notes ?? "",
        next_session_recommended_at: session.next_session_recommended_at ?? "",
    });

    const submit = (event) => {
        event.preventDefault();

        patch(route("counsellor.sessions.complete", session.id), {
            preserveScroll: true,
            onSuccess: () => setIsOpen(false),
        });
    };

    if (!session.can_be_completed) {
        return null;
    }

    if (!isOpen) {
        return (
            <PrimaryButton type="button" onClick={() => setIsOpen(true)}>
                Complete session
            </PrimaryButton>
        );
    }

    return (
        <form
            onSubmit={submit}
            className="mt-4 rounded-lg border border-green-100 bg-white p-4"
        >
            <div className="grid gap-4 md:grid-cols-2">
                <div>
                    <label className="text-sm font-medium text-gray-700">
                        Presenting summary
                    </label>
                    <textarea
                        rows="3"
                        value={data.presenting_summary}
                        onChange={(event) =>
                            setData("presenting_summary", event.target.value)
                        }
                        className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    />
                    <InputError
                        message={errors.presenting_summary}
                        className="mt-2"
                    />
                </div>

                <div>
                    <label className="text-sm font-medium text-gray-700">
                        Intervention summary
                    </label>
                    <textarea
                        rows="3"
                        value={data.intervention_summary}
                        onChange={(event) =>
                            setData("intervention_summary", event.target.value)
                        }
                        className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    />
                    <InputError
                        message={errors.intervention_summary}
                        className="mt-2"
                    />
                </div>

                <div>
                    <label className="text-sm font-medium text-gray-700">
                        Outcome summary
                    </label>
                    <textarea
                        rows="3"
                        value={data.outcome_summary}
                        onChange={(event) =>
                            setData("outcome_summary", event.target.value)
                        }
                        className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    />
                    <InputError
                        message={errors.outcome_summary}
                        className="mt-2"
                    />
                </div>

                <div>
                    <label className="text-sm font-medium text-gray-700">
                        Client-visible summary
                    </label>
                    <textarea
                        rows="3"
                        value={data.client_visible_summary}
                        onChange={(event) =>
                            setData(
                                "client_visible_summary",
                                event.target.value,
                            )
                        }
                        className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    />
                    <InputError
                        message={errors.client_visible_summary}
                        className="mt-2"
                    />
                </div>

                <div>
                    <label className="text-sm font-medium text-gray-700">
                        Homework / next steps
                    </label>
                    <textarea
                        rows="3"
                        value={data.homework}
                        onChange={(event) =>
                            setData("homework", event.target.value)
                        }
                        className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    />
                    <InputError message={errors.homework} className="mt-2" />
                </div>

                <div>
                    <label className="text-sm font-medium text-gray-700">
                        Private notes
                    </label>
                    <textarea
                        rows="3"
                        value={data.private_notes}
                        onChange={(event) =>
                            setData("private_notes", event.target.value)
                        }
                        className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    />
                    <InputError
                        message={errors.private_notes}
                        className="mt-2"
                    />
                </div>

                <div>
                    <label className="text-sm font-medium text-gray-700">
                        Clinical risk level
                    </label>
                    <select
                        value={data.clinical_risk_level}
                        onChange={(event) =>
                            setData("clinical_risk_level", event.target.value)
                        }
                        className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >
                        {options.riskLevels.map((riskLevel) => (
                            <option
                                key={riskLevel.value}
                                value={riskLevel.value}
                            >
                                {riskLevel.label}
                            </option>
                        ))}
                    </select>
                    <InputError
                        message={errors.clinical_risk_level}
                        className="mt-2"
                    />
                </div>

                <div>
                    <label className="text-sm font-medium text-gray-700">
                        Next session recommended date
                    </label>
                    <input
                        type="date"
                        value={data.next_session_recommended_at}
                        onChange={(event) =>
                            setData(
                                "next_session_recommended_at",
                                event.target.value,
                            )
                        }
                        className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    />
                    <InputError
                        message={errors.next_session_recommended_at}
                        className="mt-2"
                    />
                </div>
            </div>

            <label className="mt-4 flex items-start gap-3 rounded-lg bg-gray-50 p-4">
                <input
                    type="checkbox"
                    checked={data.follow_up_recommended}
                    onChange={(event) =>
                        setData("follow_up_recommended", event.target.checked)
                    }
                    className="mt-1 rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                />

                <span className="text-sm text-gray-700">
                    Follow-up session is recommended.
                </span>
            </label>

            <div className="mt-4">
                <label className="text-sm font-medium text-gray-700">
                    Follow-up notes
                </label>
                <textarea
                    rows="3"
                    value={data.follow_up_notes}
                    onChange={(event) =>
                        setData("follow_up_notes", event.target.value)
                    }
                    className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                />
                <InputError message={errors.follow_up_notes} className="mt-2" />
                <InputError message={errors.session} className="mt-2" />
            </div>

            <div className="mt-4 flex justify-end gap-3">
                <SecondaryButton type="button" onClick={() => setIsOpen(false)}>
                    Cancel
                </SecondaryButton>

                <PrimaryButton disabled={processing}>
                    Save and complete
                </PrimaryButton>
            </div>
        </form>
    );
}

function SessionCard({ session, options }) {
    return (
        <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg">
            <div className="border-b border-gray-100 px-6 py-5">
                <div className="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                    <div>
                        <div className="flex flex-wrap items-center gap-3">
                            <h3 className="text-lg font-semibold text-gray-900">
                                {session.client.name}
                            </h3>
                            <Badge value={session.status} />
                            <Badge
                                value={session.clinical_risk_level}
                                type="risk"
                            />
                        </div>

                        <p className="mt-1 text-sm text-gray-500">
                            {session.appointment.appointment_date} ·{" "}
                            {session.appointment.start_time} -{" "}
                            {session.appointment.end_time} ·{" "}
                            {formatValue(session.mode)}
                        </p>
                    </div>

                    <div className="text-sm text-gray-500">
                        <p>Started: {formatValue(session.started_at)}</p>
                        <p>Completed: {formatValue(session.completed_at)}</p>
                    </div>
                </div>
            </div>

            <div className="grid gap-4 p-6 md:grid-cols-2">
                <Detail
                    label="Presenting summary"
                    value={session.presenting_summary}
                />
                <Detail
                    label="Intervention summary"
                    value={session.intervention_summary}
                />
                <Detail label="Outcome" value={session.outcome_summary} />
                <Detail
                    label="Client-visible summary"
                    value={session.client_visible_summary}
                />
                <Detail label="Homework" value={session.homework} />
                <Detail label="Private notes" value={session.private_notes} />
                <Detail
                    label="Follow-up recommended"
                    value={session.follow_up_recommended ? "Yes" : "No"}
                />
                <Detail
                    label="Next session date"
                    value={session.next_session_recommended_at}
                />
            </div>

            {session.notes.length > 0 && (
                <div className="border-t border-gray-100 px-6 py-5">
                    <h4 className="text-sm font-semibold uppercase tracking-wide text-gray-500">
                        Session Notes
                    </h4>

                    <div className="mt-4 space-y-3">
                        {session.notes.map((note) => (
                            <div
                                key={note.id}
                                className="rounded-lg border border-gray-100 bg-gray-50 p-4"
                            >
                                <div className="flex flex-wrap gap-2">
                                    <Badge value={note.note_type} />
                                    <Badge value={note.visibility} />
                                </div>

                                <p className="mt-3 whitespace-pre-line text-sm text-gray-700">
                                    {note.content}
                                </p>

                                <p className="mt-2 text-xs text-gray-500">
                                    {note.author.name} · {note.created_at}
                                </p>
                            </div>
                        ))}
                    </div>
                </div>
            )}

            <div className="border-t border-gray-100 bg-gray-50 px-6 py-5">
                <div className="flex flex-wrap justify-end gap-3">
                    <SessionNoteForm session={session} options={options} />
                    <CompleteSessionForm session={session} options={options} />
                </div>
            </div>
        </div>
    );
}

export default function Index({
    readyAppointments,
    sessions,
    filters,
    options,
}) {
    const { data, setData, get, processing } = useForm({
        status: filters.status ?? "",
        risk_level: filters.risk_level ?? "",
    });

    const submit = (event) => {
        event.preventDefault();

        get(route("counsellor.sessions.index"), {
            preserveScroll: true,
            preserveState: true,
            replace: true,
        });
    };

    const resetFilters = () => {
        router.get(
            route("counsellor.sessions.index"),
            {},
            {
                preserveScroll: true,
                replace: true,
            },
        );
    };

    return (
        <CounsellorLayout
            header={
                <div>
                    <h2 className="text-xl font-semibold leading-tight text-gray-800">
                        Session Delivery
                    </h2>
                    <p className="mt-1 text-sm text-gray-500">
                        Start sessions, add notes, and complete counselling
                        records.
                    </p>
                </div>
            }
        >
            <Head title="Session Delivery" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                    <div className="rounded-lg border border-indigo-100 bg-indigo-50 p-4">
                        <p className="text-sm text-indigo-900">
                            Start sessions from confirmed appointments, record
                            progress notes, and complete session outcomes.
                            Finally, appointment rectangles evolve into actual
                            service delivery.
                        </p>
                    </div>

                    <div className="overflow-hidden bg-white p-6 shadow-sm sm:rounded-lg">
                        <h3 className="text-lg font-semibold text-gray-900">
                            Ready to Start
                        </h3>
                        <p className="mt-1 text-sm text-gray-500">
                            Confirmed appointments scheduled for today or
                            earlier without a session record.
                        </p>

                        <div className="mt-5 space-y-4">
                            {readyAppointments.length === 0 ? (
                                <div className="rounded-lg border border-dashed border-gray-300 bg-gray-50 p-5 text-sm text-gray-600">
                                    No confirmed appointments are ready to
                                    start.
                                </div>
                            ) : (
                                readyAppointments.map((appointment) => (
                                    <ReadyAppointmentCard
                                        key={appointment.id}
                                        appointment={appointment}
                                    />
                                ))
                            )}
                        </div>
                    </div>

                    <form
                        onSubmit={submit}
                        className="overflow-hidden bg-white p-6 shadow-sm sm:rounded-lg"
                    >
                        <div className="grid gap-4 md:grid-cols-3">
                            <div>
                                <label className="text-sm font-medium text-gray-700">
                                    Status
                                </label>
                                <select
                                    value={data.status}
                                    onChange={(event) =>
                                        setData("status", event.target.value)
                                    }
                                    className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                >
                                    <option value="">Any status</option>
                                    {options.statuses.map((status) => (
                                        <option
                                            key={status.value}
                                            value={status.value}
                                        >
                                            {status.label}
                                        </option>
                                    ))}
                                </select>
                            </div>

                            <div>
                                <label className="text-sm font-medium text-gray-700">
                                    Risk level
                                </label>
                                <select
                                    value={data.risk_level}
                                    onChange={(event) =>
                                        setData(
                                            "risk_level",
                                            event.target.value,
                                        )
                                    }
                                    className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                >
                                    <option value="">Any risk</option>
                                    {options.riskLevels.map((riskLevel) => (
                                        <option
                                            key={riskLevel.value}
                                            value={riskLevel.value}
                                        >
                                            {riskLevel.label}
                                        </option>
                                    ))}
                                </select>
                            </div>

                            <div className="flex items-end justify-end gap-3">
                                <SecondaryButton
                                    type="button"
                                    onClick={resetFilters}
                                >
                                    Reset
                                </SecondaryButton>

                                <PrimaryButton disabled={processing}>
                                    Apply filters
                                </PrimaryButton>
                            </div>
                        </div>
                    </form>

                    {sessions.data.length === 0 ? (
                        <div className="overflow-hidden bg-white p-8 text-center shadow-sm sm:rounded-lg">
                            <h3 className="text-base font-semibold text-gray-900">
                                No session records found
                            </h3>
                            <p className="mt-2 text-sm text-gray-500">
                                Start a confirmed appointment to create a
                                session record.
                            </p>
                        </div>
                    ) : (
                        <div className="space-y-5">
                            {sessions.data.map((session) => (
                                <SessionCard
                                    key={session.id}
                                    session={session}
                                    options={options}
                                />
                            ))}
                        </div>
                    )}

                    <Pagination links={sessions.links} />
                </div>
            </div>
        </CounsellorLayout>
    );
}
