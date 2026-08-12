import Checkbox from "@/Components/Checkbox";
import InputError from "@/Components/InputError";
import PrimaryButton from "@/Components/PrimaryButton";
import SecondaryButton from "@/Components/SecondaryButton";
import ClientLayout from "@/Layouts/ClientLayout";
import { Head, Link, useForm } from "@inertiajs/react";

function ToggleField({ label, description, checked, onChange, error }) {
    return (
        <div className="rounded-lg border border-gray-200 p-4">
            <label className="flex items-start gap-3">
                <Checkbox checked={checked} onChange={onChange} />

                <span>
                    <span className="block text-sm font-semibold text-gray-900">
                        {label}
                    </span>
                    <span className="mt-1 block text-sm text-gray-600">
                        {description}
                    </span>
                </span>
            </label>

            <InputError message={error} className="mt-2" />
        </div>
    );
}

export default function Edit({ privacy }) {
    const { data, setData, patch, processing, errors } = useForm({
        communication_consent: privacy.communication_consent ?? false,
        emergency_contact_permission:
            privacy.emergency_contact_permission ?? false,
        allow_email_updates: privacy.allow_email_updates ?? true,
        allow_sms_updates: privacy.allow_sms_updates ?? false,
        allow_whatsapp_updates: privacy.allow_whatsapp_updates ?? false,
        share_profile_with_assigned_counsellor:
            privacy.share_profile_with_assigned_counsellor ?? true,
    });

    const submit = (event) => {
        event.preventDefault();

        patch(route("client.privacy.update"), {
            preserveScroll: true,
        });
    };

    return (
        <ClientLayout
            header={
                <div>
                    <h2 className="text-xl font-semibold leading-tight text-gray-800">
                        Edit Privacy and Consent
                    </h2>
                    <p className="mt-1 text-sm text-gray-500">
                        Manage communication, emergency-contact, and profile
                        sharing preferences.
                    </p>
                </div>
            }
        >
            <Head title="Edit Privacy and Consent" />

            <div className="py-12">
                <div className="mx-auto max-w-4xl sm:px-6 lg:px-8">
                    <form
                        onSubmit={submit}
                        className="space-y-6 overflow-hidden bg-white p-6 shadow-sm sm:rounded-lg"
                    >
                        <section>
                            <h3 className="text-lg font-semibold text-gray-900">
                                Consent settings
                            </h3>

                            <div className="mt-6 space-y-4">
                                <ToggleField
                                    label="Communication consent"
                                    description="Allow the platform to send appointment and service-related communication using your selected contact methods."
                                    checked={data.communication_consent}
                                    error={errors.communication_consent}
                                    onChange={(event) =>
                                        setData(
                                            "communication_consent",
                                            event.target.checked,
                                        )
                                    }
                                />

                                <ToggleField
                                    label="Emergency contact permission"
                                    description="Allow authorised platform staff to use your saved emergency contact details for safety-related workflows."
                                    checked={data.emergency_contact_permission}
                                    error={errors.emergency_contact_permission}
                                    onChange={(event) =>
                                        setData(
                                            "emergency_contact_permission",
                                            event.target.checked,
                                        )
                                    }
                                />
                            </div>
                        </section>

                        <section className="border-t border-gray-200 pt-6">
                            <h3 className="text-lg font-semibold text-gray-900">
                                Contact preferences
                            </h3>

                            <div className="mt-6 space-y-4">
                                <ToggleField
                                    label="Email updates"
                                    description="Allow updates by email where suitable."
                                    checked={data.allow_email_updates}
                                    error={errors.allow_email_updates}
                                    onChange={(event) =>
                                        setData(
                                            "allow_email_updates",
                                            event.target.checked,
                                        )
                                    }
                                />

                                <ToggleField
                                    label="SMS updates"
                                    description="Allow updates by SMS where SMS delivery is configured."
                                    checked={data.allow_sms_updates}
                                    error={errors.allow_sms_updates}
                                    onChange={(event) =>
                                        setData(
                                            "allow_sms_updates",
                                            event.target.checked,
                                        )
                                    }
                                />

                                <ToggleField
                                    label="WhatsApp updates"
                                    description="Allow WhatsApp updates where WhatsApp delivery is configured."
                                    checked={data.allow_whatsapp_updates}
                                    error={errors.allow_whatsapp_updates}
                                    onChange={(event) =>
                                        setData(
                                            "allow_whatsapp_updates",
                                            event.target.checked,
                                        )
                                    }
                                />
                            </div>
                        </section>

                        <section className="border-t border-gray-200 pt-6">
                            <h3 className="text-lg font-semibold text-gray-900">
                                Profile sharing
                            </h3>

                            <div className="mt-6 space-y-4">
                                <ToggleField
                                    label="Share profile with assigned counsellor"
                                    description="Allow your assigned counsellor to see non-clinical profile details needed for appointment preparation."
                                    checked={
                                        data.share_profile_with_assigned_counsellor
                                    }
                                    error={
                                        errors.share_profile_with_assigned_counsellor
                                    }
                                    onChange={(event) =>
                                        setData(
                                            "share_profile_with_assigned_counsellor",
                                            event.target.checked,
                                        )
                                    }
                                />
                            </div>
                        </section>

                        <div className="rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
                            Disabling communication consent may limit reminders
                            and service updates. Emergency-contact permission is
                            separate from saving emergency contact details.
                        </div>

                        <div className="flex items-center justify-end gap-3 border-t border-gray-200 pt-6">
                            <Link href={route("client.privacy.show")}>
                                <SecondaryButton type="button">
                                    Cancel
                                </SecondaryButton>
                            </Link>

                            <PrimaryButton disabled={processing}>
                                Save privacy settings
                            </PrimaryButton>
                        </div>
                    </form>
                </div>
            </div>
        </ClientLayout>
    );
}
