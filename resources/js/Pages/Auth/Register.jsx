import Checkbox from "@/Components/Checkbox";
import InputError from "@/Components/InputError";
import InputLabel from "@/Components/InputLabel";
import PrimaryButton from "@/Components/PrimaryButton";
import TextInput from "@/Components/TextInput";
import GuestLayout from "@/Layouts/GuestLayout";
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

export default function Register({ consentLabels, languageOptions }) {
    const labels = consentLabels ?? {
        terms: "I accept the terms of service.",
        privacy: "I accept the privacy policy.",
        communication:
            "I agree to receive appointment and service-related communication.",
    };

    const { data, setData, post, processing, errors, reset } = useForm({
        first_name: "",
        last_name: "",
        preferred_name: "",
        email: "",
        phone: "",
        preferred_language: "",
        password: "",
        password_confirmation: "",
        terms_accepted: false,
        privacy_policy_accepted: false,
        communication_consent: false,
    });

    const submit = (e) => {
        e.preventDefault();

        post(route("register"), {
            onFinish: () => reset("password", "password_confirmation"),
        });
    };

    return (
        <GuestLayout>
            <Head title="Client Registration" />

            <div className="mb-6">
                <h1 className="text-xl font-semibold text-gray-900">
                    Create your client account
                </h1>
                <p className="mt-2 text-sm text-gray-600">
                    Register to manage your counselling profile, preferences,
                    appointments, and privacy settings.
                </p>
            </div>

            <form onSubmit={submit}>
                <div className="grid gap-4 sm:grid-cols-2">
                    <div>
                        <InputLabel htmlFor="first_name" value="First name" />

                        <TextInput
                            id="first_name"
                            name="first_name"
                            value={data.first_name}
                            className="mt-1 block w-full"
                            autoComplete="given-name"
                            isFocused={true}
                            onChange={(e) =>
                                setData("first_name", e.target.value)
                            }
                            required
                        />

                        <InputError
                            message={errors.first_name}
                            className="mt-2"
                        />
                    </div>

                    <div>
                        <InputLabel htmlFor="last_name" value="Last name" />

                        <TextInput
                            id="last_name"
                            name="last_name"
                            value={data.last_name}
                            className="mt-1 block w-full"
                            autoComplete="family-name"
                            onChange={(e) =>
                                setData("last_name", e.target.value)
                            }
                            required
                        />

                        <InputError
                            message={errors.last_name}
                            className="mt-2"
                        />
                    </div>
                </div>

                <div className="mt-4">
                    <InputLabel
                        htmlFor="preferred_name"
                        value="Preferred name"
                    />

                    <TextInput
                        id="preferred_name"
                        name="preferred_name"
                        value={data.preferred_name}
                        className="mt-1 block w-full"
                        autoComplete="nickname"
                        onChange={(e) =>
                            setData("preferred_name", e.target.value)
                        }
                    />

                    <InputError
                        message={errors.preferred_name}
                        className="mt-2"
                    />
                </div>

                <div className="mt-4">
                    <InputLabel htmlFor="email" value="Email" />

                    <TextInput
                        id="email"
                        type="email"
                        name="email"
                        value={data.email}
                        className="mt-1 block w-full"
                        autoComplete="username"
                        onChange={(e) => setData("email", e.target.value)}
                        required
                    />

                    <InputError message={errors.email} className="mt-2" />
                </div>

                <div className="mt-4">
                    <InputLabel htmlFor="phone" value="Phone number" />

                    <TextInput
                        id="phone"
                        type="tel"
                        name="phone"
                        value={data.phone}
                        className="mt-1 block w-full"
                        autoComplete="tel"
                        placeholder="+94 77 123 4567"
                        onChange={(e) => setData("phone", e.target.value)}
                        required
                    />

                    <InputError message={errors.phone} className="mt-2" />
                </div>

                <div className="mt-4">
                    <InputLabel
                        htmlFor="preferred_language"
                        value="Preferred language"
                    />

                    <SelectInput
                        id="preferred_language"
                        value={data.preferred_language}
                        options={
                            languageOptions ?? [
                                {
                                    value: "",
                                    label: "Select preferred language",
                                },
                                {
                                    value: "sinhala",
                                    label: "Sinhala",
                                },
                                {
                                    value: "tamil",
                                    label: "Tamil",
                                },
                                {
                                    value: "english",
                                    label: "English",
                                },
                                {
                                    value: "no_preference",
                                    label: "No preference",
                                },
                                {
                                    value: "other",
                                    label: "Other",
                                },
                            ]
                        }
                        error={errors.preferred_language}
                        required
                        onChange={(e) =>
                            setData("preferred_language", e.target.value)
                        }
                    />
                </div>

                <div className="mt-4">
                    <InputLabel htmlFor="password" value="Password" />

                    <TextInput
                        id="password"
                        type="password"
                        name="password"
                        value={data.password}
                        className="mt-1 block w-full"
                        autoComplete="new-password"
                        onChange={(e) => setData("password", e.target.value)}
                        required
                    />

                    <InputError message={errors.password} className="mt-2" />

                    <p className="mt-1 text-xs text-gray-500">
                        Use at least 8 characters with uppercase, lowercase, and
                        numbers.
                    </p>
                </div>

                <div className="mt-4">
                    <InputLabel
                        htmlFor="password_confirmation"
                        value="Confirm password"
                    />

                    <TextInput
                        id="password_confirmation"
                        type="password"
                        name="password_confirmation"
                        value={data.password_confirmation}
                        className="mt-1 block w-full"
                        autoComplete="new-password"
                        onChange={(e) =>
                            setData("password_confirmation", e.target.value)
                        }
                        required
                    />

                    <InputError
                        message={errors.password_confirmation}
                        className="mt-2"
                    />
                </div>

                <div className="mt-6 space-y-4 rounded-lg border border-gray-200 bg-gray-50 p-4">
                    <div>
                        <label className="flex items-start gap-3">
                            <Checkbox
                                name="terms_accepted"
                                checked={data.terms_accepted}
                                onChange={(e) =>
                                    setData("terms_accepted", e.target.checked)
                                }
                                required
                            />

                            <span className="text-sm text-gray-700">
                                {labels.terms}
                            </span>
                        </label>

                        <InputError
                            message={errors.terms_accepted}
                            className="mt-2"
                        />
                    </div>

                    <div>
                        <label className="flex items-start gap-3">
                            <Checkbox
                                name="privacy_policy_accepted"
                                checked={data.privacy_policy_accepted}
                                onChange={(e) =>
                                    setData(
                                        "privacy_policy_accepted",
                                        e.target.checked,
                                    )
                                }
                                required
                            />

                            <span className="text-sm text-gray-700">
                                {labels.privacy}
                            </span>
                        </label>

                        <InputError
                            message={errors.privacy_policy_accepted}
                            className="mt-2"
                        />
                    </div>

                    <div>
                        <label className="flex items-start gap-3">
                            <Checkbox
                                name="communication_consent"
                                checked={data.communication_consent}
                                onChange={(e) =>
                                    setData(
                                        "communication_consent",
                                        e.target.checked,
                                    )
                                }
                            />

                            <span className="text-sm text-gray-700">
                                {labels.communication}
                            </span>
                        </label>

                        <InputError
                            message={errors.communication_consent}
                            className="mt-2"
                        />
                    </div>

                    <p className="text-xs text-gray-500">
                        Final legal wording for the terms and privacy policy
                        must be approved before production launch.
                    </p>
                </div>

                <div className="mt-6 flex items-center justify-end">
                    <Link
                        href={route("login")}
                        className="rounded-md text-sm text-gray-600 underline hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                    >
                        Already registered?
                    </Link>

                    <PrimaryButton className="ms-4" disabled={processing}>
                        Register
                    </PrimaryButton>
                </div>
            </form>
        </GuestLayout>
    );
}
