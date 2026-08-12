import InputError from "@/Components/InputError";
import InputLabel from "@/Components/InputLabel";
import PrimaryButton from "@/Components/PrimaryButton";
import SecondaryButton from "@/Components/SecondaryButton";
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

function TextAreaInput({ id, value, onChange, rows = 4 }) {
    return (
        <textarea
            id={id}
            value={value ?? ""}
            rows={rows}
            onChange={onChange}
            className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
        />
    );
}

export default function Edit({ preference, options }) {
    const { data, setData, patch, processing, errors } = useForm({
        preferred_counselling_mode:
            preference.preferred_counselling_mode ?? "no_preference",
        preferred_counsellor_gender:
            preference.preferred_counsellor_gender ?? "no_preference",
        preferred_language: preference.preferred_language ?? "",
        general_availability_notes: preference.general_availability_notes ?? "",
        accessibility_requirements: preference.accessibility_requirements ?? "",
        additional_preferences: preference.additional_preferences ?? "",
    });

    const submit = (event) => {
        event.preventDefault();

        patch(route("client.preferences.update"), {
            preserveScroll: true,
        });
    };

    return (
        <ClientLayout
            header={
                <div>
                    <h2 className="text-xl font-semibold leading-tight text-gray-800">
                        Edit Counselling Preferences
                    </h2>
                    <p className="mt-1 text-sm text-gray-500">
                        Update preferences used by future search, booking, and
                        matching workflows.
                    </p>
                </div>
            }
        >
            <Head title="Edit Counselling Preferences" />

            <div className="py-12">
                <div className="mx-auto max-w-4xl sm:px-6 lg:px-8">
                    <form
                        onSubmit={submit}
                        className="space-y-6 overflow-hidden bg-white p-6 shadow-sm sm:rounded-lg"
                    >
                        <section>
                            <h3 className="text-lg font-semibold text-gray-900">
                                Matching preferences
                            </h3>

                            <div className="mt-6 grid gap-4 sm:grid-cols-2">
                                <div>
                                    <InputLabel
                                        htmlFor="preferred_counselling_mode"
                                        value="Preferred counselling mode"
                                    />

                                    <SelectInput
                                        id="preferred_counselling_mode"
                                        value={data.preferred_counselling_mode}
                                        options={options.counsellingModes}
                                        error={
                                            errors.preferred_counselling_mode
                                        }
                                        required
                                        onChange={(event) =>
                                            setData(
                                                "preferred_counselling_mode",
                                                event.target.value,
                                            )
                                        }
                                    />
                                </div>

                                <div>
                                    <InputLabel
                                        htmlFor="preferred_counsellor_gender"
                                        value="Preferred counsellor gender"
                                    />

                                    <SelectInput
                                        id="preferred_counsellor_gender"
                                        value={data.preferred_counsellor_gender}
                                        options={options.counsellorGenders}
                                        error={
                                            errors.preferred_counsellor_gender
                                        }
                                        required
                                        onChange={(event) =>
                                            setData(
                                                "preferred_counsellor_gender",
                                                event.target.value,
                                            )
                                        }
                                    />
                                </div>

                                <div className="sm:col-span-2">
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
                                </div>
                            </div>
                        </section>

                        <section className="border-t border-gray-200 pt-6">
                            <h3 className="text-lg font-semibold text-gray-900">
                                Availability and access notes
                            </h3>

                            <div className="mt-6 space-y-4">
                                <div>
                                    <InputLabel
                                        htmlFor="general_availability_notes"
                                        value="General availability notes"
                                    />

                                    <TextAreaInput
                                        id="general_availability_notes"
                                        value={data.general_availability_notes}
                                        onChange={(event) =>
                                            setData(
                                                "general_availability_notes",
                                                event.target.value,
                                            )
                                        }
                                    />

                                    <InputError
                                        message={
                                            errors.general_availability_notes
                                        }
                                        className="mt-2"
                                    />
                                </div>

                                <div>
                                    <InputLabel
                                        htmlFor="accessibility_requirements"
                                        value="Accessibility requirements"
                                    />

                                    <TextAreaInput
                                        id="accessibility_requirements"
                                        value={data.accessibility_requirements}
                                        onChange={(event) =>
                                            setData(
                                                "accessibility_requirements",
                                                event.target.value,
                                            )
                                        }
                                    />

                                    <InputError
                                        message={
                                            errors.accessibility_requirements
                                        }
                                        className="mt-2"
                                    />
                                </div>

                                <div>
                                    <InputLabel
                                        htmlFor="additional_preferences"
                                        value="Additional preferences"
                                    />

                                    <TextAreaInput
                                        id="additional_preferences"
                                        value={data.additional_preferences}
                                        onChange={(event) =>
                                            setData(
                                                "additional_preferences",
                                                event.target.value,
                                            )
                                        }
                                    />

                                    <InputError
                                        message={errors.additional_preferences}
                                        className="mt-2"
                                    />
                                </div>
                            </div>
                        </section>

                        <div className="rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
                            Do not enter detailed clinical history here. Use
                            these fields only for general preferences and access
                            needs.
                        </div>

                        <div className="flex items-center justify-end gap-3 border-t border-gray-200 pt-6">
                            <Link href={route("client.preferences.show")}>
                                <SecondaryButton type="button">
                                    Cancel
                                </SecondaryButton>
                            </Link>

                            <PrimaryButton disabled={processing}>
                                Save preferences
                            </PrimaryButton>
                        </div>
                    </form>
                </div>
            </div>
        </ClientLayout>
    );
}
