import InputError from "@/Components/InputError";
import ComplianceLayout from "@/Layouts/ComplianceLayout";
import { Head, router, useForm } from "@inertiajs/react";

function humanize(value) {
    return String(value ?? "")
        .replaceAll("_", " ")
        .replace(/\b\w/g, (character) => character.toUpperCase());
}

function PolicyCard({ policy, actions }) {
    const form = useForm({
        retention_days: policy.retention_days ?? "",
        action: policy.action,
        enabled: Boolean(policy.enabled),
        automatic_execution: Boolean(policy.automatic_execution),
        legal_basis: policy.legal_basis ?? "",
    });

    const save = (event) => {
        event.preventDefault();

        form.transform((data) => ({
            ...data,
            retention_days:
                data.retention_days === ""
                    ? null
                    : Number(data.retention_days),
        }));

        form.patch(route("compliance.retention.update", policy.uuid));
    };

    return (
        <form
            onSubmit={save}
            className="rounded-xl border border-slate-200 bg-white p-6 shadow-sm"
        >
            <div className="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                <div>
                    <h3 className="font-semibold text-slate-900">
                        {policy.name}
                    </h3>

                    <p className="mt-1 text-xs font-medium uppercase tracking-wide text-indigo-600">
                        {humanize(policy.category)}
                    </p>

                    <p className="mt-2 text-sm leading-6 text-slate-500">
                        {policy.description}
                    </p>
                </div>

                <span
                    className={`rounded-full px-3 py-1 text-xs font-semibold ${
                        policy.enabled
                            ? "bg-emerald-50 text-emerald-700"
                            : "bg-slate-100 text-slate-600"
                    }`}
                >
                    {policy.enabled ? "Enabled" : "Disabled"}
                </span>
            </div>

            <div className="mt-5 grid gap-4 md:grid-cols-2">
                <div>
                    <label className="text-sm font-medium text-slate-700">
                        Retention days
                    </label>
                    <input
                        type="number"
                        min="1"
                        max="36500"
                        value={form.data.retention_days}
                        onChange={(event) =>
                            form.setData("retention_days", event.target.value)
                        }
                        className="mt-1 block w-full rounded-md border-slate-300"
                    />
                    <InputError
                        message={form.errors.retention_days}
                        className="mt-2"
                    />
                </div>

                <div>
                    <label className="text-sm font-medium text-slate-700">
                        Action
                    </label>
                    <select
                        value={form.data.action}
                        onChange={(event) =>
                            form.setData("action", event.target.value)
                        }
                        className="mt-1 block w-full rounded-md border-slate-300"
                    >
                        {actions.map((action) => (
                            <option key={action} value={action}>
                                {humanize(action)}
                            </option>
                        ))}
                    </select>
                </div>
            </div>

            <div className="mt-4 space-y-3">
                <label className="flex items-center gap-3">
                    <input
                        type="checkbox"
                        checked={form.data.enabled}
                        onChange={(event) =>
                            form.setData("enabled", event.target.checked)
                        }
                        className="rounded border-slate-300 text-indigo-600"
                    />
                    <span className="text-sm text-slate-700">
                        Enable policy
                    </span>
                </label>

                <label className="flex items-center gap-3">
                    <input
                        type="checkbox"
                        checked={form.data.automatic_execution}
                        onChange={(event) =>
                            form.setData(
                                "automatic_execution",
                                event.target.checked,
                            )
                        }
                        disabled={!policy.automatic_allowed}
                        className="rounded border-slate-300 text-indigo-600 disabled:opacity-50"
                    />

                    <span className="text-sm text-slate-700">
                        Automatic execution
                        {!policy.automatic_allowed &&
                            " (not permitted for this category)"}
                    </span>
                </label>

                <InputError
                    message={form.errors.automatic_execution}
                    className="mt-2"
                />
            </div>

            <div className="mt-4">
                <label className="text-sm font-medium text-slate-700">
                    Approved legal / policy basis
                </label>

                <textarea
                    rows="4"
                    value={form.data.legal_basis}
                    onChange={(event) =>
                        form.setData("legal_basis", event.target.value)
                    }
                    className="mt-1 block w-full rounded-md border-slate-300"
                />

                <InputError
                    message={form.errors.legal_basis}
                    className="mt-2"
                />
            </div>

            <div className="mt-5 flex flex-wrap gap-3">
                <button
                    disabled={form.processing}
                    className="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white"
                >
                    Save policy
                </button>

                <button
                    type="button"
                    onClick={() =>
                        router.post(
                            route("compliance.retention.dry-run", policy.uuid),
                        )
                    }
                    disabled={!policy.enabled}
                    className="rounded-md border border-indigo-300 px-4 py-2 text-sm font-medium text-indigo-700 disabled:cursor-not-allowed disabled:opacity-50"
                >
                    Dry run
                </button>

                <button
                    type="button"
                    disabled={
                        !policy.enabled ||
                        !policy.automatic_execution ||
                        !policy.automatic_allowed
                    }
                    onClick={() => {
                        if (
                            window.confirm(
                                "Execute this approved retention policy now?",
                            )
                        ) {
                            router.post(
                                route(
                                    "compliance.retention.execute",
                                    policy.uuid,
                                ),
                            );
                        }
                    }}
                    className="rounded-md bg-rose-600 px-4 py-2 text-sm font-medium text-white disabled:cursor-not-allowed disabled:opacity-40"
                >
                    Execute
                </button>
            </div>
        </form>
    );
}

export default function Index({ policies, runs, actions }) {
    return (
        <ComplianceLayout title="Retention Management">
            <Head title="Retention Management" />

            <div className="mx-auto max-w-7xl space-y-6">
                <div className="rounded-xl border border-amber-200 bg-amber-50 p-5">
                    <h2 className="font-semibold text-amber-950">
                        Review-first retention
                    </h2>

                    <p className="mt-2 text-sm leading-6 text-amber-800">
                        Clinical, financial, document, audit and operational
                        categories cannot be automatically executed by the web
                        interface. Dry-run them, review the candidates and retain
                        evidence of every run.
                    </p>
                </div>

                <div className="grid gap-6 xl:grid-cols-2">
                    {policies.map((policy) => (
                        <PolicyCard
                            key={policy.uuid}
                            policy={policy}
                            actions={actions}
                        />
                    ))}
                </div>

                <div className="rounded-xl border border-slate-200 bg-white shadow-sm">
                    <div className="border-b border-slate-200 px-6 py-4">
                        <h3 className="font-semibold text-slate-900">
                            Recent retention runs
                        </h3>
                    </div>

                    <div className="overflow-x-auto">
                        <table className="min-w-full divide-y divide-slate-200 text-sm">
                            <thead className="bg-slate-50">
                                <tr>
                                    <th className="px-4 py-3 text-left">
                                        Policy
                                    </th>
                                    <th className="px-4 py-3 text-left">
                                        Mode
                                    </th>
                                    <th className="px-4 py-3 text-left">
                                        Status
                                    </th>
                                    <th className="px-4 py-3 text-right">
                                        Candidates
                                    </th>
                                    <th className="px-4 py-3 text-right">
                                        Processed
                                    </th>
                                </tr>
                            </thead>

                            <tbody className="divide-y divide-slate-100">
                                {runs.map((run) => (
                                    <tr key={run.uuid}>
                                        <td className="px-4 py-3">
                                            {run.policy?.name ??
                                                "Unknown policy"}
                                        </td>
                                        <td className="px-4 py-3">
                                            {humanize(run.mode)}
                                        </td>
                                        <td className="px-4 py-3">
                                            {humanize(run.status)}
                                        </td>
                                        <td className="px-4 py-3 text-right">
                                            {run.candidate_count}
                                        </td>
                                        <td className="px-4 py-3 text-right">
                                            {run.processed_count}
                                        </td>
                                    </tr>
                                ))}

                                {runs.length === 0 && (
                                    <tr>
                                        <td
                                            colSpan="5"
                                            className="px-4 py-8 text-center text-slate-500"
                                        >
                                            No retention runs yet.
                                        </td>
                                    </tr>
                                )}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </ComplianceLayout>
    );
}
