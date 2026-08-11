import InputError from "@/Components/InputError";
import InputLabel from "@/Components/InputLabel";
import PrimaryButton from "@/Components/PrimaryButton";
import TextInput from "@/Components/TextInput";
import { Link } from "@inertiajs/react";

function makeSlug(value) {
    return value
        .toLowerCase()
        .trim()
        .replace(/[^a-z0-9]+/g, "-")
        .replace(/^-+|-+$/g, "");
}

function Field({ label, error, required = false, children }) {
    return (
        <div>
            <InputLabel
                value={
                    required ? (
                        <>
                            {label} <span className="text-rose-600">*</span>
                        </>
                    ) : (
                        label
                    )
                }
            />

            {children}

            <InputError message={error} className="mt-2" />
        </div>
    );
}

export default function ServiceForm({
    form,
    categories,
    serviceModes,
    targetAgeGroups,
    durations,
    submitLabel,
}) {
    const { data, setData, errors, processing, submit } = form;

    const updateName = (event) => {
        const name = event.target.value;
        const previousGeneratedSlug = makeSlug(data.name);

        setData((current) => ({
            ...current,
            name,
            slug:
                current.slug === "" || current.slug === previousGeneratedSlug
                    ? makeSlug(name)
                    : current.slug,
        }));
    };

    const updateTargetAgeGroup = (event) => {
        const targetAgeGroup = event.target.value;

        setData((current) => ({
            ...current,
            target_age_group: targetAgeGroup,
            minimum_age: targetAgeGroup === "custom" ? current.minimum_age : "",
            maximum_age: targetAgeGroup === "custom" ? current.maximum_age : "",
        }));
    };

    return (
        <form
            onSubmit={submit}
            className="space-y-8 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"
        >
            <section>
                <h3 className="text-lg font-semibold text-slate-900">
                    Basic information
                </h3>

                <div className="mt-5 grid gap-6 md:grid-cols-2">
                    <Field
                        label="Service category"
                        error={errors.service_category_id}
                        required
                    >
                        <select
                            value={data.service_category_id}
                            onChange={(event) =>
                                setData(
                                    "service_category_id",
                                    event.target.value,
                                )
                            }
                            className="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-teal-500 focus:ring-teal-500"
                            required
                        >
                            <option value="">Select a category</option>

                            {categories.map((category) => (
                                <option key={category.id} value={category.id}>
                                    {category.name}
                                    {category.status !== "active"
                                        ? ` (${category.status})`
                                        : ""}
                                </option>
                            ))}
                        </select>
                    </Field>

                    <Field label="Service name" error={errors.name} required>
                        <TextInput
                            value={data.name}
                            onChange={updateName}
                            className="mt-1 block w-full"
                            required
                            autoFocus
                        />
                    </Field>

                    <Field label="Slug" error={errors.slug} required>
                        <TextInput
                            value={data.slug}
                            onChange={(event) =>
                                setData("slug", makeSlug(event.target.value))
                            }
                            className="mt-1 block w-full"
                            required
                        />

                        <p className="mt-1 text-xs text-slate-500">
                            Used in public-facing URLs.
                        </p>
                    </Field>

                    <Field
                        label="Display order"
                        error={errors.display_order}
                        required
                    >
                        <TextInput
                            type="number"
                            min="0"
                            max="9999"
                            value={data.display_order}
                            onChange={(event) =>
                                setData("display_order", event.target.value)
                            }
                            className="mt-1 block w-full"
                            required
                        />
                    </Field>
                </div>

                <div className="mt-6">
                    <Field
                        label="Short description"
                        error={errors.short_description}
                        required
                    >
                        <textarea
                            value={data.short_description}
                            onChange={(event) =>
                                setData("short_description", event.target.value)
                            }
                            rows="3"
                            maxLength="500"
                            className="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-teal-500 focus:ring-teal-500"
                            required
                        />

                        <p className="mt-1 text-xs text-slate-500">
                            {data.short_description.length}/500 characters
                        </p>
                    </Field>
                </div>

                <div className="mt-6">
                    <Field label="Full description" error={errors.description}>
                        <textarea
                            value={data.description}
                            onChange={(event) =>
                                setData("description", event.target.value)
                            }
                            rows="7"
                            maxLength="10000"
                            className="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-teal-500 focus:ring-teal-500"
                        />
                    </Field>
                </div>
            </section>

            <section className="border-t border-slate-200 pt-7">
                <h3 className="text-lg font-semibold text-slate-900">
                    Session configuration
                </h3>

                <div className="mt-5 grid gap-6 md:grid-cols-2">
                    <Field
                        label="Duration"
                        error={errors.duration_minutes}
                        required
                    >
                        <select
                            value={data.duration_minutes}
                            onChange={(event) =>
                                setData("duration_minutes", event.target.value)
                            }
                            className="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-teal-500 focus:ring-teal-500"
                            required
                        >
                            {durations.map((duration) => (
                                <option key={duration} value={duration}>
                                    {duration} minutes
                                </option>
                            ))}
                        </select>
                    </Field>

                    <Field
                        label="Service mode"
                        error={errors.service_mode}
                        required
                    >
                        <select
                            value={data.service_mode}
                            onChange={(event) =>
                                setData("service_mode", event.target.value)
                            }
                            className="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-teal-500 focus:ring-teal-500"
                            required
                        >
                            {serviceModes.map((mode) => (
                                <option key={mode.value} value={mode.value}>
                                    {mode.label}
                                </option>
                            ))}
                        </select>
                    </Field>

                    <Field
                        label="Target age group"
                        error={errors.target_age_group}
                        required
                    >
                        <select
                            value={data.target_age_group}
                            onChange={updateTargetAgeGroup}
                            className="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-teal-500 focus:ring-teal-500"
                            required
                        >
                            {targetAgeGroups.map((ageGroup) => (
                                <option
                                    key={ageGroup.value}
                                    value={ageGroup.value}
                                >
                                    {ageGroup.label}
                                </option>
                            ))}
                        </select>
                    </Field>

                    <Field label="Status" error={errors.status} required>
                        <select
                            value={data.status}
                            onChange={(event) =>
                                setData("status", event.target.value)
                            }
                            className="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-teal-500 focus:ring-teal-500"
                            required
                        >
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </Field>
                </div>

                {data.target_age_group === "custom" && (
                    <div className="mt-6 grid gap-6 rounded-xl border border-teal-100 bg-teal-50/50 p-5 md:grid-cols-2">
                        <Field
                            label="Minimum age"
                            error={errors.minimum_age}
                            required
                        >
                            <TextInput
                                type="number"
                                min="0"
                                max="120"
                                value={data.minimum_age}
                                onChange={(event) =>
                                    setData("minimum_age", event.target.value)
                                }
                                className="mt-1 block w-full"
                                required
                            />
                        </Field>

                        <Field
                            label="Maximum age"
                            error={errors.maximum_age}
                            required
                        >
                            <TextInput
                                type="number"
                                min="0"
                                max="120"
                                value={data.maximum_age}
                                onChange={(event) =>
                                    setData("maximum_age", event.target.value)
                                }
                                className="mt-1 block w-full"
                                required
                            />
                        </Field>
                    </div>
                )}
            </section>

            <section className="border-t border-slate-200 pt-7">
                <h3 className="text-lg font-semibold text-slate-900">
                    Pricing
                </h3>

                <div className="mt-5 grid gap-6 md:grid-cols-2">
                    <Field label="Price" error={errors.price} required>
                        <TextInput
                            type="number"
                            min="0"
                            step="0.01"
                            value={data.price}
                            onChange={(event) =>
                                setData("price", event.target.value)
                            }
                            className="mt-1 block w-full"
                            required
                        />
                    </Field>

                    <Field label="Currency" error={errors.currency} required>
                        <TextInput
                            value={data.currency}
                            onChange={(event) =>
                                setData(
                                    "currency",
                                    event.target.value
                                        .toUpperCase()
                                        .slice(0, 3),
                                )
                            }
                            maxLength="3"
                            className="mt-1 block w-full uppercase"
                            required
                        />

                        <p className="mt-1 text-xs text-slate-500">
                            Three-letter currency code, for example LKR.
                        </p>
                    </Field>
                </div>
            </section>

            <div className="flex justify-end gap-3 border-t border-slate-200 pt-6">
                <Link
                    href={route("admin.counselling-services.index")}
                    className="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50"
                >
                    Cancel
                </Link>

                <PrimaryButton disabled={processing}>
                    {processing ? "Saving..." : submitLabel}
                </PrimaryButton>
            </div>
        </form>
    );
}
