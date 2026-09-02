import {
    Head,
    Link,
    router,
} from "@inertiajs/react";
import {
    BriefcaseMedical,
    Search,
    ShieldCheck,
} from "lucide-react";

import AdminLayout from "@/Layouts/AdminLayout";
import Pagination from "@/Components/Pagination";
import PrimaryButton from "@/Components/PrimaryButton";
import SecondaryButton from "@/Components/SecondaryButton";

const statusClass = (status) => {
    const classes = {
        open:
            "bg-emerald-100 text-emerald-700",
        on_hold:
            "bg-amber-100 text-amber-700",
        closed:
            "bg-slate-100 text-slate-700",
    };

    return (
        classes[status] ??
        "bg-slate-100 text-slate-700"
    );
};

const riskClass = (risk) => {
    const classes = {
        low:
            "bg-emerald-100 text-emerald-700",
        moderate:
            "bg-amber-100 text-amber-700",
        high:
            "bg-orange-100 text-orange-700",
        urgent:
            "bg-red-100 text-red-700",
    };

    return (
        classes[risk] ??
        "bg-slate-100 text-slate-700"
    );
};

export default function Index({
    cases,
    filters,
    statuses,
    riskLevels,
}) {
    const submitFilters = (event) => {
        event.preventDefault();

        const data = new FormData(
            event.currentTarget,
        );

        router.get(
            route(
                "clinical-supervisor.cases.index",
            ),
            {
                search:
                    data.get("search"),
                status:
                    data.get("status"),
                risk:
                    data.get("risk"),
            },
            {
                preserveState: true,
                replace: true,
            },
        );
    };

    return (
        <AdminLayout>
            <Head title="Clinical Supervision" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                    <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <div className="p-6">
                            <div className="flex items-center gap-2">
                                <BriefcaseMedical className="h-6 w-6 text-indigo-600" />

                                <h1 className="text-2xl font-semibold text-slate-900">
                                    Clinical
                                    Supervision
                                </h1>
                            </div>

                            <p className="mt-2 text-sm text-slate-600">
                                Review confidential
                                clinical case records
                                and clinical-note
                                version history.
                            </p>

                            <div className="mt-4 flex items-start gap-2 rounded-lg border border-indigo-100 bg-indigo-50 p-4 text-sm text-indigo-800">
                                <ShieldCheck className="mt-0.5 h-4 w-4 shrink-0" />

                                <p>
                                    Clinical
                                    supervision is
                                    read-only in M12.
                                    Access to case
                                    records is logged.
                                </p>
                            </div>
                        </div>
                    </div>

                    <form
                        onSubmit={
                            submitFilters
                        }
                        className="grid gap-4 rounded-lg bg-white p-6 shadow-sm md:grid-cols-4"
                    >
                        <div className="md:col-span-2">
                            <label className="text-sm font-medium text-slate-700">
                                Search
                            </label>

                            <div className="relative mt-1">
                                <Search className="absolute left-3 top-3 h-4 w-4 text-slate-400" />

                                <input
                                    name="search"
                                    defaultValue={
                                        filters.search
                                    }
                                    className="w-full rounded-md border-slate-300 pl-9 shadow-sm"
                                    placeholder="Client, counsellor or case summary"
                                />
                            </div>
                        </div>

                        <div>
                            <label className="text-sm font-medium text-slate-700">
                                Status
                            </label>

                            <select
                                name="status"
                                defaultValue={
                                    filters.status
                                }
                                className="mt-1 w-full rounded-md border-slate-300 shadow-sm"
                            >
                                <option value="">
                                    All statuses
                                </option>

                                {statuses.map(
                                    (status) => (
                                        <option
                                            key={
                                                status
                                            }
                                            value={
                                                status
                                            }
                                        >
                                            {status.replaceAll(
                                                "_",
                                                " ",
                                            )}
                                        </option>
                                    ),
                                )}
                            </select>
                        </div>

                        <div>
                            <label className="text-sm font-medium text-slate-700">
                                Risk level
                            </label>

                            <select
                                name="risk"
                                defaultValue={
                                    filters.risk
                                }
                                className="mt-1 w-full rounded-md border-slate-300 shadow-sm"
                            >
                                <option value="">
                                    All risk
                                    levels
                                </option>

                                {riskLevels.map(
                                    (risk) => (
                                        <option
                                            key={
                                                risk
                                            }
                                            value={
                                                risk
                                            }
                                        >
                                            {risk}
                                        </option>
                                    ),
                                )}
                            </select>
                        </div>

                        <div className="flex flex-wrap gap-2 md:col-span-4">
                            <PrimaryButton>
                                Apply filters
                            </PrimaryButton>

                            <SecondaryButton
                                type="button"
                                onClick={() =>
                                    router.get(
                                        route(
                                            "clinical-supervisor.cases.index",
                                        ),
                                    )
                                }
                            >
                                Reset
                            </SecondaryButton>
                        </div>
                    </form>

                    <div className="grid gap-4">
                        {cases.data.length ===
                            0 && (
                            <div className="rounded-lg bg-white p-8 text-center text-sm text-slate-500 shadow-sm">
                                No clinical
                                cases match the
                                current filters.
                            </div>
                        )}

                        {cases.data.map(
                            (caseRecord) => (
                                <Link
                                    key={
                                        caseRecord.id
                                    }
                                    href={route(
                                        "clinical-supervisor.cases.show",
                                        caseRecord.id,
                                    )}
                                    className="block rounded-lg bg-white p-6 shadow-sm transition hover:shadow"
                                >
                                    <div className="flex flex-wrap items-start justify-between gap-4">
                                        <div>
                                            <h2 className="font-semibold text-slate-900">
                                                {caseRecord
                                                    .client_profile
                                                    ?.user
                                                    ?.name ??
                                                    "Client"}
                                            </h2>

                                            <p className="mt-1 text-sm text-slate-500">
                                                Counsellor:{" "}
                                                {caseRecord
                                                    .counsellor_profile
                                                    ?.user
                                                    ?.name ??
                                                    "Not provided"}
                                            </p>

                                            <p className="mt-1 text-xs text-slate-400">
                                                {caseRecord
                                                    .client_profile
                                                    ?.user
                                                    ?.email ??
                                                    "Not provided"}
                                            </p>
                                        </div>

                                        <div className="flex flex-wrap gap-2">
                                            <span
                                                className={`rounded-full px-3 py-1 text-xs font-medium ${statusClass(
                                                    caseRecord.status,
                                                )}`}
                                            >
                                                {caseRecord.status.replaceAll(
                                                    "_",
                                                    " ",
                                                )}
                                            </span>

                                            <span
                                                className={`rounded-full px-3 py-1 text-xs font-medium ${riskClass(
                                                    caseRecord.risk_level,
                                                )}`}
                                            >
                                                {
                                                    caseRecord.risk_level
                                                }{" "}
                                                risk
                                            </span>
                                        </div>
                                    </div>

                                    <p className="mt-4 line-clamp-3 text-sm leading-6 text-slate-600">
                                        {caseRecord.summary ||
                                            "No case summary recorded."}
                                    </p>

                                    <div className="mt-4 flex flex-wrap gap-5 text-xs text-slate-500">
                                        <span>
                                            {
                                                caseRecord.goals_count
                                            }{" "}
                                            goals
                                        </span>

                                        <span>
                                            {
                                                caseRecord.follow_ups_count
                                            }{" "}
                                            follow-ups
                                        </span>

                                        <span>
                                            {
                                                caseRecord.clinical_notes_count
                                            }{" "}
                                            clinical
                                            notes
                                        </span>
                                    </div>
                                </Link>
                            ),
                        )}
                    </div>

                    <Pagination
                        links={cases.links}
                    />
                </div>
            </div>
        </AdminLayout>
    );
}
