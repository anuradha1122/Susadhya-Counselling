import InputError from "@/Components/InputError";
import Pagination from "@/Components/Pagination";
import PrimaryButton from "@/Components/PrimaryButton";
import SecondaryButton from "@/Components/SecondaryButton";
import AdminLayout from "@/Layouts/AdminLayout";
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

function AdminReviewForm({ session, options }) {
    const [isOpen, setIsOpen] = useState(false);

    const { data, setData, patch, processing, errors, reset } = useForm({
        clinical_risk_level: session.clinical_risk_level ?? "low",
        follow_up_recommended: Boolean(session.follow_up_recommended),
        follow_up_notes: session.follow_up_notes ?? "",
        admin_notes: session.admin_notes ?? "",
    });

    const submit = (event) => {
        event.preventDefault();

        patch(route("admin.sessions.review", session.id), {
            preserveScroll: true,
            onSuccess: () => setIsOpen(false),
        });
    };

    if (!isOpen) {
        return (
            <PrimaryButton type="button" onClick={() => setIsOpen(true)}>
                Review session
            </PrimaryButton>
        );
    }

    return (
        <form
            onSubmit={submit}
            className="rounded-lg border border-indigo-100 bg-white p-4"
        >
            <div className="grid gap-4 md:grid-cols-2">
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

                <label className="flex items-start gap-3 rounded-lg bg-gray-50 p-4">
                    <input
                        type="checkbox"
                        checked={data.follow_up_recommended}
                        onChange={(event) =>
                            setData(
                                "follow_up_recommended",
                                event.target.checked,
                            )
                        }
                        className="mt-1 rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                    />

                    <span className="text-sm text-gray-700">
                        Follow-up recommended.
                    </span>
                </label>
            </div>

            <div className="mt-4 grid gap-4 md:grid-cols-2">
                <div>
                    <label className="text-sm font-medium text-gray-700">
                        Follow-up notes
                    </label>

                    <textarea
                        rows="4"
                        value={data.follow_up_notes}
                        onChange={(event) =>
                            setData("follow_up_notes", event.target.value)
                        }
                        className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    />

                    <InputError
                        message={errors.follow_up_notes}
                        className="mt-2"
                    />
                </div>

                <div>
                    <label className="text-sm font-medium text-gray-700">
                        Admin notes
                    </label>

                    <textarea
                        rows="4"
                        value={data.admin_notes}
                        onChange={(event) =>
                            setData("admin_notes", event.target.value)
                        }
                        className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    />

                    <InputError message={errors.admin_notes} className="mt-2" />
                </div>
            </div>

            <div className="mt-4 flex justify-end gap-3">
                <SecondaryButton
                    type="button"
                    onClick={() => {
                        reset(
                            "clinical_risk_level",
                            "follow_up_recommended",
                            "follow_up_notes",
                            "admin_notes",
                        );
                        setIsOpen(false);
                    }}
                >
                    Cancel
                </SecondaryButton>

                <PrimaryButton disabled={processing}>Save review</PrimaryButton>
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
                            Counsellor: {session.counsellor.name}
                        </p>

                        <p className="mt-1 text-sm text-gray-500">
                            {session.appointment.appointment_date} ·{" "}
                            {session.appointment.start_time} -{" "}
                            {session.appointment.end_time}
                        </p>
                    </div>

                    <div className="rounded-lg bg-indigo-50 px-4 py-3 text-sm text-indigo-800">
                        <p>
                            Follow-up:{" "}
                            {session.follow_up_recommended ? "Yes" : "No"}
                        </p>
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
                <Detail
                    label="Outcome summary"
                    value={session.outcome_summary}
                />
                <Detail
                    label="Client-visible summary"
                    value={session.client_visible_summary}
                />
                <Detail label="Homework" value={session.homework} />
                <Detail label="Private notes" value={session.private_notes} />
                <Detail
                    label="Follow-up notes"
                    value={session.follow_up_notes}
                />
                <Detail label="Admin notes" value={session.admin_notes} />
            </div>

            {session.notes.length > 0 && (
                <div className="border-t border-gray-100 px-6 py-5">
                    <h4 className="text-sm font-semibold uppercase tracking-wide text-gray-500">
                        Notes
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
                <AdminReviewForm session={session} options={options} />
            </div>
        </div>
    );
}

export default function Index({ sessions, filters, options }) {
    const { data, setData, get, processing } = useForm({
        search: filters.search ?? "",
        status: filters.status ?? "",
        risk_level: filters.risk_level ?? "",
        follow_up: filters.follow_up ?? "",
    });

    const submit = (event) => {
        event.preventDefault();

        get(route("admin.sessions.index"), {
            preserveScroll: true,
            preserveState: true,
            replace: true,
        });
    };

    const resetFilters = () => {
        router.get(
            route("admin.sessions.index"),
            {},
            {
                preserveScroll: true,
                replace: true,
            },
        );
    };

    return (
        <AdminLayout
            header={
                <div>
                    <h2 className="text-xl font-semibold leading-tight text-gray-800">
                        Session Oversight
                    </h2>
                    <p className="mt-1 text-sm text-gray-500">
                        Review counselling session delivery, risks, follow-ups,
                        and notes.
                    </p>
                </div>
            }
        >
            <Head title="Session Oversight" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                    <div className="rounded-lg border border-indigo-100 bg-indigo-50 p-4">
                        <p className="text-sm text-indigo-900">
                            Admin can oversee completed and in-progress session
                            records, review clinical risk level, and track
                            follow-up needs without guessing from appointment
                            status crumbs.
                        </p>
                    </div>

                    <form
                        onSubmit={submit}
                        className="overflow-hidden bg-white p-6 shadow-sm sm:rounded-lg"
                    >
                        <div className="grid gap-4 lg:grid-cols-5">
                            <div className="lg:col-span-2">
                                <label className="text-sm font-medium text-gray-700">
                                    Search
                                </label>
                                <input
                                    type="search"
                                    value={data.search}
                                    onChange={(event) =>
                                        setData("search", event.target.value)
                                    }
                                    placeholder="Client or counsellor name, email, phone"
                                    className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                />
                            </div>

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
                                    Risk
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

                            <div>
                                <label className="text-sm font-medium text-gray-700">
                                    Follow-up
                                </label>
                                <select
                                    value={data.follow_up}
                                    onChange={(event) =>
                                        setData("follow_up", event.target.value)
                                    }
                                    className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                >
                                    <option value="">Any</option>
                                    {options.followUpOptions.map((option) => (
                                        <option
                                            key={option.value}
                                            value={option.value}
                                        >
                                            {option.label}
                                        </option>
                                    ))}
                                </select>
                            </div>
                        </div>

                        <div className="mt-5 flex justify-end gap-3">
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
                    </form>

                    {sessions.data.length === 0 ? (
                        <div className="overflow-hidden bg-white p-8 text-center shadow-sm sm:rounded-lg">
                            <h3 className="text-base font-semibold text-gray-900">
                                No session records found
                            </h3>
                            <p className="mt-2 text-sm text-gray-500">
                                Try changing filters or wait for counsellors to
                                complete sessions.
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
        </AdminLayout>
    );
}
