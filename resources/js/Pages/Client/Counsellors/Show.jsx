import PrimaryButton from "@/Components/PrimaryButton";
import SecondaryButton from "@/Components/SecondaryButton";
import ClientLayout from "@/Layouts/ClientLayout";
import { Head, Link } from "@inertiajs/react";

function formatValue(value) {
    if (value === null || value === undefined || value === "") {
        return "Not provided";
    }

    return String(value)
        .replaceAll("_", " ")
        .replace(/\b\w/g, (character) => character.toUpperCase());
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

                                    <MutedPill>Available</MutedPill>
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

export default function Show({ counsellor }) {
    return (
        <ClientLayout
            header={
                <div>
                    <h2 className="text-xl font-semibold leading-tight text-gray-800">
                        Counsellor Profile
                    </h2>
                    <p className="mt-1 text-sm text-gray-500">
                        Review counsellor details before booking becomes
                        available in the appointments module.
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

                                <div className="flex flex-col gap-3 sm:flex-row">
                                    <PrimaryButton type="button" disabled>
                                        Book appointment in M09
                                    </PrimaryButton>
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
                                title="Availability"
                                description="Published recurring availability. Appointment booking will use this later in M09."
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
                                title="Booking Status"
                                description="Appointment booking is intentionally delayed until workbook M09."
                            >
                                <p className="text-sm text-gray-600">
                                    You can review counsellor details now.
                                    Actual appointment booking, conflict
                                    handling, payment workflow, and appointment
                                    status tracking will be implemented in M09.
                                </p>
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
