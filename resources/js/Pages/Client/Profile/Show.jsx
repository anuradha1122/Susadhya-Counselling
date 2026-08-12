import PrimaryButton from "@/Components/PrimaryButton";
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
            <dd className="mt-1 text-sm text-gray-900">{formatValue(value)}</dd>
        </div>
    );
}

export default function Show({ clientProfile }) {
    const missingFields = clientProfile.missing_fields ?? [];
    const isComplete = clientProfile.is_complete;

    return (
        <ClientLayout
            header={
                <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 className="text-xl font-semibold leading-tight text-gray-800">
                            Client Profile
                        </h2>
                        <p className="mt-1 text-sm text-gray-500">
                            Review your personal profile and contact details.
                        </p>
                    </div>

                    <Link href={route("client.profile.edit")}>
                        <PrimaryButton>Edit profile</PrimaryButton>
                    </Link>
                </div>
            }
        >
            <Head title="Client Profile" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                    <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <div className="p-6">
                            <div className="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                                <div>
                                    <p className="text-sm font-medium uppercase tracking-wide text-gray-500">
                                        Completion
                                    </p>

                                    <h3 className="mt-1 text-2xl font-semibold text-gray-900">
                                        {isComplete
                                            ? "Profile complete"
                                            : "Profile incomplete"}
                                    </h3>

                                    <p className="mt-2 text-sm text-gray-600">
                                        Complete profile information helps
                                        prepare safer counselling workflows.
                                        Clinical history belongs in intake and
                                        case modules, not this profile.
                                    </p>
                                </div>

                                <div
                                    className={`rounded-full px-4 py-2 text-sm font-semibold ${
                                        isComplete
                                            ? "bg-green-100 text-green-800"
                                            : "bg-amber-100 text-amber-800"
                                    }`}
                                >
                                    {isComplete ? "Complete" : "Incomplete"}
                                </div>
                            </div>

                            {missingFields.length > 0 && (
                                <div className="mt-6 rounded-lg border border-amber-200 bg-amber-50 p-4">
                                    <p className="text-sm font-semibold text-amber-900">
                                        Missing profile items
                                    </p>

                                    <ul className="mt-3 list-inside list-disc space-y-1 text-sm text-amber-800">
                                        {missingFields.map((field) => (
                                            <li key={field}>
                                                {formatValue(field)}
                                            </li>
                                        ))}
                                    </ul>
                                </div>
                            )}
                        </div>
                    </div>

                    <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <div className="border-b border-gray-200 px-6 py-4">
                            <h3 className="text-lg font-semibold text-gray-900">
                                Basic details
                            </h3>
                        </div>

                        <dl className="grid gap-6 p-6 sm:grid-cols-2 lg:grid-cols-3">
                            <DetailItem
                                label="First name"
                                value={clientProfile.first_name}
                            />
                            <DetailItem
                                label="Last name"
                                value={clientProfile.last_name}
                            />
                            <DetailItem
                                label="Preferred name"
                                value={clientProfile.preferred_name}
                            />
                            <DetailItem
                                label="Date of birth"
                                value={clientProfile.date_of_birth}
                            />
                            <DetailItem
                                label="Gender"
                                value={clientProfile.gender}
                            />
                            <DetailItem
                                label="Pronouns"
                                value={clientProfile.pronouns}
                            />
                            <DetailItem
                                label="Occupation"
                                value={clientProfile.occupation}
                            />
                            <DetailItem
                                label="Marital status"
                                value={clientProfile.marital_status}
                            />
                            <DetailItem
                                label="Account status"
                                value={clientProfile.status}
                            />
                        </dl>
                    </div>

                    <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <div className="border-b border-gray-200 px-6 py-4">
                            <h3 className="text-lg font-semibold text-gray-900">
                                Contact details
                            </h3>
                        </div>

                        <dl className="grid gap-6 p-6 sm:grid-cols-2 lg:grid-cols-3">
                            <DetailItem
                                label="Email"
                                value={clientProfile.user.email}
                            />
                            <DetailItem
                                label="Phone"
                                value={clientProfile.phone}
                            />
                            <DetailItem
                                label="Alternate phone"
                                value={clientProfile.alternate_phone}
                            />
                            <DetailItem
                                label="Preferred language"
                                value={clientProfile.preferred_language}
                            />
                            <DetailItem
                                label="Preferred contact method"
                                value={clientProfile.preferred_contact_method}
                            />
                        </dl>
                    </div>

                    <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <div className="border-b border-gray-200 px-6 py-4">
                            <h3 className="text-lg font-semibold text-gray-900">
                                Address
                            </h3>
                        </div>

                        <dl className="grid gap-6 p-6 sm:grid-cols-2 lg:grid-cols-3">
                            <DetailItem
                                label="Address line 1"
                                value={clientProfile.address_line_1}
                            />
                            <DetailItem
                                label="Address line 2"
                                value={clientProfile.address_line_2}
                            />
                            <DetailItem
                                label="City"
                                value={clientProfile.city}
                            />
                            <DetailItem
                                label="District"
                                value={clientProfile.district}
                            />
                            <DetailItem
                                label="Province"
                                value={clientProfile.province}
                            />
                            <DetailItem
                                label="Postal code"
                                value={clientProfile.postal_code}
                            />
                        </dl>
                    </div>
                </div>
            </div>
        </ClientLayout>
    );
}
