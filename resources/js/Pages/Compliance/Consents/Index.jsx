import Pagination from "@/Components/Pagination";
import ComplianceLayout from "@/Layouts/ComplianceLayout";
import { Head, Link, router } from "@inertiajs/react";
import { useState } from "react";

function humanize(value) {
    if (value === null || value === undefined || value === "") {
        return "Not provided";
    }

    if (typeof value === "boolean") {
        return value ? "Yes" : "No";
    }

    return String(value)
        .replaceAll("_", " ")
        .replace(/\b\w/g, (character) => character.toUpperCase());
}

export default function Index({
    available,
    consents,
    filters,
    types,
}) {
    const [form, setForm] = useState({
        search: filters.search ?? "",
        type: filters.type ?? "",
    });

    const submit = (event) => {
        event.preventDefault();

        router.get(route("compliance.consents.index"), form, {
            preserveState: true,
            replace: true,
        });
    };

    return (
        <ComplianceLayout title="Consent History">
            <Head title="Consent History" />

            <div className="mx-auto max-w-7xl space-y-6">
                <div>
                    <h2 className="text-xl font-semibold text-slate-900">
                        Consent History
                    </h2>
                    <p className="mt-1 text-sm text-slate-500">
                        Compliance visibility over the existing M10 consent
                        records. M18 does not create a second consent subsystem.
                    </p>
                </div>

                {!available ? (
                    <div className="rounded-xl border border-amber-200 bg-amber-50 p-6 text-sm text-amber-800">
                        The current client-consent schema could not be resolved.
                        Inspect the existing M10 table before changing this
                        controller.
                    </div>
                ) : (
                    <>
                        <form
                            onSubmit={submit}
                            className="flex flex-col gap-3 rounded-xl border border-slate-200 bg-white p-5 shadow-sm md:flex-row"
                        >
                            <input
                                value={form.search}
                                onChange={(event) =>
                                    setForm({
                                        ...form,
                                        search: event.target.value,
                                    })
                                }
                                placeholder="Client name or email"
                                className="flex-1 rounded-md border-slate-300 text-sm"
                            />

                            <select
                                value={form.type}
                                onChange={(event) =>
                                    setForm({
                                        ...form,
                                        type: event.target.value,
                                    })
                                }
                                className="rounded-md border-slate-300 text-sm"
                            >
                                <option value="">All consent types</option>
                                {types.map((type) => (
                                    <option key={type} value={type}>
                                        {humanize(type)}
                                    </option>
                                ))}
                            </select>

                            <button
                                className="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white"
                            >
                                Apply filters
                            </button>

                            <Link
                                href={route("compliance.consents.index")}
                                className="rounded-md border border-slate-300 px-4 py-2 text-center text-sm"
                            >
                                Reset
                            </Link>
                        </form>

                        <div className="space-y-3">
                            {consents.data.map((consent) => (
                                <div
                                    key={consent.id}
                                    className="rounded-xl border border-slate-200 bg-white p-5 shadow-sm"
                                >
                                    <p className="font-semibold text-slate-900">
                                        {consent.client_name}
                                    </p>

                                    <p className="mt-1 text-sm text-slate-500">
                                        {consent.client_email}
                                    </p>

                                    <div className="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                                        {Object.entries(consent)
                                            .filter(
                                                ([key]) =>
                                                    ![
                                                        "id",
                                                        "client_name",
                                                        "client_email",
                                                        "client_uuid",
                                                    ].includes(key),
                                            )
                                            .map(([key, value]) => (
                                                <div
                                                    key={key}
                                                    className="rounded-lg bg-slate-50 p-3"
                                                >
                                                    <p className="text-xs font-medium uppercase tracking-wide text-slate-500">
                                                        {humanize(key)}
                                                    </p>
                                                    <p className="mt-1 break-words text-sm text-slate-800">
                                                        {humanize(value)}
                                                    </p>
                                                </div>
                                            ))}
                                    </div>
                                </div>
                            ))}

                            {consents.data.length === 0 && (
                                <div className="rounded-xl border border-slate-200 bg-white p-8 text-center text-sm text-slate-500">
                                    No consent history matches the filters.
                                </div>
                            )}
                        </div>

                        <Pagination links={consents.links} />
                    </>
                )}
            </div>
        </ComplianceLayout>
    );
}
