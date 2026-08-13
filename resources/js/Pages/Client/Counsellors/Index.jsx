import Pagination from "@/Components/Pagination";
import PrimaryButton from "@/Components/PrimaryButton";
import SecondaryButton from "@/Components/SecondaryButton";
import TextInput from "@/Components/TextInput";
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

function AvailabilitySummary({ availability }) {
    if (!availability || availability.length === 0) {
        return (
            <p className="text-sm text-gray-500">
                No public availability configured yet.
            </p>
        );
    }

    return (
        <div className="space-y-2">
            {availability.slice(0, 3).map((day) => (
                <div key={day.day_of_week} className="text-sm text-gray-600">
                    <span className="font-semibold text-gray-800">
                        {day.day_name}
                    </span>
                    :{" "}
                    {day.slots
                        .slice(0, 2)
                        .map(
                            (slot) =>
                                `${slot.start_time} - ${slot.end_time} (${formatValue(
                                    slot.mode,
                                )})`,
                        )
                        .join(", ")}
                </div>
            ))}

            {availability.length > 3 && (
                <p className="text-xs text-gray-500">
                    + {availability.length - 3} more day
                    {availability.length - 3 === 1 ? "" : "s"}
                </p>
            )}
        </div>
    );
}

function CounsellorCard({ counsellor }) {
    return (
        <div className="flex h-full flex-col rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
            <div className="flex items-start justify-between gap-4">
                <div>
                    <h3 className="text-lg font-semibold text-gray-900">
                        {counsellor.name}
                    </h3>

                    <p className="mt-1 text-sm text-gray-500">
                        {formatValue(counsellor.professional_title)}
                    </p>
                </div>

                <span className="rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700">
                    {counsellor.rating_placeholder}
                </span>
            </div>

            <div className="mt-4 grid grid-cols-2 gap-3 text-sm">
                <div className="rounded-lg bg-gray-50 p-3">
                    <p className="text-xs uppercase tracking-wide text-gray-500">
                        Experience
                    </p>
                    <p className="mt-1 font-semibold text-gray-900">
                        {counsellor.years_of_experience ?? 0} years
                    </p>
                </div>

                <div className="rounded-lg bg-gray-50 p-3">
                    <p className="text-xs uppercase tracking-wide text-gray-500">
                        Location
                    </p>
                    <p className="mt-1 font-semibold text-gray-900">
                        {formatValue(counsellor.city)}
                    </p>
                </div>
            </div>

            {counsellor.biography && (
                <p className="mt-4 line-clamp-3 text-sm text-gray-600">
                    {counsellor.biography}
                </p>
            )}

            <div className="mt-4">
                <p className="text-xs font-semibold uppercase tracking-wide text-gray-500">
                    Specializations
                </p>

                <div className="mt-2 flex flex-wrap gap-2">
                    {counsellor.specializations.length === 0 ? (
                        <MutedPill>Not listed</MutedPill>
                    ) : (
                        counsellor.specializations.map((specialization) => (
                            <Pill key={specialization.id}>
                                {specialization.name}
                            </Pill>
                        ))
                    )}
                </div>
            </div>

            <div className="mt-4">
                <p className="text-xs font-semibold uppercase tracking-wide text-gray-500">
                    Languages
                </p>

                <div className="mt-2 flex flex-wrap gap-2">
                    {counsellor.languages.length === 0 ? (
                        <MutedPill>Not listed</MutedPill>
                    ) : (
                        counsellor.languages.map((language) => (
                            <MutedPill key={language.id}>
                                {language.name}
                                {language.proficiency
                                    ? ` · ${formatValue(language.proficiency)}`
                                    : ""}
                            </MutedPill>
                        ))
                    )}
                </div>
            </div>

            <div className="mt-4 border-t border-gray-100 pt-4">
                <p className="mb-2 text-xs font-semibold uppercase tracking-wide text-gray-500">
                    Availability
                </p>

                <AvailabilitySummary
                    availability={counsellor.availability_summary}
                />
            </div>

            <div className="mt-auto pt-6">
                <Link href={route("client.counsellors.show", counsellor.id)}>
                    <SecondaryButton type="button">
                        View profile
                    </SecondaryButton>
                </Link>
            </div>
        </div>
    );
}

export default function Index({ counsellors, filters, options }) {
    const { data, setData, get, processing } = useForm({
        search: filters.search ?? "",
        specialization_id: filters.specialization_id ?? "",
        language_id: filters.language_id ?? "",
        mode: filters.mode ?? "",
        availability_day: filters.availability_day ?? "",
    });

    const submit = (event) => {
        event.preventDefault();

        get(route("client.counsellors.index"), {
            preserveScroll: true,
            preserveState: true,
            replace: true,
        });
    };

    const resetFilters = () => {
        router.get(
            route("client.counsellors.index"),
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
                        Find a Counsellor
                    </h2>
                    <p className="mt-1 text-sm text-gray-500">
                        Search active counsellors by specialty, language,
                        counselling mode, and availability.
                    </p>
                </div>
            }
        >
            <Head title="Find a Counsellor" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                    <div className="rounded-lg border border-indigo-100 bg-indigo-50 p-4">
                        <p className="text-sm text-indigo-900">
                            Search results only show active counsellors with
                            active user accounts. Ratings are placeholders until
                            the review module exists, because fake ratings are
                            how trust goes to die.
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

                                <TextInput
                                    id="search"
                                    value={data.search}
                                    className="mt-1 block w-full"
                                    placeholder="Name, city, title, specialty"
                                    onChange={(event) =>
                                        setData("search", event.target.value)
                                    }
                                />
                            </div>

                            <div>
                                <label
                                    htmlFor="specialization_id"
                                    className="text-sm font-medium text-gray-700"
                                >
                                    Specialty
                                </label>

                                <select
                                    id="specialization_id"
                                    value={data.specialization_id}
                                    onChange={(event) =>
                                        setData(
                                            "specialization_id",
                                            event.target.value,
                                        )
                                    }
                                    className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                >
                                    <option value="">Any specialty</option>
                                    {options.specializations.map(
                                        (specialization) => (
                                            <option
                                                key={specialization.id}
                                                value={specialization.id}
                                            >
                                                {specialization.name}
                                            </option>
                                        ),
                                    )}
                                </select>
                            </div>

                            <div>
                                <label
                                    htmlFor="language_id"
                                    className="text-sm font-medium text-gray-700"
                                >
                                    Language
                                </label>

                                <select
                                    id="language_id"
                                    value={data.language_id}
                                    onChange={(event) =>
                                        setData(
                                            "language_id",
                                            event.target.value,
                                        )
                                    }
                                    className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                >
                                    <option value="">Any language</option>
                                    {options.languages.map((language) => (
                                        <option
                                            key={language.id}
                                            value={language.id}
                                        >
                                            {language.name}
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

                        <div className="mt-4 grid gap-4 lg:grid-cols-5">
                            <div>
                                <label
                                    htmlFor="availability_day"
                                    className="text-sm font-medium text-gray-700"
                                >
                                    Availability day
                                </label>

                                <select
                                    id="availability_day"
                                    value={data.availability_day}
                                    onChange={(event) =>
                                        setData(
                                            "availability_day",
                                            event.target.value,
                                        )
                                    }
                                    className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                >
                                    <option value="">Any day</option>
                                    {options.days.map((day) => (
                                        <option
                                            key={day.value}
                                            value={day.value}
                                        >
                                            {day.label}
                                        </option>
                                    ))}
                                </select>
                            </div>

                            <div className="lg:col-span-4">
                                <div className="rounded-lg border border-dashed border-gray-300 bg-gray-50 p-4">
                                    <p className="text-sm text-gray-600">
                                        Fee filter will be enabled after
                                        counsellor service-offerings are mapped.
                                        Current service prices exist globally,
                                        but counsellor-specific offerings are
                                        not connected yet.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div className="mt-4 flex flex-wrap justify-end gap-3">
                            <SecondaryButton
                                type="button"
                                onClick={resetFilters}
                            >
                                Reset
                            </SecondaryButton>

                            <PrimaryButton disabled={processing}>
                                Search counsellors
                            </PrimaryButton>
                        </div>
                    </form>

                    <div className="overflow-hidden bg-white px-6 py-4 shadow-sm sm:rounded-lg">
                        <p className="text-sm text-gray-600">
                            Showing{" "}
                            <span className="font-semibold text-gray-900">
                                {counsellors.total}
                            </span>{" "}
                            matching counsellor
                            {counsellors.total === 1 ? "" : "s"}.
                        </p>
                    </div>

                    {counsellors.data.length === 0 ? (
                        <div className="overflow-hidden bg-white p-8 text-center shadow-sm sm:rounded-lg">
                            <h3 className="text-base font-semibold text-gray-900">
                                No counsellors found
                            </h3>
                            <p className="mt-2 text-sm text-gray-500">
                                Try changing the specialty, language, mode, or
                                availability filters.
                            </p>
                        </div>
                    ) : (
                        <div className="grid gap-6 lg:grid-cols-3">
                            {counsellors.data.map((counsellor) => (
                                <CounsellorCard
                                    key={counsellor.id}
                                    counsellor={counsellor}
                                />
                            ))}
                        </div>
                    )}

                    <Pagination links={counsellors.links} />
                </div>
            </div>
        </ClientLayout>
    );
}
