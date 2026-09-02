import InputError from "@/Components/InputError";
import Pagination from "@/Components/Pagination";
import PrimaryButton from "@/Components/PrimaryButton";
import SecondaryButton from "@/Components/SecondaryButton";
import AdminLayout from "@/Layouts/AdminLayout";
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

function statusClasses(status) {
    const classes = {
        submitted: "bg-amber-50 text-amber-700",
        reviewed: "bg-green-50 text-green-700",
        requires_follow_up: "bg-blue-50 text-blue-700",
    };

    return classes[status] ?? "bg-gray-100 text-gray-700";
}

function riskClasses(riskLevel) {
    const classes = {
        low: "bg-green-50 text-green-700",
        moderate: "bg-amber-50 text-amber-700",
        high: "bg-red-50 text-red-700",
        urgent: "bg-rose-100 text-rose-800",
    };

    return classes[riskLevel] ?? "bg-gray-100 text-gray-700";
}

function Badge({ value, type = "status" }) {
    const classes = type === "risk" ? riskClasses(value) : statusClasses(value);

    return (
        <span className={`rounded-full px-3 py-1 text-xs font-semibold ${classes}`}>
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

function ReviewForm({ intake, options, routePrefix }) {
    const [isOpen, setIsOpen] = useState(false);

    const { data, setData, patch, processing, errors, reset } = useForm({
        status: intake.status,
        risk_level: intake.risk_level,
        reviewer_notes: intake.reviewer_notes ?? "",
    });

    const submit = (event) => {
        event.preventDefault();

        patch(route(`${routePrefix}.intakes.review`, intake.id), {
            preserveScroll: true,
            onSuccess: () => {
                setIsOpen(false);
            },
        });
    };

    const cancel = () => {
        reset("status", "risk_level", "reviewer_notes");
        setIsOpen(false);
    };

    if (!isOpen) {
        return (
            <PrimaryButton type="button" onClick={() => setIsOpen(true)}>
                Review intake
            </PrimaryButton>
        );
    }

    return (
        <form onSubmit={submit} className="rounded-lg border border-indigo-100 bg-white p-4">
            <div className="grid gap-4 md:grid-cols-2">
                <div>
                    <label
                        htmlFor={`status_${intake.id}`}
                        className="text-sm font-medium text-gray-700"
                    >
                        Review status
                    </label>

                    <select
                        id={`status_${intake.id}`}
                        value={data.status}
                        onChange={(event) => setData("status", event.target.value)}
                        className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >
                        {options.statuses.map((status) => (
                            <option key={status.value} value={status.value}>
                                {status.label}
                            </option>
                        ))}
                    </select>

                    <InputError message={errors.status} className="mt-2" />
                </div>

                <div>
                    <label
                        htmlFor={`risk_${intake.id}`}
                        className="text-sm font-medium text-gray-700"
                    >
                        Risk level
                    </label>

                    <select
                        id={`risk_${intake.id}`}
                        value={data.risk_level}
                        onChange={(event) =>
                            setData("risk_level", event.target.value)
                        }
                        className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >
                        {options.riskLevels.map((riskLevel) => (
                            <option key={riskLevel.value} value={riskLevel.value}>
                                {riskLevel.label}
                            </option>
                        ))}
                    </select>

                    <InputError message={errors.risk_level} className="mt-2" />
                </div>
            </div>

            <div className="mt-4">
                <label
                    htmlFor={`reviewer_notes_${intake.id}`}
                    className="text-sm font-medium text-gray-700"
                >
                    Reviewer notes
                </label>

                <textarea
                    id={`reviewer_notes_${intake.id}`}
                    rows="4"
                    value={data.reviewer_notes}
                    onChange={(event) =>
                        setData("reviewer_notes", event.target.value)
                    }
                    placeholder="Clinical/admin notes or follow-up instructions."
                    className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                />

                <InputError message={errors.reviewer_notes} className="mt-2" />
            </div>

            <div className="mt-4 flex flex-wrap justify-end gap-3">
                <SecondaryButton type="button" onClick={cancel}>
                    Cancel
                </SecondaryButton>

                <PrimaryButton disabled={processing}>Save review</PrimaryButton>
            </div>
        </form>
    );
}

function IntakeCard({ intake, options, routePrefix }) {
    return (
        <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg">
            <div className="border-b border-gray-100 px-6 py-5">
                <div className="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                    <div>
                        <div className="flex flex-wrap items-center gap-3">
                            <h3 className="text-lg font-semibold text-gray-900">
                                {intake.client.name}
                            </h3>

                            <Badge value={intake.status} />
                            <Badge value={intake.risk_level} type="risk" />
                        </div>

                        <p className="mt-1 text-sm text-gray-500">
                            {intake.client.email} · {formatValue(intake.client.phone)}
                        </p>

                        <p className="mt-1 text-sm text-gray-500">
                            Submitted: {formatValue(intake.submitted_at)}
                        </p>
                    </div>

                    <div className="rounded-lg bg-indigo-50 px-4 py-3 text-sm text-indigo-800">
                        <p className="font-semibold">
                            Preferred mode: {formatValue(intake.preferred_session_mode)}
                        </p>
                        <p>Client status: {formatValue(intake.client.status)}</p>
                    </div>
                </div>
            </div>

            <div className="grid gap-4 p-6 md:grid-cols-2">
                <Detail label="Main concern" value={intake.presenting_concerns} />
                <Detail label="Current symptoms" value={intake.current_symptoms} />
                <Detail label="Counselling goals" value={intake.counselling_goals} />
                <Detail label="Medication notes" value={intake.medication_notes} />
                <Detail
                    label="Previous counselling"
                    value={intake.previous_counselling ? "Yes" : "No"}
                />
                <Detail
                    label="Previous counselling notes"
                    value={intake.previous_counselling_notes}
                />
            </div>

            <div className="border-t border-gray-100 px-6 py-5">
                <h4 className="text-sm font-semibold uppercase tracking-wide text-gray-500">
                    Emergency Contact
                </h4>

                <div className="mt-3 grid gap-4 md:grid-cols-3">
                    <Detail label="Name" value={intake.emergency_contact_name} />
                    <Detail label="Phone" value={intake.emergency_contact_phone} />
                    <Detail
                        label="Relationship"
                        value={intake.emergency_contact_relationship}
                    />
                </div>
            </div>

            <div className="border-t border-gray-100 px-6 py-5">
                <h4 className="text-sm font-semibold uppercase tracking-wide text-gray-500">
                    Screening Answers
                </h4>

                <div className="mt-4 space-y-3">
                    {intake.screening_answers.map((answer) => (
                        <div
                            key={answer.id}
                            className="rounded-lg border border-gray-100 p-4"
                        >
                            <div className="flex flex-col gap-2 lg:flex-row lg:items-start lg:justify-between">
                                <p className="text-sm font-medium text-gray-900">
                                    {answer.question_text}
                                </p>

                                <span className="rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-700">
                                    Score {answer.answer_score} ·{" "}
                                    {formatValue(answer.answer_value)}
                                </span>
                            </div>
                        </div>
                    ))}
                </div>
            </div>

            <div className="border-t border-gray-100 px-6 py-5">
                <h4 className="text-sm font-semibold uppercase tracking-wide text-gray-500">
                    Consent
                </h4>

                <div className="mt-3 grid gap-3 text-sm text-gray-700 md:grid-cols-2">
                    <p>Terms: {intake.consent_terms_accepted ? "Accepted" : "No"}</p>
                    <p>
                        Privacy:{" "}
                        {intake.consent_privacy_accepted ? "Accepted" : "No"}
                    </p>
                    <p>
                        Telehealth:{" "}
                        {intake.consent_telehealth_accepted ? "Accepted" : "No"}
                    </p>
                    <p>
                        Data processing:{" "}
                        {intake.consent_data_processing_accepted
                            ? "Accepted"
                            : "No"}
                    </p>
                    <p className="md:col-span-2">
                        Consent given at: {formatValue(intake.consent_given_at)}
                    </p>
                </div>
            </div>

            {(intake.risk_notes || intake.reviewer_notes || intake.reviewer.name) && (
                <div className="border-t border-gray-100 px-6 py-5">
                    <div className="grid gap-4 md:grid-cols-3">
                        <Detail label="Risk notes" value={intake.risk_notes} />
                        <Detail label="Reviewer notes" value={intake.reviewer_notes} />
                        <Detail label="Reviewed by" value={intake.reviewer.name} />
                    </div>
                </div>
            )}

            <div className="border-t border-gray-100 bg-gray-50 px-6 py-5">
                <ReviewForm
                    intake={intake}
                    options={options}
                    routePrefix={routePrefix}
                />
            </div>
        </div>
    );
}

export default function ReviewIndex({
    intakes,
    filters,
    options,
    routePrefix,
    pageTitle,
    description,
}) {
    const Layout = routePrefix === "admin" ? AdminLayout : CounsellorLayout;

    const { data, setData, get, processing } = useForm({
        search: filters.search ?? "",
        status: filters.status ?? "",
        risk_level: filters.risk_level ?? "",
    });

    const submit = (event) => {
        event.preventDefault();

        get(route(`${routePrefix}.intakes.index`), {
            preserveScroll: true,
            preserveState: true,
            replace: true,
        });
    };

    const resetFilters = () => {
        router.get(
            route(`${routePrefix}.intakes.index`),
            {},
            {
                preserveScroll: true,
                replace: true,
            },
        );
    };

    return (
        <Layout
            header={
                <div>
                    <h2 className="text-xl font-semibold leading-tight text-gray-800">
                        {pageTitle}
                    </h2>
                    <p className="mt-1 text-sm text-gray-500">{description}</p>
                </div>
            }
        >
            <Head title={pageTitle} />

            <div className="py-12">
                <div className="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                    <div className="rounded-lg border border-indigo-100 bg-indigo-50 p-4">
                        <p className="text-sm text-indigo-900">
                            Intake reviews show submitted client concerns,
                            consent records, screening scores, risk flags, and
                            follow-up decisions. Finally, a form with actual
                            consequences instead of just decorative bureaucracy.
                        </p>
                    </div>

                    <form
                        onSubmit={submit}
                        className="overflow-hidden bg-white p-6 shadow-sm sm:rounded-lg"
                    >
                        <div className="grid gap-4 lg:grid-cols-4">
                            <div className="lg:col-span-2">
                                <label
                                    htmlFor="search"
                                    className="text-sm font-medium text-gray-700"
                                >
                                    Search
                                </label>

                                <input
                                    id="search"
                                    type="search"
                                    value={data.search}
                                    onChange={(event) =>
                                        setData("search", event.target.value)
                                    }
                                    placeholder="Client name, email, or phone"
                                    className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                />
                            </div>

                            <div>
                                <label
                                    htmlFor="status"
                                    className="text-sm font-medium text-gray-700"
                                >
                                    Status
                                </label>

                                <select
                                    id="status"
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
                                <label
                                    htmlFor="risk_level"
                                    className="text-sm font-medium text-gray-700"
                                >
                                    Risk level
                                </label>

                                <select
                                    id="risk_level"
                                    value={data.risk_level}
                                    onChange={(event) =>
                                        setData("risk_level", event.target.value)
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
                        </div>

                        <div className="mt-5 flex justify-end gap-3">
                            <SecondaryButton type="button" onClick={resetFilters}>
                                Reset
                            </SecondaryButton>

                            <PrimaryButton disabled={processing}>
                                Apply filters
                            </PrimaryButton>
                        </div>
                    </form>

                    <div className="overflow-hidden bg-white px-6 py-4 shadow-sm sm:rounded-lg">
                        <p className="text-sm text-gray-600">
                            Showing{" "}
                            <span className="font-semibold text-gray-900">
                                {intakes.total}
                            </span>{" "}
                            intake record{intakes.total === 1 ? "" : "s"}.
                        </p>
                    </div>

                    {intakes.data.length === 0 ? (
                        <div className="overflow-hidden bg-white p-8 text-center shadow-sm sm:rounded-lg">
                            <h3 className="text-base font-semibold text-gray-900">
                                No intake records found
                            </h3>
                            <p className="mt-2 text-sm text-gray-500">
                                Try changing filters or wait for clients to submit
                                intake forms.
                            </p>
                        </div>
                    ) : (
                        <div className="space-y-5">
                            {intakes.data.map((intake) => (
                                <IntakeCard
                                    key={intake.id}
                                    intake={intake}
                                    options={options}
                                    routePrefix={routePrefix}
                                />
                            ))}
                        </div>
                    )}

                    <Pagination links={intakes.links} />
                </div>
            </div>
        </Layout>
    );
}
