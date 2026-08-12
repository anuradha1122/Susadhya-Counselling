import DangerButton from "@/Components/DangerButton";
import PrimaryButton from "@/Components/PrimaryButton";
import SecondaryButton from "@/Components/SecondaryButton";
import AdminLayout from "@/Layouts/AdminLayout";
import { Head, Link, router } from "@inertiajs/react";

function formatValue(value) {
    if (value === null || value === undefined || value === "") {
        return "Not provided";
    }

    return String(value)
        .replaceAll("_", " ")
        .replace(/\b\w/g, (character) => character.toUpperCase());
}

function booleanLabel(value) {
    return value ? "Enabled" : "Disabled";
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

function StatusBadge({ status }) {
    const classes = {
        active: "bg-green-100 text-green-800",
        inactive: "bg-amber-100 text-amber-800",
        archived: "bg-gray-100 text-gray-800",
    };

    return (
        <span
            className={`rounded-full px-3 py-1 text-xs font-semibold ${
                classes[status] ?? "bg-gray-100 text-gray-800"
            }`}
        >
            {formatValue(status)}
        </span>
    );
}

export default function Show({ clientProfile }) {
    const changeStatus = (status) => {
        router.patch(
            route("admin.clients.status", clientProfile.id),
            {
                status,
            },
            {
                preserveScroll: true,
            },
        );
    };

    const archiveClient = () => {
        const confirmed = window.confirm(
            `Archive client "${clientProfile.full_name}"? Their user account will be deactivated.`,
        );

        if (!confirmed) {
            return;
        }

        router.delete(route("admin.clients.destroy", clientProfile.id), {
            preserveScroll: true,
        });
    };

    const restoreClient = () => {
        router.patch(
            route("admin.clients.restore", clientProfile.id),
            {},
            {
                preserveScroll: true,
            },
        );
    };

    return (
        <AdminLayout
            header={
                <div className="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <h2 className="text-xl font-semibold leading-tight text-gray-800">
                            {clientProfile.full_name}
                        </h2>
                        <p className="mt-1 text-sm text-gray-500">
                            Admin review of client profile, contacts,
                            preferences, and consent history.
                        </p>
                    </div>

                    <div className="flex flex-wrap gap-2">
                        <Link href={route("admin.clients.index")}>
                            <SecondaryButton>Back to clients</SecondaryButton>
                        </Link>

                        {clientProfile.status === "active" && (
                            <SecondaryButton
                                type="button"
                                onClick={() => changeStatus("inactive")}
                            >
                                Deactivate
                            </SecondaryButton>
                        )}

                        {clientProfile.status === "inactive" && (
                            <PrimaryButton
                                type="button"
                                onClick={() => changeStatus("active")}
                            >
                                Activate
                            </PrimaryButton>
                        )}

                        {clientProfile.status !== "archived" && (
                            <DangerButton type="button" onClick={archiveClient}>
                                Archive
                            </DangerButton>
                        )}

                        {clientProfile.status === "archived" && (
                            <PrimaryButton
                                type="button"
                                onClick={restoreClient}
                            >
                                Restore
                            </PrimaryButton>
                        )}
                    </div>
                </div>
            }
        >
            <Head title={clientProfile.full_name} />

            <div className="py-12">
                <div className="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                    <div>
                        <Link href={route("admin.clients.index")}>
                            <SecondaryButton type="button">Back to clients</SecondaryButton>
                        </Link>
                    </div>
                    <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <div className="p-6">
                            <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <p className="text-sm font-medium uppercase tracking-wide text-gray-500">
                                        Client status
                                    </p>

                                    <div className="mt-2 flex flex-wrap items-center gap-3">
                                        <StatusBadge
                                            status={clientProfile.status}
                                        />

                                        <span
                                            className={`rounded-full px-3 py-1 text-xs font-semibold ${
                                                clientProfile.is_complete
                                                    ? "bg-indigo-100 text-indigo-800"
                                                    : "bg-red-100 text-red-800"
                                            }`}
                                        >
                                            {clientProfile.is_complete
                                                ? "Profile complete"
                                                : "Profile incomplete"}
                                        </span>
                                    </div>
                                </div>

                                <div className="text-sm text-gray-500">
                                    User account:{" "}
                                    <span className="font-semibold text-gray-900">
                                        {clientProfile.user.is_active
                                            ? "Active"
                                            : "Inactive"}
                                    </span>
                                </div>
                            </div>

                            {clientProfile.missing_fields.length > 0 && (
                                <div className="mt-6 rounded-lg border border-amber-200 bg-amber-50 p-4">
                                    <p className="text-sm font-semibold text-amber-900">
                                        Missing profile fields
                                    </p>

                                    <ul className="mt-3 list-inside list-disc space-y-1 text-sm text-amber-800">
                                        {clientProfile.missing_fields.map(
                                            (field) => (
                                                <li key={field}>
                                                    {formatValue(field)}
                                                </li>
                                            ),
                                        )}
                                    </ul>
                                </div>
                            )}
                        </div>
                    </div>

                    <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <div className="border-b border-gray-200 px-6 py-4">
                            <h3 className="text-lg font-semibold text-gray-900">
                                Account details
                            </h3>
                        </div>

                        <dl className="grid gap-6 p-6 sm:grid-cols-2 lg:grid-cols-3">
                            <DetailItem
                                label="Account name"
                                value={clientProfile.user.name}
                            />
                            <DetailItem
                                label="Email"
                                value={clientProfile.user.email}
                            />
                            <DetailItem
                                label="Phone"
                                value={clientProfile.user.phone}
                            />
                            <DetailItem
                                label="Email verified at"
                                value={clientProfile.user.email_verified_at}
                            />
                            <DetailItem
                                label="Last login"
                                value={clientProfile.user.last_login_at}
                            />
                            <DetailItem
                                label="Registered at"
                                value={clientProfile.user.created_at}
                            />
                        </dl>
                    </div>

                    <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <div className="border-b border-gray-200 px-6 py-4">
                            <h3 className="text-lg font-semibold text-gray-900">
                                Profile details
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
                                label="Preferred language"
                                value={clientProfile.preferred_language}
                            />
                            <DetailItem
                                label="Preferred contact method"
                                value={clientProfile.preferred_contact_method}
                            />
                            <DetailItem
                                label="Alternate phone"
                                value={clientProfile.alternate_phone}
                            />
                            <DetailItem
                                label="Profile completed at"
                                value={clientProfile.profile_completed_at}
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

                    <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <div className="border-b border-gray-200 px-6 py-4">
                            <h3 className="text-lg font-semibold text-gray-900">
                                Counselling preferences
                            </h3>
                        </div>

                        {clientProfile.preference ? (
                            <dl className="grid gap-6 p-6 sm:grid-cols-2 lg:grid-cols-3">
                                <DetailItem
                                    label="Preferred mode"
                                    value={
                                        clientProfile.preference
                                            .preferred_counselling_mode
                                    }
                                />
                                <DetailItem
                                    label="Preferred counsellor gender"
                                    value={
                                        clientProfile.preference
                                            .preferred_counsellor_gender
                                    }
                                />
                                <DetailItem
                                    label="Preferred language"
                                    value={
                                        clientProfile.preference
                                            .preferred_language
                                    }
                                />
                                <DetailItem
                                    label="Availability notes"
                                    value={
                                        clientProfile.preference
                                            .general_availability_notes
                                    }
                                />
                                <DetailItem
                                    label="Accessibility requirements"
                                    value={
                                        clientProfile.preference
                                            .accessibility_requirements
                                    }
                                />
                                <DetailItem
                                    label="Additional preferences"
                                    value={
                                        clientProfile.preference
                                            .additional_preferences
                                    }
                                />
                            </dl>
                        ) : (
                            <div className="p-6 text-sm text-gray-600">
                                No counselling preferences saved yet.
                            </div>
                        )}
                    </div>

                    <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <div className="border-b border-gray-200 px-6 py-4">
                            <h3 className="text-lg font-semibold text-gray-900">
                                Emergency contacts
                            </h3>
                        </div>

                        {clientProfile.emergency_contacts.length === 0 ? (
                            <div className="p-6 text-sm text-gray-600">
                                No emergency contacts saved yet.
                            </div>
                        ) : (
                            <div className="divide-y divide-gray-200">
                                {clientProfile.emergency_contacts.map(
                                    (contact) => (
                                        <div key={contact.id} className="p-6">
                                            <div className="flex flex-wrap items-center gap-2">
                                                <h4 className="text-base font-semibold text-gray-900">
                                                    {contact.name}
                                                </h4>

                                                {contact.is_primary && (
                                                    <span className="rounded-full bg-indigo-100 px-3 py-1 text-xs font-semibold text-indigo-800">
                                                        Primary
                                                    </span>
                                                )}

                                                {contact.may_contact_in_emergency && (
                                                    <span className="rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-800">
                                                        May contact
                                                    </span>
                                                )}
                                            </div>

                                            <dl className="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                                                <DetailItem
                                                    label="Relationship"
                                                    value={contact.relationship}
                                                />
                                                <DetailItem
                                                    label="Phone"
                                                    value={contact.phone}
                                                />
                                                <DetailItem
                                                    label="Alternate phone"
                                                    value={
                                                        contact.alternate_phone
                                                    }
                                                />
                                                <DetailItem
                                                    label="Email"
                                                    value={contact.email}
                                                />
                                            </dl>
                                        </div>
                                    ),
                                )}
                            </div>
                        )}
                    </div>

                    <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <div className="border-b border-gray-200 px-6 py-4">
                            <h3 className="text-lg font-semibold text-gray-900">
                                Privacy and consent
                            </h3>
                        </div>

                        <dl className="grid gap-6 p-6 sm:grid-cols-2 lg:grid-cols-3">
                            <DetailItem
                                label="Terms accepted at"
                                value={clientProfile.terms_accepted_at}
                            />
                            <DetailItem
                                label="Privacy policy accepted at"
                                value={clientProfile.privacy_policy_accepted_at}
                            />
                            <DetailItem
                                label="Communication consent"
                                value={booleanLabel(
                                    clientProfile.communication_consent,
                                )}
                            />
                            <DetailItem
                                label="Communication consent at"
                                value={clientProfile.communication_consent_at}
                            />
                            <DetailItem
                                label="Emergency contact permission"
                                value={booleanLabel(
                                    clientProfile.emergency_contact_permission,
                                )}
                            />
                            <DetailItem
                                label="Emergency permission at"
                                value={
                                    clientProfile.emergency_contact_permission_at
                                }
                            />
                        </dl>
                    </div>

                    <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <div className="border-b border-gray-200 px-6 py-4">
                            <h3 className="text-lg font-semibold text-gray-900">
                                Consent history
                            </h3>
                        </div>

                        {clientProfile.consents.length === 0 ? (
                            <div className="p-6 text-sm text-gray-600">
                                No consent history recorded.
                            </div>
                        ) : (
                            <div className="overflow-x-auto">
                                <table className="min-w-full divide-y divide-gray-200">
                                    <thead className="bg-gray-50">
                                        <tr>
                                            <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                                Type
                                            </th>
                                            <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                                Version
                                            </th>
                                            <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                                Accepted at
                                            </th>
                                            <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                                Source
                                            </th>
                                        </tr>
                                    </thead>

                                    <tbody className="divide-y divide-gray-200 bg-white">
                                        {clientProfile.consents.map(
                                            (consent) => (
                                                <tr key={consent.id}>
                                                    <td className="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900">
                                                        {formatValue(
                                                            consent.consent_type,
                                                        )}
                                                    </td>
                                                    <td className="whitespace-nowrap px-6 py-4 text-sm text-gray-600">
                                                        {consent.version}
                                                    </td>
                                                    <td className="whitespace-nowrap px-6 py-4 text-sm text-gray-600">
                                                        {formatValue(
                                                            consent.accepted_at,
                                                        )}
                                                    </td>
                                                    <td className="whitespace-nowrap px-6 py-4 text-sm text-gray-600">
                                                        {formatValue(
                                                            consent.source,
                                                        )}
                                                    </td>
                                                </tr>
                                            ),
                                        )}
                                    </tbody>
                                </table>
                            </div>
                        )}
                    </div>
                </div>
            </div>
        </AdminLayout>
    );
}
