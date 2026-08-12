import PrimaryButton from "@/Components/PrimaryButton";
import SecondaryButton from "@/Components/SecondaryButton";
import ClientLayout from "@/Layouts/ClientLayout";
import { Head, Link } from "@inertiajs/react";

export default function Dashboard({ clientProfile }) {
    const missingFields = clientProfile?.missing_fields ?? [];
    const isComplete = missingFields.length === 0;

    return (
        <ClientLayout
            header={
                <div>
                    <h2 className="text-xl font-semibold leading-tight text-gray-800">
                        Client Dashboard
                    </h2>
                    <p className="mt-1 text-sm text-gray-500">
                        Manage your counselling profile, preferences, and
                        emergency contact details.
                    </p>
                </div>
            }
        >
            <Head title="Client Dashboard" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                    <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <div className="p-6">
                            <div className="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                                <div>
                                    <p className="text-sm font-medium uppercase tracking-wide text-gray-500">
                                        Profile status
                                    </p>

                                    <h3 className="mt-1 text-2xl font-semibold text-gray-900">
                                        {isComplete
                                            ? "Your profile is complete"
                                            : "Your profile needs attention"}
                                    </h3>

                                    <p className="mt-2 max-w-2xl text-sm text-gray-600">
                                        A complete profile helps the platform
                                        prepare safer counselling workflows
                                        without storing clinical history in your
                                        general account profile.
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

                            {!clientProfile && (
                                <div className="mt-6 rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">
                                    Your client profile has not been created
                                    yet. This should only happen for older test
                                    accounts or manually assigned users.
                                </div>
                            )}

                            {clientProfile && missingFields.length > 0 && (
                                <div className="mt-6 rounded-lg border border-gray-200 bg-gray-50 p-4">
                                    <p className="text-sm font-semibold text-gray-800">
                                        Missing profile items
                                    </p>

                                    <ul className="mt-3 list-inside list-disc space-y-1 text-sm text-gray-600">
                                        {missingFields.map((field) => (
                                            <li key={field}>
                                                {field
                                                    .replaceAll("_", " ")
                                                    .replace(
                                                        /\b\w/g,
                                                        (character) =>
                                                            character.toUpperCase(),
                                                    )}
                                            </li>
                                        ))}
                                    </ul>
                                </div>
                            )}

                            <div className="mt-6 grid gap-4 md:grid-cols-3">
                                <div className="rounded-lg border border-gray-200 p-4">
                                    <p className="text-sm font-medium text-gray-500">
                                        Emergency contacts
                                    </p>
                                    <p className="mt-2 text-2xl font-semibold text-gray-900">
                                        {clientProfile?.emergency_contacts_count ??
                                            0}
                                    </p>
                                </div>

                                <div className="rounded-lg border border-gray-200 p-4">
                                    <p className="text-sm font-medium text-gray-500">
                                        Counselling preferences
                                    </p>
                                    <p className="mt-2 text-sm font-semibold text-gray-900">
                                        {clientProfile?.has_preferences
                                            ? "Added"
                                            : "Not added yet"}
                                    </p>
                                </div>

                                <div className="rounded-lg border border-gray-200 p-4">
                                    <p className="text-sm font-medium text-gray-500">
                                        Account state
                                    </p>
                                    <p className="mt-2 text-sm font-semibold capitalize text-gray-900">
                                        {clientProfile?.status ?? "Pending"}
                                    </p>
                                </div>
                            </div>

                            {clientProfile && (
                                <div className="mt-6 flex flex-wrap gap-3">
                                    <Link href={route("client.profile.show")}>
                                        <PrimaryButton>
                                            View client profile
                                        </PrimaryButton>
                                    </Link>

                                    <Link href={route("client.profile.edit")}>
                                        <SecondaryButton>
                                            Edit profile
                                        </SecondaryButton>
                                    </Link>

                                    <Link
                                        href={route(
                                            "client.emergency-contacts.index",
                                        )}
                                    >
                                        <SecondaryButton>
                                            Emergency contacts
                                        </SecondaryButton>
                                    </Link>

                                    <Link
                                        href={route("client.preferences.show")}
                                    >
                                        <SecondaryButton>
                                            Counselling preferences
                                        </SecondaryButton>
                                    </Link>

                                    <Link href={route("client.privacy.show")}>
                                        <SecondaryButton>
                                            Privacy and consent
                                        </SecondaryButton>
                                    </Link>
                                </div>
                            )}
                        </div>
                    </div>

                    <div className="text-sm text-gray-500">
                        <Link
                            href={route("profile.edit")}
                            className="font-medium text-indigo-600 hover:text-indigo-500"
                        >
                            Manage account login profile
                        </Link>
                    </div>
                </div>
            </div>
        </ClientLayout>
    );
}
