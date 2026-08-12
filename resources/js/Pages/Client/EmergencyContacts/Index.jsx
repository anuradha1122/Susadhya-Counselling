import DangerButton from "@/Components/DangerButton";
import PrimaryButton from "@/Components/PrimaryButton";
import SecondaryButton from "@/Components/SecondaryButton";
import ClientLayout from "@/Layouts/ClientLayout";
import { Head, Link, useForm } from "@inertiajs/react";

function formatValue(value) {
    if (value === null || value === undefined || value === "") {
        return "Not provided";
    }

    return value;
}

export default function Index({ contacts, clientProfile }) {
    const { delete: destroy, processing } = useForm();

    const removeContact = (contact) => {
        const confirmed = window.confirm(
            `Remove emergency contact "${contact.name}"?`,
        );

        if (!confirmed) {
            return;
        }

        destroy(route("client.emergency-contacts.destroy", contact.id), {
            preserveScroll: true,
        });
    };

    return (
        <ClientLayout
            header={
                <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 className="text-xl font-semibold leading-tight text-gray-800">
                            Emergency Contacts
                        </h2>
                        <p className="mt-1 text-sm text-gray-500">
                            Manage the people who may be contacted for
                            safety-related emergencies.
                        </p>
                    </div>

                    <Link href={route("client.emergency-contacts.create")}>
                        <PrimaryButton>Add contact</PrimaryButton>
                    </Link>
                </div>
            }
        >
            <Head title="Emergency Contacts" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                    {!clientProfile.emergency_contact_permission && (
                        <div className="rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
                            Emergency-contact permission is currently not
                            enabled. You can still prepare contacts here, and
                            permission will be managed in the privacy settings
                            section.
                        </div>
                    )}

                    <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <div className="border-b border-gray-200 px-6 py-4">
                            <h3 className="text-lg font-semibold text-gray-900">
                                Saved contacts
                            </h3>
                        </div>

                        {contacts.length === 0 ? (
                            <div className="p-6 text-sm text-gray-600">
                                No emergency contacts have been added yet.
                                Please add at least one trusted contact if you
                                want the platform to support emergency contact
                                workflows.
                            </div>
                        ) : (
                            <div className="divide-y divide-gray-200">
                                {contacts.map((contact) => (
                                    <div key={contact.id} className="p-6">
                                        <div className="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                                            <div>
                                                <div className="flex flex-wrap items-center gap-2">
                                                    <h4 className="text-lg font-semibold text-gray-900">
                                                        {contact.name}
                                                    </h4>

                                                    {contact.is_primary && (
                                                        <span className="rounded-full bg-indigo-100 px-3 py-1 text-xs font-semibold text-indigo-700">
                                                            Primary
                                                        </span>
                                                    )}

                                                    {contact.may_contact_in_emergency && (
                                                        <span className="rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-700">
                                                            May contact
                                                        </span>
                                                    )}
                                                </div>

                                                <dl className="mt-4 grid gap-4 text-sm sm:grid-cols-2 lg:grid-cols-4">
                                                    <div>
                                                        <dt className="font-medium text-gray-500">
                                                            Relationship
                                                        </dt>
                                                        <dd className="mt-1 text-gray-900">
                                                            {formatValue(
                                                                contact.relationship,
                                                            )}
                                                        </dd>
                                                    </div>

                                                    <div>
                                                        <dt className="font-medium text-gray-500">
                                                            Phone
                                                        </dt>
                                                        <dd className="mt-1 text-gray-900">
                                                            {formatValue(
                                                                contact.phone,
                                                            )}
                                                        </dd>
                                                    </div>

                                                    <div>
                                                        <dt className="font-medium text-gray-500">
                                                            Alternate phone
                                                        </dt>
                                                        <dd className="mt-1 text-gray-900">
                                                            {formatValue(
                                                                contact.alternate_phone,
                                                            )}
                                                        </dd>
                                                    </div>

                                                    <div>
                                                        <dt className="font-medium text-gray-500">
                                                            Email
                                                        </dt>
                                                        <dd className="mt-1 text-gray-900">
                                                            {formatValue(
                                                                contact.email,
                                                            )}
                                                        </dd>
                                                    </div>
                                                </dl>
                                            </div>

                                            <div className="flex flex-wrap gap-2">
                                                <Link
                                                    href={route(
                                                        "client.emergency-contacts.edit",
                                                        contact.id,
                                                    )}
                                                >
                                                    <SecondaryButton>
                                                        Edit
                                                    </SecondaryButton>
                                                </Link>

                                                <DangerButton
                                                    type="button"
                                                    disabled={processing}
                                                    onClick={() =>
                                                        removeContact(contact)
                                                    }
                                                >
                                                    Remove
                                                </DangerButton>
                                            </div>
                                        </div>
                                    </div>
                                ))}
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
                    </div>
                </div>
            </div>
        </ClientLayout>
    );
}
