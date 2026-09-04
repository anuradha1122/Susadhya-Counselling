import InputError from "@/Components/InputError";
import PrimaryButton from "@/Components/PrimaryButton";
import SecondaryButton from "@/Components/SecondaryButton";
import ClientLayout from "@/Layouts/ClientLayout";
import { Head, Link, useForm } from "@inertiajs/react";
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

function Pill({ children }) {
    return (
        <span className="rounded-full bg-indigo-50 px-3 py-1 text-xs font-medium text-indigo-700">
            {children}
        </span>
    );
}

function MutedPill({ children }) {
    return (
        <span className="rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700">
            {children}
        </span>
    );
}

function SectionCard({ title, description, children }) {
    return (
        <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg">
            <div className="border-b border-gray-100 px-6 py-4">
                <h3 className="text-lg font-semibold text-gray-900">{title}</h3>

                {description && (
                    <p className="mt-1 text-sm text-gray-500">{description}</p>
                )}
            </div>

            <div className="p-6">{children}</div>
        </div>
    );
}

function DetailStat({ label, value }) {
    return (
        <div className="rounded-lg bg-gray-50 p-4">
            <p className="text-xs font-medium uppercase tracking-wide text-gray-500">
                {label}
            </p>
            <p className="mt-1 text-base font-semibold text-gray-900">
                {formatValue(value)}
            </p>
        </div>
    );
}

function AvailabilitySection({ availability }) {
    if (!availability || availability.length === 0) {
        return (
            <p className="text-sm text-gray-500">
                This counsellor has not published availability yet.
            </p>
        );
    }

    return (
        <div className="space-y-5">
            {availability.map((day) => (
                <div
                    key={day.day_of_week}
                    className="rounded-lg border border-gray-200 p-4"
                >
                    <h4 className="text-base font-semibold text-gray-900">
                        {day.day_name}
                    </h4>

                    <div className="mt-3 space-y-3">
                        {day.slots.map((slot) => (
                            <div
                                key={slot.id}
                                className="rounded-lg bg-gray-50 p-4"
                            >
                                <div className="flex flex-col gap-2 md:flex-row md:items-start md:justify-between">
                                    <div>
                                        <p className="font-semibold text-gray-900">
                                            {slot.start_time} - {slot.end_time}
                                        </p>

                                        <p className="mt-1 text-sm text-gray-600">
                                            {formatValue(slot.mode)} ·{" "}
                                            {slot.slot_duration_minutes} minute
                                            sessions · {slot.capacity_per_slot}{" "}
                                            per slot
                                        </p>

                                        <p className="mt-1 text-xs text-gray-500">
                                            Buffer: {slot.buffer_minutes} mins ·
                                            Timezone: {slot.timezone}
                                        </p>
                                    </div>

                                    <MutedPill>Published</MutedPill>
                                </div>

                                {slot.breaks.length > 0 && (
                                    <div className="mt-4 border-t border-gray-200 pt-3">
                                        <p className="text-xs font-semibold uppercase tracking-wide text-gray-500">
                                            Breaks
                                        </p>

                                        <div className="mt-2 flex flex-wrap gap-2">
                                            {slot.breaks.map(
                                                (availabilityBreak) => (
                                                    <MutedPill
                                                        key={
                                                            availabilityBreak.id
                                                        }
                                                    >
                                                        {
                                                            availabilityBreak.title
                                                        }
                                                        :{" "}
                                                        {
                                                            availabilityBreak.start_time
                                                        }{" "}
                                                        -{" "}
                                                        {
                                                            availabilityBreak.end_time
                                                        }
                                                    </MutedPill>
                                                ),
                                            )}
                                        </div>
                                    </div>
                                )}
                            </div>
                        ))}
                    </div>
                </div>
            ))}
        </div>
    );
}

function BookingPanel({ counsellor }) {
    const [slots, setSlots] = useState([]);
    const [loadingSlots, setLoadingSlots] = useState(false);
    const [slotError, setSlotError] = useState("");

    const {
        data,
        setData,
        post,
        processing,
        errors,
        recentlySuccessful,
        reset,
    } = useForm({
        counsellor_profile_id: counsellor.id,
        counselling_service_id: "",
        appointment_date: todayForInput(),
        start_time: "",
        end_time: "",
        mode: "online",
        client_notes: "",
    });

    useEffect(() => {
        if (!data.appointment_date || !data.mode) {
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
                "client.counsellors.appointment-slots.index",
                counsellor.id,
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
                    throw new Error("Unable to load available slots.");
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
                    "Available slots could not be loaded. Please try another date or mode.",
                );
            })
            .finally(() => {
                setLoadingSlots(false);
            });

        return () => controller.abort();
    }, [counsellor.id, data.appointment_date, data.mode]);

    const selectSlot = (slot) => {
        setData("start_time", slot.start_time);
        setData("end_time", slot.end_time);
    };

    const submit = (event) => {
        event.preventDefault();

        post(route("client.appointments.store"), {
            preserveScroll: true,
            onSuccess: () => {
                reset("start_time", "end_time", "client_notes");
            },
        });
    };

    const hasSelectedSlot = data.start_time && data.end_time;

    return (
        <SectionCard
            title="Book Appointment"
            description="Choose an available date, mode, and time slot. The system rechecks the slot before saving, because calendars enjoy betrayal."
        >
            <form onSubmit={submit} className="space-y-5">
                <div className="grid gap-4 md:grid-cols-2">
                    <div>
                        <label
                            htmlFor="appointment_date"
                            className="text-sm font-medium text-gray-700"
                        >
                            Appointment date
                        </label>

                        <input
                            id="appointment_date"
                            type="date"
                            min={todayForInput()}
                            value={data.appointment_date}
                            onChange={(event) =>
                                setData("appointment_date", event.target.value)
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
                            htmlFor="mode"
                            className="text-sm font-medium text-gray-700"
                        >
                            Counselling mode
                        </label>

                        <select
                            id="mode"
                            value={data.mode}
                            onChange={(event) =>
                                setData("mode", event.target.value)
                            }
                            className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        >
                            <option value="online">Online</option>
                            <option value="in_person">In person</option>
                        </select>

                        <InputError message={errors.mode} className="mt-2" />
                    </div>
                </div>

                <div>
                    <div className="flex items-center justify-between gap-4">
                        <p className="text-sm font-medium text-gray-700">
                            Available slots
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
                            No available slots for this date and mode. Try
                            another day, because time remains stubbornly linear.
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
                                            {slot.start_time} - {slot.end_time}
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

                    <InputError message={errors.start_time} className="mt-2" />
                    <InputError message={errors.end_time} className="mt-2" />
                </div>

                <div>
                    <label
                        htmlFor="client_notes"
                        className="text-sm font-medium text-gray-700"
                    >
                        Notes for counsellor
                    </label>

                    <textarea
                        id="client_notes"
                        rows="4"
                        value={data.client_notes}
                        onChange={(event) =>
                            setData("client_notes", event.target.value)
                        }
                        placeholder="Briefly mention what you would like support with."
                        className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    />

                    <InputError
                        message={errors.client_notes}
                        className="mt-2"
                    />
                </div>

                {recentlySuccessful && (
                    <div className="rounded-lg border border-green-200 bg-green-50 p-3 text-sm text-green-700">
                        Appointment request submitted successfully.
                    </div>
                )}

                <div className="flex justify-end">
                    <PrimaryButton
                        type="submit"
                        disabled={processing || !hasSelectedSlot}
                    >
                        Request appointment
                    </PrimaryButton>
                </div>
            </form>
        </SectionCard>
    );
}

export default function Show({ counsellor }) {
    return (
        <ClientLayout
            header={
                <div>
                    <h2 className="text-xl font-semibold leading-tight text-gray-800">
                        Counsellor Profile
                    </h2>
                    <p className="mt-1 text-sm text-gray-500">
                        Review counsellor details and request an appointment.
                    </p>
                </div>
            }
        >
            <Head title={counsellor.name} />

            <div className="py-12">
                <div className="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                    <div className="flex justify-start">
                        <Link href={route("client.counsellors.index")}>
                            <SecondaryButton type="button">
                                Back to counsellors
                            </SecondaryButton>
                        </Link>
                    </div>

                    <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <div className="bg-gradient-to-r from-indigo-50 to-white px-6 py-8">
                            <div className="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">
                                <div>
                                    <div className="flex flex-wrap items-center gap-3">
                                        <h1 className="text-2xl font-bold text-gray-900">
                                            {counsellor.name}
                                        </h1>

                                        <span className="rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700">
                                            Rating:{" "}
                                            {counsellor.rating_placeholder}
                                        </span>
                                    </div>

                                    <p className="mt-2 text-base text-gray-600">
                                        {formatValue(
                                            counsellor.professional_title,
                                        )}
                                    </p>

                                    <p className="mt-2 text-sm text-gray-500">
                                        {formatValue(counsellor.city)}
                                    </p>
                                </div>

                                <div className="rounded-lg border border-indigo-100 bg-white px-4 py-3 text-sm text-indigo-700 shadow-sm">
                                    Booking requests start as pending until the
                                    counsellor confirms them.
                                </div>
                            </div>
                        </div>

                        <div className="grid gap-4 border-t border-gray-100 p-6 md:grid-cols-2 lg:grid-cols-4">
                            <DetailStat
                                label="Experience"
                                value={`${counsellor.years_of_experience ?? 0} years`}
                            />
                            <DetailStat
                                label="Registration"
                                value={counsellor.registration_number}
                            />
                            <DetailStat
                                label="Gender"
                                value={counsellor.gender}
                            />
                            <DetailStat
                                label="Location"
                                value={counsellor.city}
                            />
                        </div>
                    </div>

                    <div className="grid gap-6 lg:grid-cols-3">
                        <div className="space-y-6 lg:col-span-2">
                            <BookingPanel counsellor={counsellor} />

                            <SectionCard
                                title="About"
                                description="Professional background and counselling profile."
                            >
                                {counsellor.biography ? (
                                    <p className="whitespace-pre-line text-sm leading-6 text-gray-700">
                                        {counsellor.biography}
                                    </p>
                                ) : (
                                    <p className="text-sm text-gray-500">
                                        No biography has been added yet.
                                    </p>
                                )}
                            </SectionCard>

                            <SectionCard
                                title="Published Availability"
                                description="General recurring availability. The booking form above shows only currently bookable slots."
                            >
                                <AvailabilitySection
                                    availability={
                                        counsellor.availability_summary
                                    }
                                />
                            </SectionCard>

                            <SectionCard
                                title="Qualifications"
                                description="Education and professional qualifications."
                            >
                                {counsellor.qualifications.length === 0 ? (
                                    <p className="text-sm text-gray-500">
                                        No qualifications listed yet.
                                    </p>
                                ) : (
                                    <div className="space-y-3">
                                        {counsellor.qualifications.map(
                                            (qualification) => (
                                                <div
                                                    key={qualification.id}
                                                    className="rounded-lg border border-gray-200 p-4"
                                                >
                                                    <p className="font-semibold text-gray-900">
                                                        {formatValue(
                                                            qualification.qualification,
                                                        )}
                                                    </p>

                                                    <p className="mt-1 text-sm text-gray-600">
                                                        {formatValue(
                                                            qualification.institution,
                                                        )}
                                                    </p>

                                                    <p className="mt-1 text-xs text-gray-500">
                                                        Completed:{" "}
                                                        {formatValue(
                                                            qualification.year_completed,
                                                        )}
                                                    </p>

                                                    {qualification.notes && (
                                                        <p className="mt-2 text-sm text-gray-500">
                                                            {
                                                                qualification.notes
                                                            }
                                                        </p>
                                                    )}
                                                </div>
                                            ),
                                        )}
                                    </div>
                                )}
                            </SectionCard>
                        </div>

                        <div className="space-y-6">
                            <SectionCard title="Specializations">
                                <div className="flex flex-wrap gap-2">
                                    {counsellor.specializations.length === 0 ? (
                                        <MutedPill>Not listed</MutedPill>
                                    ) : (
                                        counsellor.specializations.map(
                                            (specialization) => (
                                                <Pill key={specialization.id}>
                                                    {specialization.name}
                                                </Pill>
                                            ),
                                        )
                                    )}
                                </div>
                            </SectionCard>

                            <SectionCard title="Languages">
                                <div className="flex flex-wrap gap-2">
                                    {counsellor.languages.length === 0 ? (
                                        <MutedPill>Not listed</MutedPill>
                                    ) : (
                                        counsellor.languages.map((language) => (
                                            <MutedPill key={language.id}>
                                                {language.name}
                                                {language.proficiency
                                                    ? ` · ${formatValue(
                                                          language.proficiency,
                                                      )}`
                                                    : ""}
                                            </MutedPill>
                                        ))
                                    )}
                                </div>
                            </SectionCard>

                            <SectionCard
                                title="Booking Protection"
                                description="How appointment safety is handled."
                            >
                                <ul className="space-y-2 text-sm text-gray-600">
                                    <li>
                                        • Breaks, blocked slots, and leave days
                                        are excluded.
                                    </li>
                                    <li>
                                        • Existing counsellor bookings are
                                        excluded.
                                    </li>
                                    <li>
                                        • Existing client bookings are excluded.
                                    </li>
                                    <li>
                                        • The slot is rechecked before saving.
                                    </li>
                                </ul>
                            </SectionCard>

                            <SectionCard title="Contact Visibility">
                                <div className="space-y-2 text-sm text-gray-600">
                                    <p>
                                        Email: {formatValue(counsellor.email)}
                                    </p>
                                    <p>
                                        Phone: {formatValue(counsellor.phone)}
                                    </p>
                                </div>
                            </SectionCard>
                        </div>
                    </div>
                </div>
            </div>
        </ClientLayout>
    );
}
