import AdminLayout from "@/Layouts/AdminLayout";
import { Head, useForm } from "@inertiajs/react";
import { Settings } from "lucide-react";

export default function Index({ settings }) {
    const initialValues = Object.fromEntries(
        settings.map((setting) => [
            setting.key,
            setting.value ?? "",
        ]),
    );

    const form = useForm({
        settings: initialValues,
    });

    const grouped = settings.reduce((result, setting) => {
        result[setting.group] ??= [];
        result[setting.group].push(setting);

        return result;
    }, {});

    const updateValue = (key, value) => {
        form.setData("settings", {
            ...form.data.settings,
            [key]: value,
        });
    };

    return (
        <AdminLayout title="System Settings">
            <Head title="System Settings" />

            <div className="mx-auto max-w-7xl space-y-6">
                <section className="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div className="flex items-start gap-3">
                        <Settings className="mt-0.5 h-5 w-5 text-indigo-600" />

                        <div>
                            <h2 className="text-xl font-semibold text-slate-900">
                                Master settings
                            </h2>

                            <p className="mt-1 text-sm leading-6 text-slate-600">
                                Only predefined operational settings
                                can be edited here. Secrets and payment
                                credentials do not belong in this UI.
                            </p>
                        </div>
                    </div>
                </section>

                <form
                    onSubmit={(event) => {
                        event.preventDefault();

                        form.patch(
                            route("admin.settings.update"),
                            {
                                preserveScroll: true,
                            },
                        );
                    }}
                    className="space-y-6"
                >
                    {Object.entries(grouped).map(
                        ([group, groupSettings]) => (
                            <section
                                key={group}
                                className="rounded-xl border border-slate-200 bg-white p-6 shadow-sm"
                            >
                                <h3 className="text-lg font-semibold capitalize text-slate-900">
                                    {group.replaceAll("_", " ")}
                                </h3>

                                <div className="mt-5 grid gap-5">
                                    {groupSettings.map((setting) => (
                                        <div key={setting.key}>
                                            <label className="text-sm font-medium text-slate-800">
                                                {setting.label}
                                            </label>

                                            {setting.description && (
                                                <p className="mt-1 text-xs leading-5 text-slate-500">
                                                    {setting.description}
                                                </p>
                                            )}

                                            {setting.type ===
                                            "boolean" ? (
                                                <select
                                                    value={
                                                        form.data
                                                            .settings[
                                                            setting.key
                                                        ] ?? ""
                                                    }
                                                    onChange={(
                                                        event,
                                                    ) =>
                                                        updateValue(
                                                            setting.key,
                                                            event
                                                                .target
                                                                .value,
                                                        )
                                                    }
                                                    className="mt-2 block w-full rounded-lg border-slate-300"
                                                >
                                                    <option value="1">
                                                        Enabled
                                                    </option>
                                                    <option value="0">
                                                        Disabled
                                                    </option>
                                                </select>
                                            ) : (
                                                <input
                                                    type={
                                                        setting.type ===
                                                        "integer"
                                                            ? "number"
                                                            : setting.type ===
                                                                "email"
                                                              ? "email"
                                                              : setting.type ===
                                                                  "url"
                                                                ? "url"
                                                                : "text"
                                                    }
                                                    value={
                                                        form.data
                                                            .settings[
                                                            setting.key
                                                        ] ?? ""
                                                    }
                                                    onChange={(
                                                        event,
                                                    ) =>
                                                        updateValue(
                                                            setting.key,
                                                            event
                                                                .target
                                                                .value,
                                                        )
                                                    }
                                                    className="mt-2 block w-full rounded-lg border-slate-300"
                                                />
                                            )}

                                            {form.errors[
                                                `settings.${setting.key}`
                                            ] && (
                                                <p className="mt-1 text-sm text-rose-600">
                                                    {
                                                        form.errors[
                                                            `settings.${setting.key}`
                                                        ]
                                                    }
                                                </p>
                                            )}
                                        </div>
                                    ))}
                                </div>
                            </section>
                        ),
                    )}

                    <div className="flex justify-end">
                        <button
                            type="submit"
                            disabled={form.processing}
                            className="rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-medium text-white hover:bg-indigo-700 disabled:opacity-50"
                        >
                            Save settings
                        </button>
                    </div>
                </form>
            </div>
        </AdminLayout>
    );
}