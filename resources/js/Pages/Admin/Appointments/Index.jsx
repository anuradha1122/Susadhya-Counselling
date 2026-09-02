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

function DetailBox({ label, value }) {
    return (
        <div className="rounded-lg bg-gray-50 p-4">
            <p className="text-xs font-medium uppercase tracking-wide text-gray-500">
                {label}
            </p>
            <p className="mt-1 break-words font-semibold text-gray-900">
                {formatValue(value)}
            </p>
        </div>
    );
}

function AdminStatusForm({ appointment, statuses }) {
    const [isOpen, setIsOpen] = useState(false);

    const { data, setData, patch, processing, errors, reset } = useForm({
        status: appointment.status,
        meeting_link: appointment.meeting_link ?? "",
        location: appointment.location ?? "",
        cancellation_reason: appointment.cancellation_reason ?? "",
        admin_notes: appointment.admin_notes ?? "",
    });

    const submit = (event) => {
        event.preventDefault();

        patch(route("admin.appointments.update-status", appointment.id), {
            preserveScroll: true,
            onSuccess: () => {
                setIsOpen(false);
            },
        });
    };

    const closeForm = () => {
        reset(
            "status",
            "meeting_link",
            "location",
            "cancellation_reason",
            "admin_notes",
        );
        setIsOpen(false);
    };

    return (
        <div className="w-full">
            {!isOpen ? (
                <PrimaryButton type="button" onClick={() => setIsOpen(true)}>
                    Manage status
                </PrimaryButton>
            ) : (
                <form
                    onSubmit={submit}
                    className="rounded-lg border border-indigo-100 bg-white p-4"
                >
                    <div className="grid gap-4 lg:grid-cols-2">
                        <div>
                            <label
                                htmlFor={`status_${appointment.id}`}
                                className="text-sm font-medium text-gray-700"
                            >
                                Status
                            </label>

                            <select
                                id={`status_${appointment.id}`}
                                value={data.status}
                                onChange={(event) =>
                                    setData("status", event.target.value)
                                }
                                className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >
                                {statuses.map((status) => (
                                    <option
                                        key={status.value}
                                        value={status.value}
                                    >
                                        {status.label}
                                    </option>
                                ))}
                            </select>

                            <InputError
                                message={errors.status}
                                className="mt-2"
                            />
                        </div>

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
                                        setData(
                                            "meeting_link",
                                            event.target.value,
                                        )
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
                                    placeholder="Counselling room / branch"
                                    className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                />

                                <InputError
                                    message={errors.location}
                                    className="mt-2"
                                />
                            </div>
                        )}
                    </div>

                    {data.status === "cancelled" && (
                        <div className="mt-4">
                            <label
                                htmlFor={`cancellation_reason_${appointment.id}`}
                                className="text-sm font-medium text-gray-700"
                            >
                                Cancellation reason
                            </label>

                            <textarea
                                id={`cancellation_reason_${appointment.id}`}
                                rows="3"
                                value={data.cancellation_reason}
                                onChange={(event) =>
                                    setData(
                                        "cancellation_reason",
                                        event.target.value,
                                    )
                                }
                                placeholder="Reason for cancellation."
                                className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            />

                            <InputError
                                message={errors.cancellation_reason}
                                className="mt-2"
                            />
                        </div>
                    )}

                    <div className="mt-4">
                        <label
                            htmlFor={`admin_notes_${appointment.id}`}
                            className="text-sm font-medium text-gray-700"
                        >
                            Admin notes
                        </label>

                        <textarea
                            id={`admin_notes_${appointment.id}`}
                            rows="3"
                            value={data.admin_notes}
                            onChange={(event) =>
                                setData("admin_notes", event.target.value)
                            }
                            placeholder="Internal admin notes."
                            className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        />

                        <InputError
                            message={errors.admin_notes}
                            className="mt-2"
                        />
                    </div>

                    <div className="mt-4 flex flex-wrap justify-end gap-3">
                        <SecondaryButton type="button" onClick={closeForm}>
                            Cancel
                        </SecondaryButton>

                        <PrimaryButton disabled={processing}>
                            Update status
                        </PrimaryButton>
                    </div>
                </form>
            )}
        </div>
    );
}

function AppointmentCard({ appointment, statuses }) {
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
                            Client: {appointment.client.email}
                        </p>

                        <p className="mt-1 text-sm text-gray-500">
                            Counsellor: {appointment.counsellor.name}
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

            <div className="grid gap-4 p-6 md:grid-cols-2 xl:grid-cols-4">
                <DetailBox label="Mode" value={appointment.mode} />
                <DetailBox label="Timezone" value={appointment.timezone} />
                <DetailBox
                    label="Service"
                    value={appointment.service?.name ?? "Not selected"}
                />
                <DetailBox
                    label="Location / Link"
                    value={
                        appointment.mode === "online"
                            ? appointment.meeting_link || "Pending"
                            : appointment.location || "Pending"
                    }
                />
            </div>

            <div className="grid gap-4 border-t border-gray-100 px-6 py-5 lg:grid-cols-2">
                <div className="rounded-lg border border-gray-100 p-4">
                    <p className="text-xs font-semibold uppercase tracking-wide text-gray-500">
                        Client details
                    </p>

                    <div className="mt-3 space-y-1 text-sm text-gray-700">
                        <p>Name: {formatValue(appointment.client.name)}</p>
                        <p>Email: {formatValue(appointment.client.email)}</p>
                        <p>Phone: {formatValue(appointment.client.phone)}</p>
                        <p>City: {formatValue(appointment.client.city)}</p>
                    </div>
                </div>

                <div className="rounded-lg border border-gray-100 p-4">
                    <p className="text-xs font-semibold uppercase tracking-wide text-gray-500">
                        Counsellor details
                    </p>

                    <div className="mt-3 space-y-1 text-sm text-gray-700">
                        <p>
                            Name: {formatValue(appointment.counsellor.name)}
                        </p>
                        <p>
                            Email: {formatValue(appointment.counsellor.email)}
                        </p>
                        <p>
                            Phone: {formatValue(appointment.counsellor.phone)}
                        </p>
                        <p>
                            Title:{" "}
                            {formatValue(
                                appointment.counsellor.professional_title,
                            )}
                        </p>
                    </div>
                </div>
            </div>

            {(appointment.client_notes ||
                appointment.counsellor_notes ||
                appointment.admin_notes ||
                appointment.cancellation_reason ||
                appointment.reminder_scheduled_at) && (
                <div className="border-t border-gray-100 px-6 py-5">
                    <div className="grid gap-4 lg:grid-cols-5">
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
                                    Counsellor notes
                                </p>
                                <p className="mt-1 text-sm text-gray-700">
                                    {appointment.counsellor_notes}
                                </p>
                            </div>
                        )}

                        {appointment.admin_notes && (
                            <div>
                                <p className="text-xs font-semibold uppercase tracking-wide text-gray-500">
                                    Admin notes
                                </p>
                                <p className="mt-1 text-sm text-gray-700">
                                    {appointment.admin_notes}
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

            <div className="border-t border-gray-100 bg-gray-50 px-6 py-5">
                <div className="mb-4 text-sm text-gray-600">
                    <p>
                        Admin can override status when operational correction is
                        needed. The change is recorded in status history, so the
                        database remembers what humans try to forget.
                    </p>

                    <p className="mt-1 text-xs text-gray-500">
                        Created by: {formatValue(appointment.audit.created_by)} ·
                        Updated by: {formatValue(appointment.audit.updated_by)} ·
                        Cancelled by:{" "}
                        {formatValue(appointment.audit.cancelled_by)}
                    </p>
                </div>

                <AdminStatusForm
                    appointment={appointment}
                    statuses={statuses}
                />
            </div>
        </div>
    );
}

export default function Index({ appointments, filters, options }) {
    const { data, setData, get, processing } = useForm({
        search: filters.search ?? "",
        status: filters.status ?? "",
        period: filters.period ?? "upcoming",
        mode: filters.mode ?? "",
    });

    const submit = (event) => {
        event.preventDefault();

        get(route("admin.appointments.index"), {
            preserveScroll: true,
            preserveState: true,
            replace: true,
        });
    };

    const resetFilters = () => {
        router.get(
            route("admin.appointments.index"),
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
                        Appointment Oversight
                    </h2>
                    <p className="mt-1 text-sm text-gray-500">
                        Review client appointments and manage operational status
                        changes.
                    </p>
                </div>
            }
        >
            <Head title="Appointment Oversight" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                    <div className="rounded-lg border border-indigo-100 bg-indigo-50 p-4">
                        <p className="text-sm text-indigo-900">
                            This page gives admins full appointment visibility
                            and controlled status management. Online
                            confirmations require a meeting link. In-person
                            confirmations require a location. Cancelled
                            appointments store a reason and user audit.
                        </p>
                    </div>

                    <form
                        onSubmit={submit}
                        className="overflow-hidden bg-white p-6 shadow-sm sm:rounded-lg"
                    >
                        <div className="grid gap-4 lg:grid-cols-5">
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
                                    placeholder="Client, counsellor, email, phone, UUID"
                                    className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                />
                            </div>

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

                            <div>
                                <label
                                    htmlFor="mode"
                                    className="text-sm font-medium text-gray-700"
                                >
                                    Mode
                                </label>

                                <select
                                    id="mode"
                                    value={data.mode}
                                    onChange={(event) =>
                                        setData("mode", event.target.value)
                                    }
                                    className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                >
                                    <option value="">Any mode</option>

                                    {options.modes.map((mode) => (
                                        <option
                                            key={mode.value}
                                            value={mode.value}
                                        >
                                            {mode.label}
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
                                Try changing the filters. The database is
                                empty, not philosophical.
                            </p>
                        </div>
                    ) : (
                        <div className="space-y-5">
                            {appointments.data.map((appointment) => (
                                <AppointmentCard
                                    key={appointment.id}
                                    appointment={appointment}
                                    statuses={options.statuses}
                                />
                            ))}
                        </div>
                    )}

                    <Pagination links={appointments.links} />
                </div>
            </div>
        </AdminLayout>
    );
}
