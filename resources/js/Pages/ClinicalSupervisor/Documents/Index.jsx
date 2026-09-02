import {
    Head,
    router,
} from "@inertiajs/react";
import {
    Download,
    FileText,
    LockKeyhole,
    Search,
    ShieldCheck,
} from "lucide-react";

import AdminLayout from "@/Layouts/AdminLayout";
import Pagination from "@/Components/Pagination";
import PrimaryButton from "@/Components/PrimaryButton";
import SecondaryButton from "@/Components/SecondaryButton";

const formatLabel = (value) =>
    String(value ?? "")
        .replaceAll("_", " ")
        .replace(/\b\w/g, (letter) =>
            letter.toUpperCase(),
        );

const formatBytes = (bytes) => {
    const value = Number(bytes ?? 0);

    if (value < 1024) {
        return `${value} B`;
    }

    if (value < 1024 * 1024) {
        return `${(value / 1024).toFixed(1)} KB`;
    }

    return `${(
        value /
        (1024 * 1024)
    ).toFixed(1)} MB`;
};

const scanClass = (status) => {
    const classes = {
        clean:
            "bg-emerald-100 text-emerald-700",
        pending:
            "bg-amber-100 text-amber-700",
        unavailable:
            "bg-slate-100 text-slate-700",
        quarantined:
            "bg-red-100 text-red-700",
        failed:
            "bg-red-100 text-red-700",
    };

    return (
        classes[status] ??
        "bg-slate-100 text-slate-700"
    );
};

export default function Index({
    documents,
    filters,
    categories,
    scopes,
}) {
    const submitFilters = (event) => {
        event.preventDefault();

        const data =
            new FormData(
                event.currentTarget,
            );

        router.get(
            route(
                "clinical-supervisor.documents.index",
            ),
            {
                search:
                    data.get("search") ??
                    "",

                category:
                    data.get("category") ??
                    "",

                scope:
                    data.get("scope") ??
                    "",
            },
            {
                preserveState: true,
                replace: true,
            },
        );
    };

    return (
        <AdminLayout>
            <Head title="Clinical Documents" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                    <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <div className="p-6">
                            <div className="flex items-center gap-3">
                                <FileText className="h-6 w-6 text-indigo-600" />

                                <div>
                                    <h1 className="text-2xl font-semibold text-slate-900">
                                        Clinical
                                        Documents
                                    </h1>

                                    <p className="mt-1 text-sm text-slate-500">
                                        Read-only
                                        document access
                                        for clinical
                                        supervision.
                                    </p>
                                </div>
                            </div>

                            <div className="mt-4 flex items-start gap-2 rounded-lg border border-indigo-100 bg-indigo-50 p-4 text-sm text-indigo-800">
                                <ShieldCheck className="mt-0.5 h-4 w-4 shrink-0" />

                                <p>
                                    Supervisor
                                    downloads are
                                    access logged. No
                                    upload, modification
                                    or deletion actions
                                    are available here.
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
                                        filters?.search ??
                                        ""
                                    }
                                    className="w-full rounded-md border-slate-300 pl-9 shadow-sm"
                                    placeholder="Document title or filename"
                                />
                            </div>
                        </div>

                        <div>
                            <label className="text-sm font-medium text-slate-700">
                                Category
                            </label>

                            <select
                                name="category"
                                defaultValue={
                                    filters?.category ??
                                    ""
                                }
                                className="mt-1 w-full rounded-md border-slate-300 shadow-sm"
                            >
                                <option value="">
                                    All categories
                                </option>

                                {categories.map(
                                    (
                                        category,
                                    ) => (
                                        <option
                                            key={
                                                category
                                            }
                                            value={
                                                category
                                            }
                                        >
                                            {formatLabel(
                                                category,
                                            )}
                                        </option>
                                    ),
                                )}
                            </select>
                        </div>

                        <div>
                            <label className="text-sm font-medium text-slate-700">
                                Access scope
                            </label>

                            <select
                                name="scope"
                                defaultValue={
                                    filters?.scope ??
                                    ""
                                }
                                className="mt-1 w-full rounded-md border-slate-300 shadow-sm"
                            >
                                <option value="">
                                    All scopes
                                </option>

                                {scopes.map(
                                    (scope) => (
                                        <option
                                            key={
                                                scope
                                            }
                                            value={
                                                scope
                                            }
                                        >
                                            {formatLabel(
                                                scope,
                                            )}
                                        </option>
                                    ),
                                )}
                            </select>
                        </div>

                        <div className="flex gap-2 md:col-span-4">
                            <PrimaryButton>
                                Apply filters
                            </PrimaryButton>

                            <SecondaryButton
                                type="button"
                                onClick={() =>
                                    router.get(
                                        route(
                                            "clinical-supervisor.documents.index",
                                        ),
                                    )
                                }
                            >
                                Reset
                            </SecondaryButton>
                        </div>
                    </form>

                    <div className="grid gap-4">
                        {documents.data.length ===
                            0 && (
                            <div className="rounded-lg bg-white p-8 text-center text-sm text-slate-500 shadow-sm">
                                No clinical
                                documents match the
                                current filters.
                            </div>
                        )}

                        {documents.data.map(
                            (
                                documentRecord,
                            ) => (
                                <article
                                    key={
                                        documentRecord.uuid
                                    }
                                    className="rounded-lg bg-white p-6 shadow-sm"
                                >
                                    <div className="flex flex-wrap items-start justify-between gap-4">
                                        <div>
                                            <h2 className="font-semibold text-slate-900">
                                                {
                                                    documentRecord.title
                                                }
                                            </h2>

                                            <p className="mt-1 text-sm text-slate-500">
                                                Client:{" "}
                                                {documentRecord
                                                    .client_profile
                                                    ?.user
                                                    ?.name ??
                                                    "Not provided"}
                                            </p>

                                            <p className="mt-1 text-sm text-slate-500">
                                                Counsellor:{" "}
                                                {documentRecord
                                                    .client_case
                                                    ?.counsellor_profile
                                                    ?.user
                                                    ?.name ??
                                                    "Not provided"}
                                            </p>

                                            <p className="mt-1 break-all text-xs text-slate-400">
                                                {
                                                    documentRecord.original_name
                                                }
                                            </p>
                                        </div>

                                        <LockKeyhole className="h-5 w-5 text-slate-400" />
                                    </div>

                                    <div className="mt-4 flex flex-wrap gap-2 text-xs">
                                        <span className="rounded-full bg-indigo-50 px-3 py-1 font-medium text-indigo-700">
                                            {formatLabel(
                                                documentRecord.category,
                                            )}
                                        </span>

                                        <span className="rounded-full bg-slate-100 px-3 py-1 font-medium text-slate-700">
                                            {formatLabel(
                                                documentRecord.access_scope,
                                            )}
                                        </span>

                                        <span
                                            className={`rounded-full px-3 py-1 font-medium ${scanClass(
                                                documentRecord.scan_status,
                                            )}`}
                                        >
                                            {formatLabel(
                                                documentRecord.scan_status,
                                            )}
                                        </span>
                                    </div>

                                    <div className="mt-4 grid gap-3 text-xs text-slate-500 sm:grid-cols-3">
                                        <div>
                                            Size:{" "}
                                            {formatBytes(
                                                documentRecord.size_bytes,
                                            )}
                                        </div>

                                        <div>
                                            Uploaded by:{" "}
                                            {documentRecord
                                                .uploader
                                                ?.name ??
                                                "Not provided"}
                                        </div>

                                        <div>
                                            Uploaded:{" "}
                                            {documentRecord.created_at ??
                                                "Not provided"}
                                        </div>
                                    </div>

                                    <div className="mt-5">
                                        <a
                                            href={route(
                                                "clinical-supervisor.documents.download",
                                                documentRecord.uuid,
                                            )}
                                            className="inline-flex items-center gap-2 rounded-md bg-indigo-600 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-indigo-500"
                                        >
                                            <Download className="h-4 w-4" />

                                            Download
                                        </a>
                                    </div>
                                </article>
                            ),
                        )}
                    </div>

                    <Pagination
                        links={
                            documents.links
                        }
                    />
                </div>
            </div>
        </AdminLayout>
    );
}
