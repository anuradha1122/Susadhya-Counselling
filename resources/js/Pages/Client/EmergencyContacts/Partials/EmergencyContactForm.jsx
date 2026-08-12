import Checkbox from "@/Components/Checkbox";
import InputError from "@/Components/InputError";
import InputLabel from "@/Components/InputLabel";
import PrimaryButton from "@/Components/PrimaryButton";
import SecondaryButton from "@/Components/SecondaryButton";
import TextInput from "@/Components/TextInput";
import { Link, useForm } from "@inertiajs/react";

export default function EmergencyContactForm({
    contact,
    submitRoute,
    submitMethod,
    submitLabel,
}) {
    const { data, setData, post, patch, processing, errors } = useForm({
        name: contact.name ?? "",
        relationship: contact.relationship ?? "",
        phone: contact.phone ?? "",
        alternate_phone: contact.alternate_phone ?? "",
        email: contact.email ?? "",
        may_contact_in_emergency: contact.may_contact_in_emergency ?? true,
        is_primary: contact.is_primary ?? false,
    });

    const submit = (event) => {
        event.preventDefault();

        if (submitMethod === "patch") {
            patch(submitRoute, {
                preserveScroll: true,
            });

            return;
        }

        post(submitRoute, {
            preserveScroll: true,
        });
    };

    return (
        <form onSubmit={submit} className="space-y-6">
            <section className="grid gap-4 sm:grid-cols-2">
                <div>
                    <InputLabel htmlFor="name" value="Contact name" />

                    <TextInput
                        id="name"
                        value={data.name}
                        className="mt-1 block w-full"
                        autoComplete="name"
                        onChange={(event) =>
                            setData("name", event.target.value)
                        }
                        required
                    />

                    <InputError message={errors.name} className="mt-2" />
                </div>

                <div>
                    <InputLabel htmlFor="relationship" value="Relationship" />

                    <TextInput
                        id="relationship"
                        value={data.relationship}
                        className="mt-1 block w-full"
                        placeholder="Parent, spouse, sibling, friend"
                        onChange={(event) =>
                            setData("relationship", event.target.value)
                        }
                        required
                    />

                    <InputError
                        message={errors.relationship}
                        className="mt-2"
                    />
                </div>

                <div>
                    <InputLabel htmlFor="phone" value="Phone number" />

                    <TextInput
                        id="phone"
                        type="tel"
                        value={data.phone}
                        className="mt-1 block w-full"
                        placeholder="+94 77 123 4567"
                        onChange={(event) =>
                            setData("phone", event.target.value)
                        }
                        required
                    />

                    <InputError message={errors.phone} className="mt-2" />
                </div>

                <div>
                    <InputLabel
                        htmlFor="alternate_phone"
                        value="Alternate phone"
                    />

                    <TextInput
                        id="alternate_phone"
                        type="tel"
                        value={data.alternate_phone}
                        className="mt-1 block w-full"
                        placeholder="+94 77 123 4567"
                        onChange={(event) =>
                            setData("alternate_phone", event.target.value)
                        }
                    />

                    <InputError
                        message={errors.alternate_phone}
                        className="mt-2"
                    />
                </div>

                <div className="sm:col-span-2">
                    <InputLabel htmlFor="email" value="Email" />

                    <TextInput
                        id="email"
                        type="email"
                        value={data.email}
                        className="mt-1 block w-full"
                        autoComplete="email"
                        onChange={(event) =>
                            setData("email", event.target.value)
                        }
                    />

                    <InputError message={errors.email} className="mt-2" />
                </div>
            </section>

            <section className="space-y-4 rounded-lg border border-gray-200 bg-gray-50 p-4">
                <label className="flex items-start gap-3">
                    <Checkbox
                        checked={data.may_contact_in_emergency}
                        onChange={(event) =>
                            setData(
                                "may_contact_in_emergency",
                                event.target.checked,
                            )
                        }
                    />

                    <span className="text-sm text-gray-700">
                        This person may be contacted in an emergency.
                    </span>
                </label>

                <InputError
                    message={errors.may_contact_in_emergency}
                    className="mt-2"
                />

                <label className="flex items-start gap-3">
                    <Checkbox
                        checked={data.is_primary}
                        onChange={(event) =>
                            setData("is_primary", event.target.checked)
                        }
                    />

                    <span className="text-sm text-gray-700">
                        Mark this as my primary emergency contact.
                    </span>
                </label>

                <InputError message={errors.is_primary} className="mt-2" />

                <p className="text-xs text-gray-500">
                    Emergency contact details are used only for safety-related
                    workflows and should not contain clinical history.
                </p>
            </section>

            <div className="flex items-center justify-end gap-3">
                <Link href={route("client.emergency-contacts.index")}>
                    <SecondaryButton type="button">Cancel</SecondaryButton>
                </Link>

                <PrimaryButton disabled={processing}>
                    {submitLabel}
                </PrimaryButton>
            </div>
        </form>
    );
}
