import {
    Head,
    router,
    useForm,
} from "@inertiajs/react";
import {
    Download,
    FileText,
    LockKeyhole,
    Search,
    ShieldCheck,
    Trash2,
    Upload,
} from "lucide-react";

import ClientLayout from "@/Layouts/ClientLayout";
import InputError from "@/Components/InputError";
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
    maxUploadKb,
}) {
    const uploadForm = useForm({
        title: "",
        category:
            categories?.[0] ?? "client_upload",
        access_scope:
            scopes?.[0] ?? "client",
        file: null,
    });

    const submitUpload = (event) => {
        event.preventDefault();

        uploadForm.post(
            route(
                "client.documents.store",
            ),
            {
                forceFormData: true,
                preserveScroll: true,

                onSuccess: () => {
                    uploadForm.reset();

                    const input =
                        document.getElementById(
                            "client-document-file",
                        );

                    if (input) {
                        input.value = "";
                    }
                },
            },
        );
    };

    const submitFilters = (event) => {
        event.preventDefault();

        const data =
            new FormData(
                event.currentTarget,
            );

        router.get(
            route(
                "client.documents.index",
            ),
            {
                search:
                    data.get("search") ??
                    "",
                category:
                    data.get("category") ??
                    "",
            },
            {
                preserveState: true,
                replace: true,
            },
        );
    };

    const removeDocument = (
        documentRecord,
    ) => {
        if (
            ! window.confirm(
                `Remove "${documentRecord.title}" from active documents?`,
            )
        ) {
            return;
        }

        router.delete(
            route(
                "client.documents.destroy",
                documentRecord.uuid,
            ),
            {
                preserveScroll: true,
            },
        );
    };

    return (
        <ClientLayout>
            <Head title="My Documents" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                    <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <div className="p-6">
                            <div className="flex items-center gap-3">
                                <FileText className="h-6 w-6 text-indigo-600" />

                                <div>
                                    <h1 className="text-2xl font-semibold text-slate-900">
                                        My Documents
                                    </h1>

                                    <p className="mt-1 text-sm text-slate-500">
                                        Upload and
                                        securely access
                                        your counselling
                                        documents.
                                    </p>
                                </div>
                            </div>

                            <div className="mt-4 flex items-start gap-2 rounded-lg border border-indigo-100 bg-indigo-50 p-4 text-sm text-indigo-800">
                                <ShieldCheck className="mt-0.5 h-4 w-4 shrink-0" />

                                <p>
                                    Documents are
                                    stored privately
                                    and can only be
                                    downloaded after
                                    access checks.
                                </p>
                            </div>
                        </div>
                    </div>

                    <section className="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <div className="p-6">
                            <div className="flex items-center gap-2">
                                <Upload className="h-5 w-5 text-indigo-600" />

                                <h2 className="text-lg font-semibold text-slate-900">
                                    Upload document
                                </h2>
                            </div>

                            <p className="mt-1 text-sm text-slate-500">
                                Maximum file size:{" "}
                                {Math.round(
                                    maxUploadKb /
                                        1024,
                                )}{" "}
                                MB.
                            </p>

                            <form
                                onSubmit={
                                    submitUpload
                                }
                                className="mt-5 grid gap-4 md:grid-cols-2"
                            >
                                <div>
                                    <label className="text-sm font-medium text-slate-700">
                                        Title
                                    </label>

                                    <input
                                        value={
                                            uploadForm
                                                .data
                                                .title
                                        }
                                        onChange={(
                                            event,
                                        ) =>
                                            uploadForm.setData(
                                                "title",
                                                event
                                                    .target
                                                    .value,
                                            )
                                        }
                                        className="mt-1 w-full rounded-md border-slate-300 shadow-sm"
                                        placeholder="Optional document title"
                                    />

                                    <InputError
                                        className="mt-2"
                                        message={
                                            uploadForm
                                                .errors
                                                .title
                                        }
                                    />
                                </div>

                                <div>
                                    <label className="text-sm font-medium text-slate-700">
                                        Category
                                    </label>

                                    <select
                                        value={
                                            uploadForm
                                                .data
                                                .category
                                        }
                                        onChange={(
                                            event,
                                        ) =>
                                            uploadForm.setData(
                                                "category",
                                                event
                                                    .target
                                                    .value,
                                            )
                                        }
                                        className="mt-1 w-full rounded-md border-slate-300 shadow-sm"
                                    >
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

                                    <InputError
                                        className="mt-2"
                                        message={
                                            uploadForm
                                                .errors
                                                .category
                                        }
                                    />
                                </div>

                                <div>
                                    <label className="text-sm font-medium text-slate-700">
                                        Access
                                    </label>

                                    <select
                                        value={
                                            uploadForm
                                                .data
                                                .access_scope
                                        }
                                        onChange={(
                                            event,
                                        ) =>
                                            uploadForm.setData(
                                                "access_scope",
                                                event
                                                    .target
                                                    .value,
                                            )
                                        }
                                        className="mt-1 w-full rounded-md border-slate-300 shadow-sm"
                                    >
                                        {scopes.map(
                                            (
                                                scope,
                                            ) => (
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

                                    <InputError
                                        className="mt-2"
                                        message={
                                            uploadForm
                                                .errors
                                                .access_scope
                                        }
                                    />
                                </div>

                                <div>
                                    <label className="text-sm font-medium text-slate-700">
                                        File
                                    </label>

                                    <input
                                        id="client-document-file"
                                        type="file"
                                        accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"
                                        onChange={(
                                            event,
                                        ) =>
                                            uploadForm.setData(
                                                "file",
                                                event
                                                    .target
                                                    .files?.[0] ??
                                                    null,
                                            )
                                        }
                                        className="mt-1 block w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm"
                                    />

                                    <InputError
                                        className="mt-2"
                                        message={
                                            uploadForm
                                                .errors
                                                .file
                                        }
                                    />
                                </div>

                                <div className="md:col-span-2">
                                    <PrimaryButton
                                        disabled={
                                            uploadForm.processing
                                        }
                                    >
                                        Upload securely
                                    </PrimaryButton>
                                </div>
                            </form>
                        </div>
                    </section>

                    <form
                        onSubmit={
                            submitFilters
                        }
                        className="grid gap-4 rounded-lg bg-white p-6 shadow-sm md:grid-cols-3"
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
                                    placeholder="Title or filename"
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

                        <div className="flex flex-wrap gap-2 md:col-span-3">
                            <PrimaryButton>
                                Apply filters
                            </PrimaryButton>

                            <SecondaryButton
                                type="button"
                                onClick={() =>
                                    router.get(
                                        route(
                                            "client.documents.index",
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
                                No documents found.
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
                                        <div className="min-w-0">
                                            <h2 className="truncate font-semibold text-slate-900">
                                                {
                                                    documentRecord.title
                                                }
                                            </h2>

                                            <p className="mt-1 break-all text-sm text-slate-500">
                                                {
                                                    documentRecord.original_name
                                                }
                                            </p>

                                            <div className="mt-3 flex flex-wrap gap-2 text-xs">
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
                                                    Scan:{" "}
                                                    {formatLabel(
                                                        documentRecord.scan_status,
                                                    )}
                                                </span>
                                            </div>
                                        </div>

                                        <LockKeyhole className="h-5 w-5 text-slate-400" />
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

                                    <div className="mt-5 flex flex-wrap gap-2">
                                        <a
                                            href={route(
                                                "client.documents.download",
                                                documentRecord.uuid,
                                            )}
                                            className="inline-flex items-center gap-2 rounded-md bg-indigo-600 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-indigo-500"
                                        >
                                            <Download className="h-4 w-4" />

                                            Download
                                        </a>

                                        {documentRecord.can_delete && (
                                            <button
                                                type="button"
                                                onClick={() =>
                                                    removeDocument(
                                                        documentRecord,
                                                    )
                                                }
                                                className="inline-flex items-center gap-2 rounded-md border border-red-200 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-red-600 transition hover:bg-red-50"
                                            >
                                                <Trash2 className="h-4 w-4" />

                                                Remove
                                            </button>
                                        )}
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
        </ClientLayout>
    );
}
