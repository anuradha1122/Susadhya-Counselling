import PrimaryButton from "@/Components/PrimaryButton";
import SecondaryButton from "@/Components/SecondaryButton";
import ClientLayout from "@/Layouts/ClientLayout";
import { Head, Link } from "@inertiajs/react";

function booleanLabel(value) {
    return value ? "Enabled" : "Disabled";
}

function formatConsentType(value) {
    return String(value)
        .replaceAll("_", " ")
        .replace(/\b\w/g, (character) => character.toUpperCase());
}

function DetailItem({ label, value }) {
    return (
        <div>
            <dt className="text-sm font-medium text-gray-500">{label}</dt>
            <dd className="mt-1 text-sm font-semibold text-gray-900">
                {value}
            </dd>
        </div>
    );
}

export default function Show({ privacy, consents }) {
    return (
        <ClientLayout
            header={
                <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 className="text-xl font-semibold leading-tight text-gray-800">
                            Privacy and Consent
                        </h2>
                        <p className="mt-1 text-sm text-gray-500">
                            Review consent history and privacy preferences for
                            your client account.
                        </p>
                    </div>

                    <Link href={route("client.privacy.edit")}>
                        <PrimaryButton>Edit privacy settings</PrimaryButton>
                    </Link>
                </div>
            }
        >
            <Head title="Privacy and Consent" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                    <div className="rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
                        Final legal wording for terms, privacy policy, and
                        consent explanations must be approved before production
                        launch. This module records acceptance and preferences;
                        it does not replace legal review, because apparently
                        courts are picky like that.
                    </div>

                    <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <div className="border-b border-gray-200 px-6 py-4">
                            <h3 className="text-lg font-semibold text-gray-900">
                                Required acceptances
                            </h3>
                        </div>

                        <dl className="grid gap-6 p-6 sm:grid-cols-2">
                            <DetailItem
                                label="Terms accepted at"
                                value={
                                    privacy.terms_accepted_at ?? "Not recorded"
                                }
                            />
                            <DetailItem
                                label="Privacy policy accepted at"
                                value={
                                    privacy.privacy_policy_accepted_at ??
                                    "Not recorded"
                                }
                            />
                        </dl>
                    </div>

                    <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <div className="border-b border-gray-200 px-6 py-4">
                            <h3 className="text-lg font-semibold text-gray-900">
                                Current privacy settings
                            </h3>
                        </div>

                        <dl className="grid gap-6 p-6 sm:grid-cols-2 lg:grid-cols-3">
                            <DetailItem
                                label="Communication consent"
                                value={booleanLabel(
                                    privacy.communication_consent,
                                )}
                            />
                            <DetailItem
                                label="Communication consent at"
                                value={
                                    privacy.communication_consent_at ??
                                    "Not recorded"
                                }
                            />
                            <DetailItem
                                label="Emergency contact permission"
                                value={booleanLabel(
                                    privacy.emergency_contact_permission,
                                )}
                            />
                            <DetailItem
                                label="Emergency permission at"
                                value={
                                    privacy.emergency_contact_permission_at ??
                                    "Not recorded"
                                }
                            />
                            <DetailItem
                                label="Email updates"
                                value={booleanLabel(
                                    privacy.allow_email_updates,
                                )}
                            />
                            <DetailItem
                                label="SMS updates"
                                value={booleanLabel(privacy.allow_sms_updates)}
                            />
                            <DetailItem
                                label="WhatsApp updates"
                                value={booleanLabel(
                                    privacy.allow_whatsapp_updates,
                                )}
                            />
                            <DetailItem
                                label="Share profile with assigned counsellor"
                                value={booleanLabel(
                                    privacy.share_profile_with_assigned_counsellor,
                                )}
                            />
                        </dl>
                    </div>

                    <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <div className="border-b border-gray-200 px-6 py-4">
                            <h3 className="text-lg font-semibold text-gray-900">
                                Consent history
                            </h3>
                        </div>

                        {consents.length === 0 ? (
                            <div className="p-6 text-sm text-gray-600">
                                No consent records have been saved yet.
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
                                        {consents.map((consent) => (
                                            <tr key={consent.id}>
                                                <td className="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900">
                                                    {formatConsentType(
                                                        consent.consent_type,
                                                    )}
                                                </td>
                                                <td className="whitespace-nowrap px-6 py-4 text-sm text-gray-600">
                                                    {consent.version}
                                                </td>
                                                <td className="whitespace-nowrap px-6 py-4 text-sm text-gray-600">
                                                    {consent.accepted_at}
                                                </td>
                                                <td className="whitespace-nowrap px-6 py-4 text-sm text-gray-600">
                                                    {consent.source ??
                                                        "Not recorded"}
                                                </td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </div>
                        )}
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

                        <Link href={route("client.preferences.show")}>
                            <SecondaryButton type="button">
                                Counselling preferences
                            </SecondaryButton>
                        </Link>
                    </div>
                </div>
            </div>
        </ClientLayout>
    );
}
