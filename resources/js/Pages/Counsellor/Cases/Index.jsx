import {
    Head,
    Link,
    router,
    useForm,
} from "@inertiajs/react";
import {
    ClipboardPlus,
    Search,
} from "lucide-react";

import CounsellorLayout from "@/Layouts/CounsellorLayout";
import InputError from "@/Components/InputError";
import Pagination from "@/Components/Pagination";
import PrimaryButton from "@/Components/PrimaryButton";
import SecondaryButton from "@/Components/SecondaryButton";

export default function Index({
    cases,
    eligibleClients,
    filters,
    statuses,
    riskLevels,
}) {
    const form = useForm({
        client_profile_id: "",
        summary: "",
        formulation: "",
        risk_level: "low",
        risk_flag: false,
        risk_notes: "",
    });

    const submitFilters = (event) => {
        event.preventDefault();

        const data = new FormData(
            event.currentTarget,
        );

        router.get(
            route("counsellor.cases.index"),
            {
                search: data.get("search"),
                status: data.get("status"),
                risk: data.get("risk"),
            },
            {
                preserveState: true,
                replace: true,
            },
        );
    };

    const submitCase = (event) => {
        event.preventDefault();

        form.post(
            route("counsellor.cases.store"),
            {
                preserveScroll: true,
            },
        );
    };

    return (
        <CounsellorLayout>
            <Head title="Clinical Cases" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                    <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <div className="p-6">
                            <h1 className="text-2xl font-semibold text-slate-900">
                                Case & Clinical Records
                            </h1>

                            <p className="mt-2 text-sm text-slate-600">
                                Manage confidential cases,
                                clinical notes, goals and
                                follow-up plans.
                            </p>

                            <div className="mt-4 rounded-lg border border-indigo-100 bg-indigo-50 p-4 text-sm text-indigo-800">
                                Signed clinical notes are
                                permanently locked. Clinical
                                record access is logged.
                            </div>
                        </div>
                    </div>

                    <form
                        onSubmit={submitFilters}
                        className="grid gap-4 rounded-lg bg-white p-6 shadow-sm md:grid-cols-4"
                    >
                        <div className="relative md:col-span-2">
                            <Search className="absolute left-3 top-3 h-4 w-4 text-slate-400" />

                            <input
                                name="search"
                                defaultValue={
                                    filters.search
                                }
                                className="w-full rounded-md border-slate-300 pl-9 shadow-sm"
                                placeholder="Client or case summary"
                            />
                        </div>

                        <select
                            name="status"
                            defaultValue={
                                filters.status
                            }
                            className="rounded-md border-slate-300 shadow-sm"
                        >
                            <option value="">
                                All statuses
                            </option>

                            {statuses.map(
                                (status) => (
                                    <option
                                        key={status}
                                        value={status}
                                    >
                                        {status.replaceAll(
                                            "_",
                                            " ",
                                        )}
                                    </option>
                                ),
                            )}
                        </select>

                        <select
                            name="risk"
                            defaultValue={
                                filters.risk
                            }
                            className="rounded-md border-slate-300 shadow-sm"
                        >
                            <option value="">
                                All risk levels
                            </option>

                            {riskLevels.map(
                                (risk) => (
                                    <option
                                        key={risk}
                                        value={risk}
                                    >
                                        {risk}
                                    </option>
                                ),
                            )}
                        </select>

                        <div className="flex gap-2 md:col-span-4">
                            <PrimaryButton>
                                Apply filters
                            </PrimaryButton>

                            <SecondaryButton
                                type="button"
                                onClick={() =>
                                    router.get(
                                        route(
                                            "counsellor.cases.index",
                                        ),
                                    )
                                }
                            >
                                Reset
                            </SecondaryButton>
                        </div>
                    </form>

                    <form
                        onSubmit={submitCase}
                        className="rounded-lg bg-white p-6 shadow-sm"
                    >
                        <div className="flex items-center gap-2">
                            <ClipboardPlus className="h-5 w-5 text-indigo-600" />

                            <h2 className="text-lg font-semibold text-slate-900">
                                Open clinical case
                            </h2>
                        </div>

                        <div className="mt-4 grid gap-4 md:grid-cols-2">
                            <div>
                                <label className="text-sm font-medium text-slate-700">
                                    Client
                                </label>

                                <select
                                    value={
                                        form.data
                                            .client_profile_id
                                    }
                                    onChange={(
                                        event,
                                    ) =>
                                        form.setData(
                                            "client_profile_id",
                                            event.target
                                                .value,
                                        )
                                    }
                                    className="mt-1 w-full rounded-md border-slate-300 shadow-sm"
                                >
                                    <option value="">
                                        Select client
                                    </option>

                                    {eligibleClients.map(
                                        (client) => (
                                            <option
                                                key={
                                                    client.id
                                                }
                                                value={
                                                    client.id
                                                }
                                            >
                                                {
                                                    client.name
                                                }
                                            </option>
                                        ),
                                    )}
                                </select>

                                <InputError
                                    className="mt-2"
                                    message={
                                        form.errors
                                            .client_profile_id
                                    }
                                />
                            </div>

                            <div>
                                <label className="text-sm font-medium text-slate-700">
                                    Risk level
                                </label>

                                <select
                                    value={
                                        form.data
                                            .risk_level
                                    }
                                    onChange={(
                                        event,
                                    ) =>
                                        form.setData(
                                            "risk_level",
                                            event.target
                                                .value,
                                        )
                                    }
                                    className="mt-1 w-full rounded-md border-slate-300 shadow-sm"
                                >
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

                            <textarea
                                rows="3"
                                value={
                                    form.data.summary
                                }
                                onChange={(event) =>
                                    form.setData(
                                        "summary",
                                        event.target.value,
                                    )
                                }
                                className="rounded-md border-slate-300 shadow-sm md:col-span-2"
                                placeholder="Case summary"
                            />

                            <textarea
                                rows="3"
                                value={
                                    form.data
                                        .formulation
                                }
                                onChange={(event) =>
                                    form.setData(
                                        "formulation",
                                        event.target.value,
                                    )
                                }
                                className="rounded-md border-slate-300 shadow-sm md:col-span-2"
                                placeholder="Initial formulation"
                            />

                            <label className="flex items-center gap-2 text-sm text-slate-700">
                                <input
                                    type="checkbox"
                                    checked={
                                        form.data
                                            .risk_flag
                                    }
                                    onChange={(
                                        event,
                                    ) =>
                                        form.setData(
                                            "risk_flag",
                                            event.target
                                                .checked,
                                        )
                                    }
                                    className="rounded border-slate-300"
                                />
                                Active risk flag
                            </label>

                            <input
                                value={
                                    form.data
                                        .risk_notes
                                }
                                onChange={(event) =>
                                    form.setData(
                                        "risk_notes",
                                        event.target.value,
                                    )
                                }
                                className="rounded-md border-slate-300 shadow-sm"
                                placeholder="Risk notes"
                            />
                        </div>

                        <PrimaryButton
                            className="mt-4"
                            disabled={
                                form.processing
                            }
                        >
                            Open case
                        </PrimaryButton>
                    </form>

                    <div className="grid gap-4">
                        {cases.data.map(
                            (caseRecord) => (
                                <Link
                                    key={
                                        caseRecord.id
                                    }
                                    href={route(
                                        "counsellor.cases.show",
                                        caseRecord.id,
                                    )}
                                    className="block rounded-lg bg-white p-6 shadow-sm transition hover:shadow"
                                >
                                    <div className="flex justify-between gap-4">
                                        <div>
                                            <h3 className="font-semibold text-slate-900">
                                                {caseRecord
                                                    .client_profile
                                                    .user
                                                    ?.name ??
                                                    "Client"}
                                            </h3>

                                            <p className="mt-1 text-sm text-slate-500">
                                                {caseRecord
                                                    .client_profile
                                                    .user
                                                    ?.email ??
                                                    "Not provided"}
                                            </p>
                                        </div>

                                        <div className="text-right text-xs text-slate-500">
                                            <div>
                                                {
                                                    caseRecord.status
                                                }
                                            </div>

                                            <div>
                                                {
                                                    caseRecord.risk_level
                                                }{" "}
                                                risk
                                            </div>
                                        </div>
                                    </div>

                                    <p className="mt-4 line-clamp-2 text-sm text-slate-600">
                                        {caseRecord.summary ||
                                            "No case summary recorded."}
                                    </p>

                                    <div className="mt-4 flex gap-4 text-xs text-slate-500">
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
        </CounsellorLayout>
    );
}
