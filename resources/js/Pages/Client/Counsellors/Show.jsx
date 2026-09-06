import InputError from "@/Components/InputError";
import PrimaryButton from "@/Components/PrimaryButton";
import SecondaryButton from "@/Components/SecondaryButton";
import ClientLayout from "@/Layouts/ClientLayout";
import {
    Head,
    Link,
    useForm,
} from "@inertiajs/react";
import { UserRound } from "lucide-react";
import {
    useEffect,
    useMemo,
    useState,
} from "react";

function formatValue(value) {
    if (
        value === null ||
        value === undefined ||
        value === ""
    ) {
        return "Not provided";
    }

    return String(value)
        .replaceAll("_", " ")
        .replace(
            /\b\w/g,
            (character) =>
                character.toUpperCase(),
        );
}

function todayForInput() {
    const now = new Date();

    const localDate = new Date(
        now.getTime() -
            now.getTimezoneOffset() *
                60000,
    );

    return localDate
        .toISOString()
        .slice(0, 10);
}

function money(
    amount,
    currency = "LKR",
) {
    return `${currency} ${Number(
        amount ?? 0,
    ).toFixed(2)}`;
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

function SectionCard({
    title,
    description,
    children,
}) {
    return (
        <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg">
            <div className="border-b border-gray-100 px-6 py-4">
                <h3 className="text-lg font-semibold text-gray-900">
                    {title}
                </h3>

                {description && (
                    <p className="mt-1 text-sm text-gray-500">
                        {description}
                    </p>
                )}
            </div>

            <div className="p-6">
                {children}
            </div>
        </div>
    );
}

function DetailStat({
    label,
    value,
}) {
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

function ProfilePhoto({
    counsellor,
}) {
    if (
        counsellor.profile_photo_url
    ) {
        return (
            <img
                src={
                    counsellor.profile_photo_url
                }
                alt={`${counsellor.name} profile`}
                className="h-full w-full object-cover"
            />
        );
    }

    return (
        <div className="flex h-full w-full items-center justify-center bg-indigo-50 text-indigo-500">
            <UserRound className="h-12 w-12" />
        </div>
    );
}

function AvailabilitySection({
    availability,
}) {
    if (
        !availability ||
        availability.length === 0
    ) {
        return (
            <p className="text-sm text-gray-500">
                This counsellor has not
                published availability
                yet.
            </p>
        );
    }

    return (
        <div className="space-y-5">
            {availability.map(
                (day) => (
                    <div
                        key={
                            day.day_of_week
                        }
                        className="rounded-lg border border-gray-200 p-4"
                    >
                        <h4 className="text-base font-semibold text-gray-900">
                            {day.day_name}
                        </h4>

                        <div className="mt-3 space-y-3">
                            {day.slots.map(
                                (slot) => (
                                    <div
                                        key={
                                            slot.id
                                        }
                                        className="rounded-lg bg-gray-50 p-4"
                                    >
                                        <div className="flex flex-col gap-2 md:flex-row md:items-start md:justify-between">
                                            <div>
                                                <p className="font-semibold text-gray-900">
                                                    {
                                                        slot.start_time
                                                    }{" "}
                                                    -{" "}
                                                    {
                                                        slot.end_time
                                                    }
                                                </p>

                                                <p className="mt-1 text-sm text-gray-600">
                                                    {formatValue(
                                                        slot.mode,
                                                    )}{" "}
                                                    ·{" "}
                                                    {
                                                        slot.slot_duration_minutes
                                                    }{" "}
                                                    minute
                                                    sessions
                                                    ·{" "}
                                                    {
                                                        slot.capacity_per_slot
                                                    }{" "}
                                                    per
                                                    slot
                                                </p>

                                                <p className="mt-1 text-xs text-gray-500">
                                                    Buffer:{" "}
                                                    {
                                                        slot.buffer_minutes
                                                    }{" "}
                                                    mins ·
                                                    Timezone:{" "}
                                                    {
                                                        slot.timezone
                                                    }
                                                </p>
                                            </div>

                                            <MutedPill>
                                                Published
                                            </MutedPill>
                                        </div>

                                        {(slot.breaks ??
                                            [])
                                            .length >
                                            0 && (
                                            <div className="mt-4 border-t border-gray-200 pt-3">
                                                <p className="text-xs font-semibold uppercase tracking-wide text-gray-500">
                                                    Breaks
                                                </p>

                                                <div className="mt-2 flex flex-wrap gap-2">
                                                    {slot.breaks.map(
                                                        (
                                                            availabilityBreak,
                                                        ) => (
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
                                ),
                            )}
                        </div>
                    </div>
                ),
            )}
        </div>
    );
}

function BookingPanel({
    counsellor,
    services,
}) {
    const [slots, setSlots] =
        useState([]);

    const [
        loadingSlots,
        setLoadingSlots,
    ] = useState(false);

    const [
        slotError,
        setSlotError,
    ] = useState("");

    const {
        data,
        setData,
        post,
        processing,
        errors,
        reset,
    } = useForm({
        counsellor_profile_id:
            counsellor.id,

        counselling_service_id:
            "",

        appointment_date:
            todayForInput(),

        start_time: "",

        end_time: "",

        mode: "online",

        client_notes: "",
    });

    const selectedService =
        useMemo(
            () =>
                services.find(
                    (service) =>
                        String(
                            service.id,
                        ) ===
                        String(
                            data.counselling_service_id,
                        ),
                ) ?? null,
            [
                services,
                data.counselling_service_id,
            ],
        );

    const modeOptions =
        useMemo(() => {
            if (!selectedService) {
                return [
                    {
                        value:
                            "online",
                        label:
                            "Online",
                    },
                    {
                        value:
                            "in_person",
                        label:
                            "In person",
                    },
                ];
            }

            if (
                selectedService.service_mode ===
                "online"
            ) {
                return [
                    {
                        value:
                            "online",
                        label:
                            "Online",
                    },
                ];
            }

            if (
                selectedService.service_mode ===
                "in_person"
            ) {
                return [
                    {
                        value:
                            "in_person",
                        label:
                            "In person",
                    },
                ];
            }

            return [
                {
                    value:
                        "online",
                    label:
                        "Online",
                },
                {
                    value:
                        "in_person",
                    label:
                        "In person",
                },
            ];
        }, [selectedService]);

    useEffect(() => {
        if (
            !data.counselling_service_id ||
            !data.appointment_date ||
            !data.mode
        ) {
            setSlots([]);

            return;
        }

        const controller =
            new AbortController();

        setLoadingSlots(true);
        setSlotError("");

        const parameters =
            new URLSearchParams({
                appointment_date:
                    data.appointment_date,

                mode:
                    data.mode,
            });

        fetch(
            `${route(
                "client.counsellors.appointment-slots.index",
                counsellor.uuid,
            )}?${parameters.toString()}`,
            {
                headers: {
                    Accept:
                        "application/json",
                },

                signal:
                    controller.signal,
            },
        )
            .then(
                async (
                    response,
                ) => {
                    if (
                        !response.ok
                    ) {
                        throw new Error(
                            "Unable to load available slots.",
                        );
                    }

                    return response.json();
                },
            )
            .then(
                (payload) => {
                    setSlots(
                        payload.slots ??
                            [],
                    );
                },
            )
            .catch(
                (error) => {
                    if (
                        error.name ===
                        "AbortError"
                    ) {
                        return;
                    }

                    setSlots([]);

                    setSlotError(
                        "Available slots could not be loaded. Please try another date or mode.",
                    );
                },
            )
            .finally(() => {
                setLoadingSlots(
                    false,
                );
            });

        return () =>
            controller.abort();
    }, [
        counsellor.uuid,
        data.counselling_service_id,
        data.appointment_date,
        data.mode,
    ]);

    const clearSelectedSlot =
        () => {
            setData(
                "start_time",
                "",
            );

            setData(
                "end_time",
                "",
            );

            setSlots([]);
        };

    const handleServiceChange =
        (event) => {
            const value =
                event.target.value;

            const service =
                services.find(
                    (item) =>
                        String(
                            item.id,
                        ) ===
                        String(value),
                ) ?? null;

            setData(
                "counselling_service_id",
                value,
            );

            setData(
                "start_time",
                "",
            );

            setData(
                "end_time",
                "",
            );

            setSlots([]);

            if (
                service &&
                service.service_mode !==
                    "both"
            ) {
                setData(
                    "mode",
                    service.service_mode,
                );
            }
        };

    const handleDateChange =
        (event) => {
            setData(
                "appointment_date",
                event.target.value,
            );

            clearSelectedSlot();
        };

    const handleModeChange =
        (event) => {
            setData(
                "mode",
                event.target.value,
            );

            clearSelectedSlot();
        };

    const selectSlot = (slot) => {
        setData(
            "start_time",
            slot.start_time,
        );

        setData(
            "end_time",
            slot.end_time,
        );
    };

    const submit = (event) => {
        event.preventDefault();

        post(
            route(
                "client.appointments.store",
            ),
            {
                preserveScroll:
                    true,

                onSuccess: () => {
                    reset(
                        "start_time",
                        "end_time",
                        "client_notes",
                    );
                },
            },
        );
    };

    const hasSelectedSlot =
        Boolean(
            data.start_time &&
                data.end_time,
        );

    const canSubmit =
        Boolean(
            data.counselling_service_id &&
                hasSelectedSlot,
        );

    return (
        <SectionCard
            title="Book Appointment"
            description="Choose a counselling service first, then select the date, mode and available time slot."
        >
            <form
                onSubmit={submit}
                className="space-y-5"
            >
                <div>
                    <label
                        htmlFor="counselling_service_id"
                        className="text-sm font-medium text-gray-700"
                    >
                        Counselling
                        service
                    </label>

                    <select
                        id="counselling_service_id"
                        value={
                            data.counselling_service_id
                        }
                        onChange={
                            handleServiceChange
                        }
                        className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >
                        <option value="">
                            Select a
                            counselling
                            service
                        </option>

                        {services.map(
                            (service) => (
                                <option
                                    key={
                                        service.id
                                    }
                                    value={
                                        service.id
                                    }
                                >
                                    {
                                        service.name
                                    }{" "}
                                    ·{" "}
                                    {money(
                                        service.price,
                                        service.currency,
                                    )}{" "}
                                    ·{" "}
                                    {
                                        service.duration_minutes
                                    }{" "}
                                    min ·{" "}
                                    {formatValue(
                                        service.service_mode,
                                    )}
                                </option>
                            ),
                        )}
                    </select>

                    <InputError
                        message={
                            errors.counselling_service_id
                        }
                        className="mt-2"
                    />

                    {services.length ===
                        0 && (
                        <div className="mt-2 rounded-lg border border-amber-200 bg-amber-50 p-3 text-sm text-amber-700">
                            There are
                            currently no
                            active counselling
                            services that match
                            this counsellor&apos;s
                            published
                            availability.
                        </div>
                    )}
                </div>

                {selectedService && (
                    <div className="rounded-lg border border-indigo-100 bg-indigo-50 p-4">
                        <div className="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                            <div>
                                <p className="font-semibold text-indigo-950">
                                    {
                                        selectedService.name
                                    }
                                </p>

                                <p className="mt-1 text-sm text-indigo-800">
                                    {
                                        selectedService.short_description ??
                                        "Counselling service"
                                    }
                                </p>
                            </div>

                            <div className="text-sm font-semibold text-indigo-900">
                                {money(
                                    selectedService.price,
                                    selectedService.currency,
                                )}
                            </div>
                        </div>

                        <div className="mt-3 flex flex-wrap gap-2">
                            <Pill>
                                {
                                    selectedService.duration_minutes
                                }{" "}
                                minutes
                            </Pill>

                            <Pill>
                                {formatValue(
                                    selectedService.service_mode,
                                )}
                            </Pill>
                        </div>
                    </div>
                )}

                <div className="grid gap-4 md:grid-cols-2">
                    <div>
                        <label
                            htmlFor="appointment_date"
                            className="text-sm font-medium text-gray-700"
                        >
                            Appointment
                            date
                        </label>

                        <input
                            id="appointment_date"
                            type="date"
                            min={
                                todayForInput()
                            }
                            value={
                                data.appointment_date
                            }
                            onChange={
                                handleDateChange
                            }
                            disabled={
                                !selectedService
                            }
                            className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 disabled:bg-gray-100"
                        />

                        <InputError
                            message={
                                errors.appointment_date
                            }
                            className="mt-2"
                        />
                    </div>

                    <div>
                        <label
                            htmlFor="mode"
                            className="text-sm font-medium text-gray-700"
                        >
                            Counselling
                            mode
                        </label>

                        <select
                            id="mode"
                            value={
                                data.mode
                            }
                            onChange={
                                handleModeChange
                            }
                            disabled={
                                !selectedService
                            }
                            className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 disabled:bg-gray-100"
                        >
                            {modeOptions.map(
                                (
                                    option,
                                ) => (
                                    <option
                                        key={
                                            option.value
                                        }
                                        value={
                                            option.value
                                        }
                                    >
                                        {
                                            option.label
                                        }
                                    </option>
                                ),
                            )}
                        </select>

                        <InputError
                            message={
                                errors.mode
                            }
                            className="mt-2"
                        />
                    </div>
                </div>

                <div>
                    <div className="flex items-center justify-between gap-4">
                        <p className="text-sm font-medium text-gray-700">
                            Available
                            slots
                        </p>

                        {loadingSlots && (
                            <p className="text-xs text-gray-500">
                                Loading
                                slots...
                            </p>
                        )}
                    </div>

                    {!selectedService && (
                        <div className="mt-2 rounded-lg border border-dashed border-gray-300 bg-gray-50 p-4 text-sm text-gray-600">
                            Select a
                            counselling
                            service before
                            choosing an
                            appointment
                            slot.
                        </div>
                    )}

                    {slotError && (
                        <div className="mt-2 rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700">
                            {
                                slotError
                            }
                        </div>
                    )}

                    {selectedService &&
                        !loadingSlots &&
                        slots.length ===
                            0 &&
                        !slotError && (
                            <div className="mt-2 rounded-lg border border-dashed border-gray-300 bg-gray-50 p-4 text-sm text-gray-600">
                                No available
                                slots for
                                this date
                                and mode.
                                Try another
                                date or
                                mode.
                            </div>
                        )}

                    {slots.length >
                        0 && (
                        <div className="mt-3 grid gap-3 sm:grid-cols-2">
                            {slots.map(
                                (
                                    slot,
                                ) => {
                                    const selected =
                                        data.start_time ===
                                            slot.start_time &&
                                        data.end_time ===
                                            slot.end_time;

                                    return (
                                        <button
                                            key={`${slot.date}-${slot.start_time}-${slot.end_time}`}
                                            type="button"
                                            onClick={() =>
                                                selectSlot(
                                                    slot,
                                                )
                                            }
                                            className={`rounded-lg border p-4 text-left transition ${
                                                selected
                                                    ? "border-indigo-500 bg-indigo-50 ring-2 ring-indigo-100"
                                                    : "border-gray-200 bg-white hover:border-indigo-300 hover:bg-indigo-50"
                                            }`}
                                        >
                                            <p className="font-semibold text-gray-900">
                                                {
                                                    slot.start_time
                                                }{" "}
                                                -{" "}
                                                {
                                                    slot.end_time
                                                }
                                            </p>

                                            <p className="mt-1 text-xs text-gray-500">
                                                {formatValue(
                                                    slot.mode,
                                                )}{" "}
                                                ·{" "}
                                                {
                                                    slot.timezone
                                                }
                                            </p>
                                        </button>
                                    );
                                },
                            )}
                        </div>
                    )}

                    <InputError
                        message={
                            errors.start_time
                        }
                        className="mt-2"
                    />

                    <InputError
                        message={
                            errors.end_time
                        }
                        className="mt-2"
                    />
                </div>

                <div>
                    <label
                        htmlFor="client_notes"
                        className="text-sm font-medium text-gray-700"
                    >
                        Notes for
                        counsellor
                    </label>

                    <textarea
                        id="client_notes"
                        rows="4"
                        value={
                            data.client_notes
                        }
                        onChange={(
                            event,
                        ) =>
                            setData(
                                "client_notes",
                                event
                                    .target
                                    .value,
                            )
                        }
                        placeholder="Briefly mention what you would like support with."
                        className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    />

                    <InputError
                        message={
                            errors.client_notes
                        }
                        className="mt-2"
                    />
                </div>

                <div className="flex justify-end">
                    <PrimaryButton
                        type="submit"
                        disabled={
                            processing ||
                            !canSubmit
                        }
                    >
                        {processing
                            ? "Submitting..."
                            : "Request appointment"}
                    </PrimaryButton>
                </div>
            </form>
        </SectionCard>
    );
}

export default function Show({
    counsellor,
    services = [],
}) {
    return (
        <ClientLayout
            header={
                <div>
                    <h2 className="text-xl font-semibold leading-tight text-gray-800">
                        Counsellor
                        Profile
                    </h2>

                    <p className="mt-1 text-sm text-gray-500">
                        Review
                        counsellor
                        details and
                        request an
                        appointment.
                    </p>
                </div>
            }
        >
            <Head
                title={
                    counsellor.name
                }
            />

            <div className="py-12">
                <div className="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                    <div className="flex justify-start">
                        <Link
                            href={route(
                                "client.counsellors.index",
                            )}
                        >
                            <SecondaryButton type="button">
                                Back to
                                counsellors
                            </SecondaryButton>
                        </Link>
                    </div>

                    {/* Profile header */}
                    <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <div className="bg-gradient-to-r from-indigo-50 to-white px-6 py-8">
                            <div className="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">
                                <div className="flex flex-col gap-5 sm:flex-row sm:items-start">
                                    <div className="h-32 w-32 shrink-0 overflow-hidden rounded-2xl border-4 border-white bg-gray-100 shadow-md">
                                        <ProfilePhoto
                                            counsellor={
                                                counsellor
                                            }
                                        />
                                    </div>

                                    <div>
                                        <div className="flex flex-wrap items-center gap-3">
                                            <h1 className="text-2xl font-bold text-gray-900">
                                                {
                                                    counsellor.name
                                                }
                                            </h1>

                                            {counsellor.rating_placeholder && (
                                                <span className="rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700">
                                                    Rating:{" "}
                                                    {
                                                        counsellor.rating_placeholder
                                                    }
                                                </span>
                                            )}
                                        </div>

                                        <p className="mt-2 text-base font-medium text-indigo-600">
                                            {formatValue(
                                                counsellor.professional_title,
                                            )}
                                        </p>

                                        {counsellor.city && (
                                            <p className="mt-2 text-sm text-gray-500">
                                                {formatValue(
                                                    counsellor.city,
                                                )}
                                            </p>
                                        )}

                                        {counsellor.registration_number && (
                                            <p className="mt-2 text-xs text-gray-500">
                                                Registration:{" "}
                                                <span className="font-medium text-gray-700">
                                                    {
                                                        counsellor.registration_number
                                                    }
                                                </span>
                                            </p>
                                        )}
                                    </div>
                                </div>

                                <div className="max-w-sm rounded-lg border border-indigo-100 bg-white px-4 py-3 text-sm leading-6 text-indigo-700 shadow-sm">
                                    Booking
                                    requests
                                    start as
                                    pending until
                                    the
                                    counsellor
                                    confirms them.
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
                                value={
                                    counsellor.registration_number
                                }
                            />

                            <DetailStat
                                label="Gender"
                                value={
                                    counsellor.gender
                                }
                            />

                            <DetailStat
                                label="Location"
                                value={
                                    counsellor.city
                                }
                            />
                        </div>
                    </div>

                    <div className="grid gap-6 lg:grid-cols-3">
                        <div className="space-y-6 lg:col-span-2">
                            <BookingPanel
                                counsellor={
                                    counsellor
                                }
                                services={
                                    services
                                }
                            />

                            <SectionCard
                                title="About"
                                description="Professional background and counselling profile."
                            >
                                {counsellor.biography ? (
                                    <p className="whitespace-pre-line text-sm leading-6 text-gray-700">
                                        {
                                            counsellor.biography
                                        }
                                    </p>
                                ) : (
                                    <p className="text-sm text-gray-500">
                                        No
                                        biography
                                        has been
                                        added yet.
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
                        </div>

                        <div className="space-y-6">
                            <SectionCard title="Specializations">
                                {counsellor
                                    .specializations
                                    ?.length >
                                0 ? (
                                    <div className="flex flex-wrap gap-2">
                                        {counsellor.specializations.map(
                                            (
                                                specialization,
                                            ) => (
                                                <Pill
                                                    key={
                                                        specialization.id
                                                    }
                                                >
                                                    {
                                                        specialization.name
                                                    }
                                                </Pill>
                                            ),
                                        )}
                                    </div>
                                ) : (
                                    <p className="text-sm text-gray-500">
                                        No
                                        specializations
                                        listed.
                                    </p>
                                )}
                            </SectionCard>

                            <SectionCard title="Languages">
                                {counsellor
                                    .languages
                                    ?.length >
                                0 ? (
                                    <div className="space-y-3">
                                        {counsellor.languages.map(
                                            (
                                                language,
                                            ) => (
                                                <div
                                                    key={
                                                        language.id
                                                    }
                                                    className="flex items-center justify-between gap-3"
                                                >
                                                    <span className="text-sm text-gray-700">
                                                        {
                                                            language.name
                                                        }
                                                    </span>

                                                    {language.proficiency && (
                                                        <MutedPill>
                                                            {formatValue(
                                                                language.proficiency,
                                                            )}
                                                        </MutedPill>
                                                    )}
                                                </div>
                                            ),
                                        )}
                                    </div>
                                ) : (
                                    <p className="text-sm text-gray-500">
                                        No
                                        languages
                                        listed.
                                    </p>
                                )}
                            </SectionCard>

                            <SectionCard title="Qualifications">
                                {counsellor
                                    .qualifications
                                    ?.length >
                                0 ? (
                                    <div className="space-y-4">
                                        {counsellor.qualifications.map(
                                            (
                                                qualification,
                                            ) => (
                                                <div
                                                    key={
                                                        qualification.id
                                                    }
                                                    className="rounded-lg border border-gray-200 p-4"
                                                >
                                                    <p className="font-medium text-gray-900">
                                                        {formatValue(
                                                            qualification.qualification,
                                                        )}
                                                    </p>

                                                    <p className="mt-1 text-sm text-gray-600">
                                                        {formatValue(
                                                            qualification.institution,
                                                        )}
                                                    </p>

                                                    {qualification.field_of_study && (
                                                        <p className="mt-1 text-sm text-gray-500">
                                                            {formatValue(
                                                                qualification.field_of_study,
                                                            )}
                                                        </p>
                                                    )}

                                                    {qualification.year_completed && (
                                                        <p className="mt-1 text-xs text-gray-500">
                                                            Completed:{" "}
                                                            {
                                                                qualification.year_completed
                                                            }
                                                        </p>
                                                    )}
                                                </div>
                                            ),
                                        )}
                                    </div>
                                ) : (
                                    <p className="text-sm text-gray-500">
                                        No
                                        qualifications
                                        listed.
                                    </p>
                                )}
                            </SectionCard>

                            <SectionCard title="Contact">
                                <dl className="space-y-4 text-sm">
                                    <div>
                                        <dt className="font-medium text-gray-500">
                                            Email
                                        </dt>

                                        <dd className="mt-1 text-gray-800">
                                            {formatValue(
                                                counsellor.email,
                                            )}
                                        </dd>
                                    </div>

                                    <div>
                                        <dt className="font-medium text-gray-500">
                                            Phone
                                        </dt>

                                        <dd className="mt-1 text-gray-800">
                                            {formatValue(
                                                counsellor.phone,
                                            )}
                                        </dd>
                                    </div>

                                    <div>
                                        <dt className="font-medium text-gray-500">
                                            City
                                        </dt>

                                        <dd className="mt-1 text-gray-800">
                                            {formatValue(
                                                counsellor.city,
                                            )}
                                        </dd>
                                    </div>
                                </dl>
                            </SectionCard>
                        </div>
                    </div>
                </div>
            </div>
        </ClientLayout>
    );
}
