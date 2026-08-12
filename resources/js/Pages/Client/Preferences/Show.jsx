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

function DetailItem({ label, value }) {
    return (
        <div>
            <dt className="text-sm font-medium text-gray-500">{label}</dt>
            <dd className="mt-1 whitespace-pre-line text-sm text-gray-900">
                {formatValue(value)}
            </dd>
        </div>
    );
}

export default function Show({ preference, clientProfile }) {
    return (
        <ClientLayout
            header={
                <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 className="text-xl font-semibold leading-tight text-gray-800">
                            Counselling Preferences
                        </h2>
                        <p className="mt-1 text-sm text-gray-500">
                            Review your counselling mode, language,
                            availability, and accessibility preferences.
                        </p>
                    </div>

                    <Link href={route("client.preferences.edit")}>
                        <PrimaryButton>Edit preferences</PrimaryButton>
                    </Link>
                </div>
            }
        >
            <Head title="Counselling Preferences" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                    <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <div className="border-b border-gray-200 px-6 py-4">
                            <h3 className="text-lg font-semibold text-gray-900">
                                Matching preferences
                            </h3>
                        </div>

                        <dl className="grid gap-6 p-6 sm:grid-cols-2 lg:grid-cols-3">
                            <DetailItem
                                label="Preferred counselling mode"
                                value={preference.preferred_counselling_mode}
                            />
                            <DetailItem
                                label="Preferred counsellor gender"
                                value={preference.preferred_counsellor_gender}
                            />
                            <DetailItem
                                label="Preferred language"
                                value={preference.preferred_language}
                            />
                        </dl>
                    </div>

                    <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <div className="border-b border-gray-200 px-6 py-4">
                            <h3 className="text-lg font-semibold text-gray-900">
                                Notes
                            </h3>
                        </div>

                        <dl className="grid gap-6 p-6">
                            <DetailItem
                                label="General availability notes"
                                value={preference.general_availability_notes}
                            />
                            <DetailItem
                                label="Accessibility requirements"
                                value={preference.accessibility_requirements}
                            />
                            <DetailItem
                                label="Additional preferences"
                                value={preference.additional_preferences}
                            />
                        </dl>
                    </div>

                    <div className="rounded-lg border border-blue-200 bg-blue-50 p-4 text-sm text-blue-900">
                        These preferences help search and appointment workflows.
                        Do not enter detailed clinical history here. Clinical
                        information belongs in the intake and case record
                        modules, because dumping everything into one profile
                        table is how systems become landfill.
                    </div>

                    <div className="flex flex-wrap gap-3">
                        <Link href={route("client.dashboard")}>
                            <SecondaryButton type="button">
                                Back to dashboard
                            </SecondaryButton>
                        </Link>

                        <Link href={route("client.profile.show")}>
                            <SecondaryButton type="button">
                                View profile
                            </SecondaryButton>
                        </Link>

                        <Link href={route("client.emergency-contacts.index")}>
                            <SecondaryButton type="button">
                                Emergency contacts
                            </SecondaryButton>
                        </Link>
                    </div>
                </div>
            </div>
        </ClientLayout>
    );
}
