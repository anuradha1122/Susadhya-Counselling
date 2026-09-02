import Pagination from "@/Components/Pagination";
import PrimaryButton from "@/Components/PrimaryButton";
import SecondaryButton from "@/Components/SecondaryButton";
import ClientLayout from "@/Layouts/ClientLayout";
import { Head, Link, router, useForm } from "@inertiajs/react";

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

function modeLabel(mode) {
    return formatValue(mode);
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

function AppointmentCard({ appointment }) {
    return (
        <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg">
            <div className="border-b border-gray-100 px-6 py-5">
                <div className="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                    <div>
                        <div className="flex flex-wrap items-center gap-3">
                            <h3 className="text-lg font-semibold text-gray-900">
                                {appointment.counsellor.name}
                            </h3>

                            <StatusBadge status={appointment.status} />
                        </div>

                        <p className="mt-1 text-sm text-gray-500">
                            {formatValue(
                                appointment.counsellor.professional_title,
                            )}
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
                        {modeLabel(appointment.mode)}
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
                appointment.cancellation_reason) && (
                <div className="border-t border-gray-100 px-6 py-5">
                    <div className="grid gap-4 lg:grid-cols-3">
                        {appointment.client_notes && (
                            <div>
                                <p className="text-xs font-semibold uppercase tracking-wide text-gray-500">
                                    Your notes
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
                    </div>
                </div>
            )}

            <div className="flex flex-col gap-3 border-t border-gray-100 bg-gray-50 px-6 py-4 sm:flex-row sm:items-center sm:justify-between">
                <Link
                    href={route(
                        "client.counsellors.show",
                        appointment.counsellor.id,
                    )}
                    className="text-sm font-medium text-indigo-600 hover:text-indigo-700"
                >
                    View counsellor profile
                </Link>

                <div className="flex flex-wrap gap-3">
                    <SecondaryButton type="button" disabled>
                        Reschedule soon
                    </SecondaryButton>

                    <SecondaryButton type="button" disabled>
                        Cancel soon
                    </SecondaryButton>
                </div>
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

        get(route("client.appointments.index"), {
            preserveScroll: true,
            preserveState: true,
            replace: true,
        });
    };

    const resetFilters = () => {
        router.get(
            route("client.appointments.index"),
            {},
            {
                preserveScroll: true,
                replace: true,
            },
        );
    };

    return (
        <ClientLayout
            header={
                <div>
                    <h2 className="text-xl font-semibold leading-tight text-gray-800">
                        My Appointments
                    </h2>
                    <p className="mt-1 text-sm text-gray-500">
                        View your appointment requests, upcoming counselling
                        sessions, and appointment history.
                    </p>
                </div>
            }
        >
            <Head title="My Appointments" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                    <div className="rounded-lg border border-indigo-100 bg-indigo-50 p-4">
                        <p className="text-sm text-indigo-900">
                            New appointment requests start as pending. The
                            counsellor or admin can confirm them later. Because
                            naturally, a calendar is not complicated enough
                            until status workflows join the party.
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
                                Change the filters or book an appointment from a
                                counsellor profile.
                            </p>

                            <div className="mt-5">
                                <Link href={route("client.counsellors.index")}>
                                    <PrimaryButton type="button">
                                        Find counsellors
                                    </PrimaryButton>
                                </Link>
                            </div>
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
        </ClientLayout>
    );
}
