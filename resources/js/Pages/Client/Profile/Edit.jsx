import InputError from "@/Components/InputError";
import InputLabel from "@/Components/InputLabel";
import PrimaryButton from "@/Components/PrimaryButton";
import SecondaryButton from "@/Components/SecondaryButton";
import TextInput from "@/Components/TextInput";
import ClientLayout from "@/Layouts/ClientLayout";
import { Head, Link, useForm } from "@inertiajs/react";

function SelectInput({
    id,
    value,
    onChange,
    options,
    error,
    required = false,
}) {
    return (
        <>
            <select
                id={id}
                value={value ?? ""}
                onChange={onChange}
                required={required}
                className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
            >
                {options.map((option) => (
                    <option key={option.value} value={option.value}>
                        {option.label}
                    </option>
                ))}
            </select>

            <InputError message={error} className="mt-2" />
        </>
    );
}

function Field({ children }) {
    return <div>{children}</div>;
}

export default function Edit({ clientProfile, options }) {
    const { data, setData, patch, processing, errors } = useForm({
        first_name: clientProfile.first_name ?? "",
        last_name: clientProfile.last_name ?? "",
        preferred_name: clientProfile.preferred_name ?? "",
        date_of_birth: clientProfile.date_of_birth ?? "",
        gender: clientProfile.gender ?? "",
        pronouns: clientProfile.pronouns ?? "",
        phone: clientProfile.phone ?? "",
        alternate_phone: clientProfile.alternate_phone ?? "",
        address_line_1: clientProfile.address_line_1 ?? "",
        address_line_2: clientProfile.address_line_2 ?? "",
        city: clientProfile.city ?? "",
        district: clientProfile.district ?? "",
        province: clientProfile.province ?? "",
        postal_code: clientProfile.postal_code ?? "",
        preferred_language: clientProfile.preferred_language ?? "",
        preferred_contact_method:
            clientProfile.preferred_contact_method ?? "email",
        occupation: clientProfile.occupation ?? "",
        marital_status: clientProfile.marital_status ?? "",
    });

    const submit = (event) => {
        event.preventDefault();

        patch(route("client.profile.update"), {
            preserveScroll: true,
        });
    };

    return (
        <ClientLayout
            header={
                <div>
                    <h2 className="text-xl font-semibold leading-tight text-gray-800">
                        Edit Client Profile
                    </h2>
                    <p className="mt-1 text-sm text-gray-500">
                        Update your contact details, basic profile information,
                        and preferred communication method.
                    </p>
                </div>
            }
        >
            <Head title="Edit Client Profile" />

            <div className="py-12">
                <div className="mx-auto max-w-5xl sm:px-6 lg:px-8">
                    <form
                        onSubmit={submit}
                        className="space-y-6 overflow-hidden bg-white p-6 shadow-sm sm:rounded-lg"
                    >
                        <section>
                            <h3 className="text-lg font-semibold text-gray-900">
                                Basic details
                            </h3>

                            <div className="mt-6 grid gap-4 sm:grid-cols-2">
                                <Field>
                                    <InputLabel
                                        htmlFor="first_name"
                                        value="First name"
                                    />

                                    <TextInput
                                        id="first_name"
                                        value={data.first_name}
                                        className="mt-1 block w-full"
                                        autoComplete="given-name"
                                        onChange={(event) =>
                                            setData(
                                                "first_name",
                                                event.target.value,
                                            )
                                        }
                                        required
                                    />

                                    <InputError
                                        message={errors.first_name}
                                        className="mt-2"
                                    />
                                </Field>

                                <Field>
                                    <InputLabel
                                        htmlFor="last_name"
                                        value="Last name"
                                    />

                                    <TextInput
                                        id="last_name"
                                        value={data.last_name}
                                        className="mt-1 block w-full"
                                        autoComplete="family-name"
                                        onChange={(event) =>
                                            setData(
                                                "last_name",
                                                event.target.value,
                                            )
                                        }
                                        required
                                    />

                                    <InputError
                                        message={errors.last_name}
                                        className="mt-2"
                                    />
                                </Field>

                                <Field>
                                    <InputLabel
                                        htmlFor="preferred_name"
                                        value="Preferred name"
                                    />

                                    <TextInput
                                        id="preferred_name"
                                        value={data.preferred_name}
                                        className="mt-1 block w-full"
                                        autoComplete="nickname"
                                        onChange={(event) =>
                                            setData(
                                                "preferred_name",
                                                event.target.value,
                                            )
                                        }
                                    />

                                    <InputError
                                        message={errors.preferred_name}
                                        className="mt-2"
                                    />
                                </Field>

                                <Field>
                                    <InputLabel
                                        htmlFor="date_of_birth"
                                        value="Date of birth"
                                    />

                                    <TextInput
                                        id="date_of_birth"
                                        type="date"
                                        value={data.date_of_birth}
                                        className="mt-1 block w-full"
                                        onChange={(event) =>
                                            setData(
                                                "date_of_birth",
                                                event.target.value,
                                            )
                                        }
                                    />

                                    <InputError
                                        message={errors.date_of_birth}
                                        className="mt-2"
                                    />
                                </Field>

                                <Field>
                                    <InputLabel
                                        htmlFor="gender"
                                        value="Gender"
                                    />

                                    <SelectInput
                                        id="gender"
                                        value={data.gender}
                                        options={options.genders}
                                        error={errors.gender}
                                        onChange={(event) =>
                                            setData(
                                                "gender",
                                                event.target.value,
                                            )
                                        }
                                    />
                                </Field>

                                <Field>
                                    <InputLabel
                                        htmlFor="pronouns"
                                        value="Preferred pronouns"
                                    />

                                    <TextInput
                                        id="pronouns"
                                        value={data.pronouns}
                                        className="mt-1 block w-full"
                                        placeholder="Example: she/her, he/him, they/them"
                                        onChange={(event) =>
                                            setData(
                                                "pronouns",
                                                event.target.value,
                                            )
                                        }
                                    />

                                    <InputError
                                        message={errors.pronouns}
                                        className="mt-2"
                                    />
                                </Field>

                                <Field>
                                    <InputLabel
                                        htmlFor="occupation"
                                        value="Occupation"
                                    />

                                    <TextInput
                                        id="occupation"
                                        value={data.occupation}
                                        className="mt-1 block w-full"
                                        onChange={(event) =>
                                            setData(
                                                "occupation",
                                                event.target.value,
                                            )
                                        }
                                    />

                                    <InputError
                                        message={errors.occupation}
                                        className="mt-2"
                                    />
                                </Field>

                                <Field>
                                    <InputLabel
                                        htmlFor="marital_status"
                                        value="Marital status"
                                    />

                                    <SelectInput
                                        id="marital_status"
                                        value={data.marital_status}
                                        options={options.maritalStatuses}
                                        error={errors.marital_status}
                                        onChange={(event) =>
                                            setData(
                                                "marital_status",
                                                event.target.value,
                                            )
                                        }
                                    />
                                </Field>
                            </div>
                        </section>

                        <section className="border-t border-gray-200 pt-6">
                            <h3 className="text-lg font-semibold text-gray-900">
                                Contact details
                            </h3>

                            <div className="mt-6 grid gap-4 sm:grid-cols-2">
                                <Field>
                                    <InputLabel htmlFor="email" value="Email" />

                                    <TextInput
                                        id="email"
                                        value={clientProfile.user.email}
                                        className="mt-1 block w-full bg-gray-100 text-gray-500"
                                        disabled
                                    />

                                    <p className="mt-1 text-xs text-gray-500">
                                        Email is managed from account settings.
                                    </p>
                                </Field>

                                <Field>
                                    <InputLabel
                                        htmlFor="phone"
                                        value="Phone number"
                                    />

                                    <TextInput
                                        id="phone"
                                        type="tel"
                                        value={data.phone}
                                        className="mt-1 block w-full"
                                        autoComplete="tel"
                                        placeholder="+94 77 123 4567"
                                        onChange={(event) =>
                                            setData("phone", event.target.value)
                                        }
                                        required
                                    />

                                    <InputError
                                        message={errors.phone}
                                        className="mt-2"
                                    />
                                </Field>

                                <Field>
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
                                            setData(
                                                "alternate_phone",
                                                event.target.value,
                                            )
                                        }
                                    />

                                    <InputError
                                        message={errors.alternate_phone}
                                        className="mt-2"
                                    />
                                </Field>

                                <Field>
                                    <InputLabel
                                        htmlFor="preferred_language"
                                        value="Preferred language"
                                    />

                                    <SelectInput
                                        id="preferred_language"
                                        value={data.preferred_language}
                                        options={options.preferredLanguages}
                                        error={errors.preferred_language}
                                        required
                                        onChange={(event) =>
                                            setData(
                                                "preferred_language",
                                                event.target.value,
                                            )
                                        }
                                    />
                                </Field>

                                <Field>
                                    <InputLabel
                                        htmlFor="preferred_contact_method"
                                        value="Preferred contact method"
                                    />

                                    <SelectInput
                                        id="preferred_contact_method"
                                        value={data.preferred_contact_method}
                                        options={options.contactMethods}
                                        error={errors.preferred_contact_method}
                                        required
                                        onChange={(event) =>
                                            setData(
                                                "preferred_contact_method",
                                                event.target.value,
                                            )
                                        }
                                    />
                                </Field>
                            </div>
                        </section>

                        <section className="border-t border-gray-200 pt-6">
                            <h3 className="text-lg font-semibold text-gray-900">
                                Address
                            </h3>

                            <div className="mt-6 grid gap-4 sm:grid-cols-2">
                                <Field>
                                    <InputLabel
                                        htmlFor="address_line_1"
                                        value="Address line 1"
                                    />

                                    <TextInput
                                        id="address_line_1"
                                        value={data.address_line_1}
                                        className="mt-1 block w-full"
                                        autoComplete="address-line1"
                                        onChange={(event) =>
                                            setData(
                                                "address_line_1",
                                                event.target.value,
                                            )
                                        }
                                    />

                                    <InputError
                                        message={errors.address_line_1}
                                        className="mt-2"
                                    />
                                </Field>

                                <Field>
                                    <InputLabel
                                        htmlFor="address_line_2"
                                        value="Address line 2"
                                    />

                                    <TextInput
                                        id="address_line_2"
                                        value={data.address_line_2}
                                        className="mt-1 block w-full"
                                        autoComplete="address-line2"
                                        onChange={(event) =>
                                            setData(
                                                "address_line_2",
                                                event.target.value,
                                            )
                                        }
                                    />

                                    <InputError
                                        message={errors.address_line_2}
                                        className="mt-2"
                                    />
                                </Field>

                                <Field>
                                    <InputLabel htmlFor="city" value="City" />

                                    <TextInput
                                        id="city"
                                        value={data.city}
                                        className="mt-1 block w-full"
                                        autoComplete="address-level2"
                                        onChange={(event) =>
                                            setData("city", event.target.value)
                                        }
                                    />

                                    <InputError
                                        message={errors.city}
                                        className="mt-2"
                                    />
                                </Field>

                                <Field>
                                    <InputLabel
                                        htmlFor="district"
                                        value="District"
                                    />

                                    <TextInput
                                        id="district"
                                        value={data.district}
                                        className="mt-1 block w-full"
                                        onChange={(event) =>
                                            setData(
                                                "district",
                                                event.target.value,
                                            )
                                        }
                                    />

                                    <InputError
                                        message={errors.district}
                                        className="mt-2"
                                    />
                                </Field>

                                <Field>
                                    <InputLabel
                                        htmlFor="province"
                                        value="Province"
                                    />

                                    <TextInput
                                        id="province"
                                        value={data.province}
                                        className="mt-1 block w-full"
                                        autoComplete="address-level1"
                                        onChange={(event) =>
                                            setData(
                                                "province",
                                                event.target.value,
                                            )
                                        }
                                    />

                                    <InputError
                                        message={errors.province}
                                        className="mt-2"
                                    />
                                </Field>

                                <Field>
                                    <InputLabel
                                        htmlFor="postal_code"
                                        value="Postal code"
                                    />

                                    <TextInput
                                        id="postal_code"
                                        value={data.postal_code}
                                        className="mt-1 block w-full"
                                        autoComplete="postal-code"
                                        onChange={(event) =>
                                            setData(
                                                "postal_code",
                                                event.target.value,
                                            )
                                        }
                                    />

                                    <InputError
                                        message={errors.postal_code}
                                        className="mt-2"
                                    />
                                </Field>
                            </div>
                        </section>

                        <div className="flex items-center justify-end gap-3 border-t border-gray-200 pt-6">
                            <Link href={route("client.profile.show")}>
                                <SecondaryButton type="button">
                                    Cancel
                                </SecondaryButton>
                            </Link>

                            <PrimaryButton disabled={processing}>
                                Save profile
                            </PrimaryButton>
                        </div>
                    </form>
                </div>
            </div>
        </ClientLayout>
    );
}
