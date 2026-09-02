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

function statusClasses(status) {
    const classes = {
        pending: "bg-amber-50 text-amber-700",
        confirmed: "bg-green-50 text-green-700",
        rescheduled: "bg-blue-50 text-blue-700",
        completed: "bg-gray-100 text-gray-700",
        cancelled: "bg-red-50 text-red-700",
        no_show: "bg-rose-50 text-rose-700",
    };

    return classes[status] ?? "bg-gray-100 text-gray-700";
}

function StatusBadge({ status }) {
    return (
        <span
            className={`rounded-full px-3 py-1 text-xs font-semibold ${statusClasses(
                status,
            )}`}
        >
            {formatValue(status)}
        </span>
    );
}

function ConfirmAppointmentForm({ appointment }) {
    const [isOpen, setIsOpen] = useState(false);

    const { data, setData, patch, processing, errors, reset } = useForm({
        meeting_link: appointment.meeting_link ?? "",
        location: appointment.location ?? "",
        counsellor_notes: appointment.counsellor_notes ?? "",
    });

    const submit = (event) => {
        event.preventDefault();

        patch(route("counsellor.appointments.confirm", appointment.id), {
            preserveScroll: true,
            onSuccess: () => {
                reset("meeting_link", "location", "counsellor_notes");
                setIsOpen(false);
            },
        });
    };

    if (!appointment.can_be_confirmed) {
        return (
            <SecondaryButton type="button" disabled>
                Already processed
            </SecondaryButton>
        );
    }

    return (
        <div className="w-full sm:w-auto">
            {!isOpen ? (
                <PrimaryButton type="button" onClick={() => setIsOpen(true)}>
                    Confirm appointment
                </PrimaryButton>
            ) : (
                <form
                    onSubmit={submit}
                    className="mt-3 rounded-lg border border-green-100 bg-white p-4 sm:min-w-96"
                >
                    {appointment.mode === "online" && (
                        <div>
                            <label
                                htmlFor={`meeting_link_${appointment.id}`}
                                className="text-sm font-medium text-gray-700"
                            >
                                Meeting link
                            </label>

                            <input
                                id={`meeting_link_${appointment.id}`}
                                type="url"
                                value={data.meeting_link}
                                onChange={(event) =>
                                    setData("meeting_link", event.target.value)
                                }
                                placeholder="https://meet.google.com/..."
                                className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            />

                            <InputError
                                message={errors.meeting_link}
                                className="mt-2"
                            />
                        </div>
                    )}

                    {appointment.mode === "in_person" && (
                        <div>
                            <label
                                htmlFor={`location_${appointment.id}`}
                                className="text-sm font-medium text-gray-700"
                            >
                                Location
                            </label>

                            <input
                                id={`location_${appointment.id}`}
                                type="text"
                                value={data.location}
                                onChange={(event) =>
                                    setData("location", event.target.value)
                                }
                                placeholder="Counselling room / branch location"
                                className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            />

                            <InputError
                                message={errors.location}
                                className="mt-2"
                            />
                        </div>
                    )}

                    <div className="mt-4">
                        <label
                            htmlFor={`counsellor_notes_${appointment.id}`}
                            className="text-sm font-medium text-gray-700"
                        >
                            Counsellor notes
                        </label>

                        <textarea
                            id={`counsellor_notes_${appointment.id}`}
                            rows="3"
                            value={data.counsellor_notes}
                            onChange={(event) =>
                                setData("counsellor_notes", event.target.value)
                            }
                            placeholder="Optional notes for the client."
                            className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        />

                        <InputError
                            message={
                                errors.counsellor_notes || errors.appointment
                            }
                            className="mt-2"
                        />
                    </div>

                    <div className="mt-4 flex flex-wrap justify-end gap-3">
                        <SecondaryButton
                            type="button"
                            onClick={() => {
                                reset(
                                    "meeting_link",
                                    "location",
                                    "counsellor_notes",
                                );
                                setIsOpen(false);
                            }}
                        >
                            Not now
                        </SecondaryButton>

                        <PrimaryButton disabled={processing}>
                            Confirm
                        </PrimaryButton>
                    </div>
                </form>
            )}
        </div>
    );
}

function AppointmentCard({ appointment }) {
    return (
        <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg">
            <div className="border-b border-gray-100 px-6 py-5">
                <div className="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                    <div>
                        <div className="flex flex-wrap items-center gap-3">
                            <h3 className="text-lg font-semibold text-gray-900">
                                {appointment.client.name}
                            </h3>

                            <StatusBadge status={appointment.status} />
                        </div>

                        <p className="mt-1 text-sm text-gray-500">
                            {appointment.client.email}
                        </p>

                        <p className="mt-1 text-sm text-gray-500">
                            {appointment.client.phone ?? "Phone not provided"}
                        </p>
                    </div>

                    <div className="rounded-lg bg-indigo-50 px-4 py-3 text-sm text-indigo-800">
                        <p className="font-semibold">
                            {appointment.appointment_date}
                        </p>
                        <p>
                            {appointment.start_time} - {appointment.end_time}
                        </p>
                    </div>
                </div>
            </div>

            <div className="grid gap-4 p-6 md:grid-cols-2 lg:grid-cols-4">
                <div className="rounded-lg bg-gray-50 p-4">
                    <p className="text-xs font-medium uppercase tracking-wide text-gray-500">
                        Mode
                    </p>
                    <p className="mt-1 font-semibold text-gray-900">
                        {formatValue(appointment.mode)}
                    </p>
                </div>

                <div className="rounded-lg bg-gray-50 p-4">
                    <p className="text-xs font-medium uppercase tracking-wide text-gray-500">
                        Timezone
                    </p>
                    <p className="mt-1 font-semibold text-gray-900">
                        {appointment.timezone}
                    </p>
                </div>

                <div className="rounded-lg bg-gray-50 p-4">
                    <p className="text-xs font-medium uppercase tracking-wide text-gray-500">
                        Service
                    </p>
                    <p className="mt-1 font-semibold text-gray-900">
                        {appointment.service?.name ?? "Not selected"}
                    </p>
                </div>

                <div className="rounded-lg bg-gray-50 p-4">
                    <p className="text-xs font-medium uppercase tracking-wide text-gray-500">
                        Location / Link
                    </p>
                    <p className="mt-1 break-words font-semibold text-gray-900">
                        {appointment.mode === "online"
                            ? appointment.meeting_link || "Pending"
                            : appointment.location || "Pending"}
                    </p>
                </div>
            </div>

            {(appointment.client_notes ||
                appointment.counsellor_notes ||
                appointment.cancellation_reason ||
                appointment.reminder_scheduled_at) && (
                <div className="border-t border-gray-100 px-6 py-5">
                    <div className="grid gap-4 lg:grid-cols-4">
                        {appointment.client_notes && (
                            <div>
                                <p className="text-xs font-semibold uppercase tracking-wide text-gray-500">
                                    Client notes
                                </p>
                                <p className="mt-1 text-sm text-gray-700">
                                    {appointment.client_notes}
                                </p>
                            </div>
                        )}

                        {appointment.counsellor_notes && (
                            <div>
                                <p className="text-xs font-semibold uppercase tracking-wide text-gray-500">
                                    Your notes
                                </p>
                                <p className="mt-1 text-sm text-gray-700">
                                    {appointment.counsellor_notes}
                                </p>
                            </div>
                        )}

                        {appointment.cancellation_reason && (
                            <div>
                                <p className="text-xs font-semibold uppercase tracking-wide text-gray-500">
                                    Cancellation reason
                                </p>
                                <p className="mt-1 text-sm text-gray-700">
                                    {appointment.cancellation_reason}
                                </p>
                            </div>
                        )}

                        {appointment.reminder_scheduled_at && (
                            <div>
                                <p className="text-xs font-semibold uppercase tracking-wide text-gray-500">
                                    Reminder hook
                                </p>
                                <p className="mt-1 text-sm text-gray-700">
                                    {appointment.reminder_scheduled_at}
                                </p>
                            </div>
                        )}
                    </div>
                </div>
            )}

            <div className="flex flex-col gap-3 border-t border-gray-100 bg-gray-50 px-6 py-4 lg:flex-row lg:items-start lg:justify-between">
                <p className="text-sm text-gray-600">
                    Confirmation will update status history and prepare the
                    reminder hook.
                </p>

                <ConfirmAppointmentForm appointment={appointment} />
            </div>
        </div>
    );
}

export default function Index({ appointments, filters, options }) {
    const { data, setData, get, processing } = useForm({
        period: filters.period ?? "upcoming",
        status: filters.status ?? "",
    });

    const submit = (event) => {
        event.preventDefault();

        get(route("counsellor.appointments.index"), {
            preserveScroll: true,
            preserveState: true,
            replace: true,
        });
    };

    const resetFilters = () => {
        router.get(
            route("counsellor.appointments.index"),
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
                        Counsellor Appointments
                    </h2>
                    <p className="mt-1 text-sm text-gray-500">
                        Review client appointment requests and confirm pending
                        sessions.
                    </p>
                </div>
            }
        >
            <Head title="Counsellor Appointments" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                    <div className="rounded-lg border border-indigo-100 bg-indigo-50 p-4">
                        <p className="text-sm text-indigo-900">
                            Pending appointments need confirmation. Online
                            appointments require a meeting link. In-person
                            appointments require a location. Simple, which is
                            why computers need sixteen files to enforce it.
                        </p>
                    </div>

                    <form
                        onSubmit={submit}
                        className="overflow-hidden bg-white p-6 shadow-sm sm:rounded-lg"
                    >
                        <div className="grid gap-4 md:grid-cols-3">
                            <div>
                                <label
                                    htmlFor="period"
                                    className="text-sm font-medium text-gray-700"
                                >
                                    Period
                                </label>

                                <select
                                    id="period"
                                    value={data.period}
                                    onChange={(event) =>
                                        setData("period", event.target.value)
                                    }
                                    className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                >
                                    {options.periods.map((period) => (
                                        <option
                                            key={period.value}
                                            value={period.value}
                                        >
                                            {period.label}
                                        </option>
                                    ))}
                                </select>
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

                    <div className="overflow-hidden bg-white px-6 py-4 shadow-sm sm:rounded-lg">
                        <p className="text-sm text-gray-600">
                            Showing{" "}
                            <span className="font-semibold text-gray-900">
                                {appointments.total}
                            </span>{" "}
                            appointment
                            {appointments.total === 1 ? "" : "s"}.
                        </p>
                    </div>

                    {appointments.data.length === 0 ? (
                        <div className="overflow-hidden bg-white p-8 text-center shadow-sm sm:rounded-lg">
                            <h3 className="text-base font-semibold text-gray-900">
                                No appointments found
                            </h3>
                            <p className="mt-2 text-sm text-gray-500">
                                Change the filters or wait for clients to book
                                appointments. Waiting: the original background
                                job.
                            </p>
                        </div>
                    ) : (
                        <div className="space-y-5">
                            {appointments.data.map((appointment) => (
                                <AppointmentCard
                                    key={appointment.id}
                                    appointment={appointment}
                                />
                            ))}
                        </div>
                    )}

                    <Pagination links={appointments.links} />
                </div>
            </div>
        </CounsellorLayout>
    );
}
