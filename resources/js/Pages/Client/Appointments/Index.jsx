import InputError from "@/Components/InputError";
import Pagination from "@/Components/Pagination";
import PrimaryButton from "@/Components/PrimaryButton";
import SecondaryButton from "@/Components/SecondaryButton";
import ClientLayout from "@/Layouts/ClientLayout";
import { Head, Link, router, useForm } from "@inertiajs/react";
import { useEffect, useState } from "react";

function formatValue(value) {
    if (value === null || value === undefined || value === "") {
        return "Not provided";
    }

    return String(value)
        .replaceAll("_", " ")
        .replace(/\b\w/g, (character) => character.toUpperCase());
}

function todayForInput() {
    const now = new Date();
    const localDate = new Date(now.getTime() - now.getTimezoneOffset() * 60000);

    return localDate.toISOString().slice(0, 10);
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

function CancelAppointmentForm({ appointment }) {
    const [isOpen, setIsOpen] = useState(false);

    const { data, setData, patch, processing, errors, reset } = useForm({
        cancellation_reason: "",
    });

    const submit = (event) => {
        event.preventDefault();

        patch(route("client.appointments.cancel", appointment.id), {
            preserveScroll: true,
            onSuccess: () => {
                reset("cancellation_reason");
                setIsOpen(false);
            },
        });
    };

    if (!appointment.can_be_cancelled) {
        return (
            <SecondaryButton type="button" disabled>
                Cannot cancel
            </SecondaryButton>
        );
    }

    return (
        <div className="w-full sm:w-auto">
            {!isOpen ? (
                <SecondaryButton type="button" onClick={() => setIsOpen(true)}>
                    Cancel appointment
                </SecondaryButton>
            ) : (
                <form
                    onSubmit={submit}
                    className="mt-3 rounded-lg border border-red-100 bg-white p-4 sm:min-w-80"
                >
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
                            setData("cancellation_reason", event.target.value)
                        }
                        placeholder="Optional reason for cancelling this appointment."
                        className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    />

                    <InputError
                        message={
                            errors.cancellation_reason || errors.appointment
                        }
                        className="mt-2"
                    />

                    <div className="mt-3 flex flex-wrap justify-end gap-3">
                        <SecondaryButton
                            type="button"
                            onClick={() => {
                                reset("cancellation_reason");
                                setIsOpen(false);
                            }}
                        >
                            Keep appointment
                        </SecondaryButton>

                        <PrimaryButton disabled={processing}>
                            Confirm cancellation
                        </PrimaryButton>
                    </div>
                </form>
            )}
        </div>
    );
}

function RescheduleAppointmentForm({ appointment }) {
    const [isOpen, setIsOpen] = useState(false);
    const [slots, setSlots] = useState([]);
    const [loadingSlots, setLoadingSlots] = useState(false);
    const [slotError, setSlotError] = useState("");

    const { data, setData, patch, processing, errors, reset } = useForm({
        appointment_date: appointment.appointment_date ?? todayForInput(),
        start_time: "",
        end_time: "",
        mode: appointment.mode ?? "online",
        client_notes: appointment.client_notes ?? "",
    });

    useEffect(() => {
        if (!isOpen || !data.appointment_date || !data.mode) {
            setSlots([]);
            return;
        }

        const controller = new AbortController();

        setLoadingSlots(true);
        setSlotError("");
        setData("start_time", "");
        setData("end_time", "");

        const parameters = new URLSearchParams({
            appointment_date: data.appointment_date,
            mode: data.mode,
        });

        fetch(
            `${route(
                "client.appointments.reschedule-slots",
                appointment.id,
            )}?${parameters.toString()}`,
            {
                headers: {
                    Accept: "application/json",
                },
                signal: controller.signal,
            },
        )
            .then(async (response) => {
                if (!response.ok) {
                    throw new Error("Unable to load reschedule slots.");
                }

                return response.json();
            })
            .then((payload) => {
                setSlots(payload.slots ?? []);
            })
            .catch((error) => {
                if (error.name === "AbortError") {
                    return;
                }

                setSlots([]);
                setSlotError(
                    "Available reschedule slots could not be loaded. Please try another date or mode.",
                );
            })
            .finally(() => {
                setLoadingSlots(false);
            });

        return () => controller.abort();
    }, [appointment.id, data.appointment_date, data.mode, isOpen]);

    const selectSlot = (slot) => {
        setData("start_time", slot.start_time);
        setData("end_time", slot.end_time);
    };

    const submit = (event) => {
        event.preventDefault();

        patch(route("client.appointments.reschedule", appointment.id), {
            preserveScroll: true,
            onSuccess: () => {
                reset("start_time", "end_time");
                setIsOpen(false);
            },
        });
    };

    if (!appointment.can_be_rescheduled) {
        return (
            <SecondaryButton type="button" disabled>
                Cannot reschedule
            </SecondaryButton>
        );
    }

    return (
        <div className="w-full sm:w-auto">
            {!isOpen ? (
                <SecondaryButton type="button" onClick={() => setIsOpen(true)}>
                    Reschedule appointment
                </SecondaryButton>
            ) : (
                <form
                    onSubmit={submit}
                    className="mt-3 rounded-lg border border-indigo-100 bg-white p-4 sm:min-w-96"
                >
                    <div className="grid gap-4 md:grid-cols-2">
                        <div>
                            <label
                                htmlFor={`reschedule_date_${appointment.id}`}
                                className="text-sm font-medium text-gray-700"
                            >
                                New date
                            </label>

                            <input
                                id={`reschedule_date_${appointment.id}`}
                                type="date"
                                min={todayForInput()}
                                value={data.appointment_date}
                                onChange={(event) =>
                                    setData(
                                        "appointment_date",
                                        event.target.value,
                                    )
                                }
                                className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            />

                            <InputError
                                message={errors.appointment_date}
                                className="mt-2"
                            />
                        </div>

                        <div>
                            <label
                                htmlFor={`reschedule_mode_${appointment.id}`}
                                className="text-sm font-medium text-gray-700"
                            >
                                Mode
                            </label>

                            <select
                                id={`reschedule_mode_${appointment.id}`}
                                value={data.mode}
                                onChange={(event) =>
                                    setData("mode", event.target.value)
                                }
                                className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >
                                <option value="online">Online</option>
                                <option value="in_person">In person</option>
                            </select>

                            <InputError
                                message={errors.mode}
                                className="mt-2"
                            />
                        </div>
                    </div>

                    <div className="mt-4">
                        <div className="flex items-center justify-between gap-4">
                            <p className="text-sm font-medium text-gray-700">
                                New available slots
                            </p>

                            {loadingSlots && (
                                <p className="text-xs text-gray-500">
                                    Loading slots...
                                </p>
                            )}
                        </div>

                        {slotError && (
                            <div className="mt-2 rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700">
                                {slotError}
                            </div>
                        )}

                        {!loadingSlots && slots.length === 0 && !slotError && (
                            <div className="mt-2 rounded-lg border border-dashed border-gray-300 bg-gray-50 p-4 text-sm text-gray-600">
                                No available reschedule slots for this date and
                                mode. Time remains annoying.
                            </div>
                        )}

                        {slots.length > 0 && (
                            <div className="mt-3 grid gap-3 sm:grid-cols-2">
                                {slots.map((slot) => {
                                    const selected =
                                        data.start_time === slot.start_time &&
                                        data.end_time === slot.end_time;

                                    return (
                                        <button
                                            key={`${slot.date}-${slot.start_time}-${slot.end_time}`}
                                            type="button"
                                            onClick={() => selectSlot(slot)}
                                            className={`rounded-lg border p-4 text-left transition ${
                                                selected
                                                    ? "border-indigo-500 bg-indigo-50 ring-2 ring-indigo-100"
                                                    : "border-gray-200 bg-white hover:border-indigo-300 hover:bg-indigo-50"
                                            }`}
                                        >
                                            <p className="font-semibold text-gray-900">
                                                {slot.start_time} -{" "}
                                                {slot.end_time}
                                            </p>

                                            <p className="mt-1 text-xs text-gray-500">
                                                {formatValue(slot.mode)} ·{" "}
                                                {slot.timezone}
                                            </p>
                                        </button>
                                    );
                                })}
                            </div>
                        )}

                        <InputError
                            message={errors.start_time || errors.end_time}
                            className="mt-2"
                        />
                        <InputError
                            message={errors.appointment}
                            className="mt-2"
                        />
                    </div>

                    <div className="mt-4">
                        <label
                            htmlFor={`reschedule_notes_${appointment.id}`}
                            className="text-sm font-medium text-gray-700"
                        >
                            Notes
                        </label>

                        <textarea
                            id={`reschedule_notes_${appointment.id}`}
                            rows="3"
                            value={data.client_notes}
                            onChange={(event) =>
                                setData("client_notes", event.target.value)
                            }
                            placeholder="Optional note for the counsellor."
                            className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        />

                        <InputError
                            message={errors.client_notes}
                            className="mt-2"
                        />
                    </div>

                    <div className="mt-4 flex flex-wrap justify-end gap-3">
                        <SecondaryButton
                            type="button"
                            onClick={() => {
                                reset(
                                    "appointment_date",
                                    "start_time",
                                    "end_time",
                                    "mode",
                                    "client_notes",
                                );
                                setIsOpen(false);
                            }}
                        >
                            Keep current time
                        </SecondaryButton>

                        <PrimaryButton
                            disabled={
                                processing || !data.start_time || !data.end_time
                            }
                        >
                            Confirm reschedule
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
                                {appointment.counsellor.name}
                            </h3>

                            <StatusBadge status={appointment.status} />
                        </div>

                        <p className="mt-1 text-sm text-gray-500">
                            {formatValue(
                                appointment.counsellor.professional_title,
                            )}
                        </p>

                        {appointment.rescheduled_from_appointment_id && (
                            <p className="mt-2 text-xs font-medium text-blue-700">
                                This appointment was created from a reschedule
                                request.
                            </p>
                        )}
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

            <div className="flex flex-col gap-3 border-t border-gray-100 bg-gray-50 px-6 py-4 lg:flex-row lg:items-start lg:justify-between">
                <Link
                    href={route(
                        "client.counsellors.show",
                        appointment.counsellor.id,
                    )}
                    className="text-sm font-medium text-indigo-600 hover:text-indigo-700"
                >
                    View counsellor profile
                </Link>

                <div className="flex flex-col gap-3 sm:flex-row sm:items-start">
                    <RescheduleAppointmentForm appointment={appointment} />

                    <CancelAppointmentForm appointment={appointment} />
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
                        View, reschedule, and cancel your counselling
                        appointments.
                    </p>
                </div>
            }
        >
            <Head title="My Appointments" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                    <div className="rounded-lg border border-indigo-100 bg-indigo-50 p-4">
                        <p className="text-sm text-indigo-900">
                            Pending and confirmed appointments can be
                            rescheduled or cancelled. A reschedule creates a new
                            pending appointment and keeps the old appointment as
                            a history record. Data integrity, that tiny candle
                            in the software darkness.
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
