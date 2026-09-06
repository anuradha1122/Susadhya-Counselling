import Checkbox from "@/Components/Checkbox";
import InputError from "@/Components/InputError";
import PublicAuthLayout from "@/Layouts/PublicAuthLayout";
import {
    Head,
    Link,
    useForm,
} from "@inertiajs/react";
import {
    ArrowRight,
    CheckCircle2,
    Languages,
    LockKeyhole,
    Mail,
    Phone,
    ShieldCheck,
    UserRound,
} from "lucide-react";

function FieldLabel({
    htmlFor,
    children,
    optional = false,
}) {
    return (
        <div className="flex items-center justify-between gap-3">
            <label
                htmlFor={htmlFor}
                className="text-sm font-semibold text-[#123B5B]"
            >
                {children}
            </label>

            {optional && (
                <span className="text-[11px] text-slate-400">
                    Optional
                </span>
            )}
        </div>
    );
}

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
            <div className="relative mt-2">
                <Languages className="pointer-events-none absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />

                <select
                    id={id}
                    value={value ?? ""}
                    onChange={onChange}
                    required={required}
                    className="block h-12 w-full appearance-none rounded-xl border border-slate-300 bg-white pl-11 pr-10 text-sm text-slate-700 shadow-sm outline-none transition focus:border-[#2A9D8F] focus:ring-2 focus:ring-[#2A9D8F]/15"
                >
                    {options.map(
                        (option) => (
                            <option
                                key={
                                    option.value
                                }
                                value={
                                    option.value
                                }
                            >
                                {
                                    option.label
                                }
                            </option>
                        ),
                    )}
                </select>

                <span className="pointer-events-none absolute right-4 top-1/2 -translate-y-1/2 text-xs text-slate-400">
                    ▼
                </span>
            </div>

            <InputError
                message={error}
                className="mt-2"
            />
        </>
    );
}

export default function Register({
    consentLabels,
    languageOptions,
}) {
    const labels =
        consentLabels ?? {
            terms:
                "I accept the terms of service.",
            privacy:
                "I accept the privacy policy.",
            communication:
                "I agree to receive appointment and service-related communication.",
        };

    const {
        data,
        setData,
        post,
        processing,
        errors,
        reset,
    } = useForm({
        first_name: "",
        last_name: "",
        preferred_name: "",
        email: "",
        phone: "",
        preferred_language: "",
        password: "",
        password_confirmation: "",
        terms_accepted: false,
        privacy_policy_accepted:
            false,
        communication_consent:
            false,
    });

    const submit = (event) => {
        event.preventDefault();

        post(route("register"), {
            onFinish: () =>
                reset(
                    "password",
                    "password_confirmation",
                ),
        });
    };

    return (
       <PublicAuthLayout
            title="Create your account"
            description="Create a secure client account to explore counselling support, manage appointments and access your Susadhya profile."
            image="/images/site-defaults/auth-register.jpg"
            imageAlt="Professional counsellor supporting a client"
        >
            <Head title="Client Registration" />

            <form
                onSubmit={submit}
                className="space-y-7"
            >
                {/* Personal information */}
                <section>
                    <div className="mb-5 flex items-center gap-3">
                        <div className="flex h-9 w-9 items-center justify-center rounded-full bg-[#E5F5F4] text-[#168C9D]">
                            <UserRound className="h-4 w-4" />
                        </div>

                        <div>
                            <h3 className="text-sm font-bold text-[#123B5B]">
                                Personal
                                information
                            </h3>

                            <p className="mt-0.5 text-xs text-slate-500">
                                Tell us how to
                                identify and
                                contact you.
                            </p>
                        </div>
                    </div>

                    <div className="grid gap-5 sm:grid-cols-2">
                        <div>
                            <FieldLabel htmlFor="first_name">
                                First name
                            </FieldLabel>

                            <div className="relative mt-2">
                                <UserRound className="pointer-events-none absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />

                                <input
                                    id="first_name"
                                    name="first_name"
                                    value={
                                        data.first_name
                                    }
                                    autoComplete="given-name"
                                    autoFocus
                                    required
                                    onChange={(
                                        event,
                                    ) =>
                                        setData(
                                            "first_name",
                                            event
                                                .target
                                                .value,
                                        )
                                    }
                                    placeholder="First name"
                                    className="block h-12 w-full rounded-xl border border-slate-300 bg-white pl-11 pr-4 text-sm text-slate-700 shadow-sm outline-none focus:border-[#2A9D8F] focus:ring-2 focus:ring-[#2A9D8F]/15"
                                />
                            </div>

                            <InputError
                                message={
                                    errors.first_name
                                }
                                className="mt-2"
                            />
                        </div>

                        <div>
                            <FieldLabel htmlFor="last_name">
                                Last name
                            </FieldLabel>

                            <div className="relative mt-2">
                                <UserRound className="pointer-events-none absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />

                                <input
                                    id="last_name"
                                    name="last_name"
                                    value={
                                        data.last_name
                                    }
                                    autoComplete="family-name"
                                    required
                                    onChange={(
                                        event,
                                    ) =>
                                        setData(
                                            "last_name",
                                            event
                                                .target
                                                .value,
                                        )
                                    }
                                    placeholder="Last name"
                                    className="block h-12 w-full rounded-xl border border-slate-300 bg-white pl-11 pr-4 text-sm text-slate-700 shadow-sm outline-none focus:border-[#2A9D8F] focus:ring-2 focus:ring-[#2A9D8F]/15"
                                />
                            </div>

                            <InputError
                                message={
                                    errors.last_name
                                }
                                className="mt-2"
                            />
                        </div>
                    </div>

                    <div className="mt-5">
                        <FieldLabel
                            htmlFor="preferred_name"
                            optional
                        >
                            Preferred name
                        </FieldLabel>

                        <input
                            id="preferred_name"
                            name="preferred_name"
                            value={
                                data.preferred_name
                            }
                            autoComplete="nickname"
                            onChange={(event) =>
                                setData(
                                    "preferred_name",
                                    event.target
                                        .value,
                                )
                            }
                            placeholder="How would you like us to address you?"
                            className="mt-2 block h-12 w-full rounded-xl border border-slate-300 bg-white px-4 text-sm text-slate-700 shadow-sm outline-none focus:border-[#2A9D8F] focus:ring-2 focus:ring-[#2A9D8F]/15"
                        />

                        <InputError
                            message={
                                errors.preferred_name
                            }
                            className="mt-2"
                        />
                    </div>
                </section>

                <div className="border-t border-slate-200" />

                {/* Contact */}
                <section>
                    <div className="mb-5">
                        <h3 className="text-sm font-bold text-[#123B5B]">
                            Contact &
                            preferences
                        </h3>

                        <p className="mt-1 text-xs leading-5 text-slate-500">
                            These details help
                            Susadhya manage your
                            account and
                            counselling
                            preferences.
                        </p>
                    </div>

                    <div className="space-y-5">
                        <div>
                            <FieldLabel htmlFor="email">
                                Email address
                            </FieldLabel>

                            <div className="relative mt-2">
                                <Mail className="pointer-events-none absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />

                                <input
                                    id="email"
                                    type="email"
                                    name="email"
                                    value={
                                        data.email
                                    }
                                    autoComplete="username"
                                    required
                                    onChange={(
                                        event,
                                    ) =>
                                        setData(
                                            "email",
                                            event
                                                .target
                                                .value,
                                        )
                                    }
                                    placeholder="you@example.com"
                                    className="block h-12 w-full rounded-xl border border-slate-300 bg-white pl-11 pr-4 text-sm text-slate-700 shadow-sm outline-none focus:border-[#2A9D8F] focus:ring-2 focus:ring-[#2A9D8F]/15"
                                />
                            </div>

                            <InputError
                                message={
                                    errors.email
                                }
                                className="mt-2"
                            />
                        </div>

                        <div>
                            <FieldLabel htmlFor="phone">
                                Phone number
                            </FieldLabel>

                            <div className="relative mt-2">
                                <Phone className="pointer-events-none absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />

                                <input
                                    id="phone"
                                    type="tel"
                                    name="phone"
                                    value={
                                        data.phone
                                    }
                                    autoComplete="tel"
                                    required
                                    placeholder="+94 77 123 4567"
                                    onChange={(
                                        event,
                                    ) =>
                                        setData(
                                            "phone",
                                            event
                                                .target
                                                .value,
                                        )
                                    }
                                    className="block h-12 w-full rounded-xl border border-slate-300 bg-white pl-11 pr-4 text-sm text-slate-700 shadow-sm outline-none focus:border-[#2A9D8F] focus:ring-2 focus:ring-[#2A9D8F]/15"
                                />
                            </div>

                            <InputError
                                message={
                                    errors.phone
                                }
                                className="mt-2"
                            />
                        </div>

                        <div>
                            <FieldLabel htmlFor="preferred_language">
                                Preferred
                                language
                            </FieldLabel>

                            <SelectInput
                                id="preferred_language"
                                value={
                                    data.preferred_language
                                }
                                options={
                                    languageOptions ??
                                    [
                                        {
                                            value: "",
                                            label:
                                                "Select preferred language",
                                        },
                                        {
                                            value:
                                                "sinhala",
                                            label:
                                                "Sinhala",
                                        },
                                        {
                                            value:
                                                "tamil",
                                            label:
                                                "Tamil",
                                        },
                                        {
                                            value:
                                                "english",
                                            label:
                                                "English",
                                        },
                                        {
                                            value:
                                                "no_preference",
                                            label:
                                                "No preference",
                                        },
                                        {
                                            value:
                                                "other",
                                            label:
                                                "Other",
                                        },
                                    ]
                                }
                                error={
                                    errors.preferred_language
                                }
                                required
                                onChange={(
                                    event,
                                ) =>
                                    setData(
                                        "preferred_language",
                                        event
                                            .target
                                            .value,
                                    )
                                }
                            />
                        </div>
                    </div>
                </section>

                <div className="border-t border-slate-200" />

                {/* Security */}
                <section>
                    <div className="mb-5 flex items-center gap-3">
                        <div className="flex h-9 w-9 items-center justify-center rounded-full bg-[#E5F5F4] text-[#168C9D]">
                            <LockKeyhole className="h-4 w-4" />
                        </div>

                        <div>
                            <h3 className="text-sm font-bold text-[#123B5B]">
                                Account security
                            </h3>

                            <p className="mt-0.5 text-xs text-slate-500">
                                Choose a secure
                                password for your
                                account.
                            </p>
                        </div>
                    </div>

                    <div className="space-y-5">
                        <div>
                            <FieldLabel htmlFor="password">
                                Password
                            </FieldLabel>

                            <div className="relative mt-2">
                                <LockKeyhole className="pointer-events-none absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />

                                <input
                                    id="password"
                                    type="password"
                                    name="password"
                                    value={
                                        data.password
                                    }
                                    autoComplete="new-password"
                                    required
                                    onChange={(
                                        event,
                                    ) =>
                                        setData(
                                            "password",
                                            event
                                                .target
                                                .value,
                                        )
                                    }
                                    placeholder="Create a secure password"
                                    className="block h-12 w-full rounded-xl border border-slate-300 bg-white pl-11 pr-4 text-sm text-slate-700 shadow-sm outline-none focus:border-[#2A9D8F] focus:ring-2 focus:ring-[#2A9D8F]/15"
                                />
                            </div>

                            <InputError
                                message={
                                    errors.password
                                }
                                className="mt-2"
                            />

                            <p className="mt-2 flex items-start gap-2 text-xs leading-5 text-slate-500">
                                <ShieldCheck className="mt-0.5 h-3.5 w-3.5 shrink-0 text-[#7FB069]" />

                                Use at least 8
                                characters with
                                uppercase,
                                lowercase and
                                numbers.
                            </p>
                        </div>

                        <div>
                            <FieldLabel htmlFor="password_confirmation">
                                Confirm password
                            </FieldLabel>

                            <div className="relative mt-2">
                                <LockKeyhole className="pointer-events-none absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />

                                <input
                                    id="password_confirmation"
                                    type="password"
                                    name="password_confirmation"
                                    value={
                                        data.password_confirmation
                                    }
                                    autoComplete="new-password"
                                    required
                                    onChange={(
                                        event,
                                    ) =>
                                        setData(
                                            "password_confirmation",
                                            event
                                                .target
                                                .value,
                                        )
                                    }
                                    placeholder="Enter the password again"
                                    className="block h-12 w-full rounded-xl border border-slate-300 bg-white pl-11 pr-4 text-sm text-slate-700 shadow-sm outline-none focus:border-[#2A9D8F] focus:ring-2 focus:ring-[#2A9D8F]/15"
                                />
                            </div>

                            <InputError
                                message={
                                    errors.password_confirmation
                                }
                                className="mt-2"
                            />
                        </div>
                    </div>
                </section>

                <div className="border-t border-slate-200" />

                {/* Consent */}
                <section>
                    <div className="mb-5">
                        <h3 className="text-sm font-bold text-[#123B5B]">
                            Privacy &
                            consent
                        </h3>

                        <p className="mt-1 text-xs leading-5 text-slate-500">
                            Review the required
                            agreements before
                            creating your
                            account.
                        </p>
                    </div>

                    <div className="space-y-4 rounded-2xl border border-[#D9E9E8] bg-[#F5FAF9] p-5">
                        <div>
                            <label className="flex cursor-pointer items-start gap-3">
                                <Checkbox
                                    name="terms_accepted"
                                    checked={
                                        data.terms_accepted
                                    }
                                    onChange={(
                                        event,
                                    ) =>
                                        setData(
                                            "terms_accepted",
                                            event
                                                .target
                                                .checked,
                                        )
                                    }
                                    required
                                />

                                <span className="text-sm leading-6 text-slate-700">
                                    {
                                        labels.terms
                                    }
                                </span>
                            </label>

                            <InputError
                                message={
                                    errors.terms_accepted
                                }
                                className="mt-2"
                            />
                        </div>

                        <div>
                            <label className="flex cursor-pointer items-start gap-3">
                                <Checkbox
                                    name="privacy_policy_accepted"
                                    checked={
                                        data.privacy_policy_accepted
                                    }
                                    onChange={(
                                        event,
                                    ) =>
                                        setData(
                                            "privacy_policy_accepted",
                                            event
                                                .target
                                                .checked,
                                        )
                                    }
                                    required
                                />

                                <span className="text-sm leading-6 text-slate-700">
                                    {
                                        labels.privacy
                                    }
                                </span>
                            </label>

                            <InputError
                                message={
                                    errors.privacy_policy_accepted
                                }
                                className="mt-2"
                            />
                        </div>

                        <div>
                            <label className="flex cursor-pointer items-start gap-3">
                                <Checkbox
                                    name="communication_consent"
                                    checked={
                                        data.communication_consent
                                    }
                                    onChange={(
                                        event,
                                    ) =>
                                        setData(
                                            "communication_consent",
                                            event
                                                .target
                                                .checked,
                                        )
                                    }
                                />

                                <span className="text-sm leading-6 text-slate-700">
                                    {
                                        labels.communication
                                    }
                                </span>
                            </label>

                            <InputError
                                message={
                                    errors.communication_consent
                                }
                                className="mt-2"
                            />
                        </div>

                        <div className="flex gap-2 border-t border-[#D9E9E8] pt-4">
                            <CheckCircle2 className="mt-0.5 h-4 w-4 shrink-0 text-[#7FB069]" />

                            <p className="text-[11px] leading-5 text-slate-500">
                                Final legal wording
                                for the terms and
                                privacy policy must
                                be approved before
                                production launch.
                            </p>
                        </div>
                    </div>
                </section>

                {/* Submit */}
                <button
                    type="submit"
                    disabled={processing}
                    className="inline-flex h-12 w-full items-center justify-center gap-2 rounded-full bg-[#168C9D] px-6 text-sm font-semibold text-white shadow-md shadow-cyan-900/10 transition hover:-translate-y-0.5 hover:bg-[#147D8B] hover:shadow-lg disabled:cursor-not-allowed disabled:opacity-50"
                >
                    {processing
                        ? "Creating account..."
                        : "Create account"}

                    {!processing && (
                        <ArrowRight className="h-4 w-4" />
                    )}
                </button>

                <div className="border-t border-slate-200 pt-6 text-center">
                    <p className="text-sm text-slate-500">
                        Already have an
                        account?{" "}
                        <Link
                            href={route(
                                "login",
                            )}
                            className="font-semibold text-[#168C9D] transition hover:text-[#123B5B]"
                        >
                            Sign in
                        </Link>
                    </p>
                </div>
            </form>
        </PublicAuthLayout>
    );
}
