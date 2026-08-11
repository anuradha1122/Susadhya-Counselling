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

function Field({ label, error, children }) {
    return (
        <div>
            <InputLabel value={label} />
            {children}
            <InputError message={error} className="mt-2" />
        </div>
    );
}

export default function CategoryForm({ form, submitLabel }) {
    const { data, setData, errors, processing, submit } = form;

    const updateName = (event) => {
        const name = event.target.value;
        const oldGeneratedSlug = makeSlug(data.name);

        setData((current) => ({
            ...current,
            name,
            slug:
                current.slug === "" || current.slug === oldGeneratedSlug
                    ? makeSlug(name)
                    : current.slug,
        }));
    };

    return (
        <form
            onSubmit={submit}
            className="space-y-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"
        >
            <div className="grid gap-6 md:grid-cols-2">
                <Field label="Category name" error={errors.name}>
                    <TextInput
                        value={data.name}
                        onChange={updateName}
                        className="mt-1 block w-full"
                        required
                        autoFocus
                    />
                </Field>

                <Field label="Slug" error={errors.slug}>
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

                <Field label="Display order" error={errors.display_order}>
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

                <Field label="Status" error={errors.status}>
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

            <Field label="Description" error={errors.description}>
                <textarea
                    value={data.description}
                    onChange={(event) =>
                        setData("description", event.target.value)
                    }
                    rows="5"
                    maxLength="3000"
                    className="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-teal-500 focus:ring-teal-500"
                />
            </Field>

            <div className="flex justify-end gap-3 border-t border-slate-100 pt-5">
                <Link
                    href={route("admin.service-categories.index")}
                    className="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50"
                >
                    Cancel
                </Link>

                <PrimaryButton disabled={processing}>
                    {submitLabel}
                </PrimaryButton>
            </div>
        </form>
    );
}
