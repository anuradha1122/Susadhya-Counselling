import {
    Head,
    router,
    useForm,
} from "@inertiajs/react";
import {
    Download,
    FileText,
    Search,
    ShieldCheck,
    Trash2,
    Upload,
} from "lucide-react";

import AdminLayout from "@/Layouts/AdminLayout";
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

export default function Index({
    documents,
    clients,
    filters,
    categories,
    scopes,
    maxUploadKb,
}) {
    const uploadForm = useForm({
        client_profile_id:
            clients?.[0]?.id ?? "",
        title: "",
        category:
            categories?.[0] ??
            "administrative",
        access_scope:
            scopes?.[0] ?? "admin",
        file: null,
    });

    const submitUpload = (event) => {
        event.preventDefault();

        uploadForm.post(
            route(
                "admin.documents.store",
            ),
            {
                forceFormData: true,
                preserveScroll: true,

                onSuccess: () => {
                    uploadForm.reset(
                        "title",
                        "file",
                    );

                    const input =
                        document.getElementById(
                            "admin-document-file",
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
                "admin.documents.index",
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
                "admin.documents.destroy",
                documentRecord.uuid,
            ),
            {
                preserveScroll: true,
            },
        );
    };

    return (
        <AdminLayout>
            <Head title="Documents" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                    <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <div className="p-6">
                            <div className="flex items-center gap-3">
                                <FileText className="h-6 w-6 text-indigo-600" />

                                <div>
                                    <h1 className="text-2xl font-semibold text-slate-900">
                                        Administrative
                                        Documents
                                    </h1>

                                    <p className="mt-1 text-sm text-slate-500">
                                        Manage
                                        administrative
                                        and shared client
                                        documents.
                                    </p>
                                </div>
                            </div>

                            <div className="mt-4 flex items-start gap-2 rounded-lg border border-indigo-100 bg-indigo-50 p-4 text-sm text-indigo-800">
                                <ShieldCheck className="mt-0.5 h-4 w-4 shrink-0" />

                                <p>
                                    Clinical-only
                                    documents are not
                                    exposed through
                                    ordinary admin
                                    document access.
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
                                Maximum size:{" "}
                                {Math.round(
                                    maxUploadKb /
                                        1024,
                                )}{" "}
                                MB.
                            </p>

                            {clients.length === 0 ? (
                                <div className="mt-5 rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-700">
                                    No client profiles
                                    are available.
                                </div>
                            ) : (
                                <form
                                    onSubmit={
                                        submitUpload
                                    }
                                    className="mt-5 grid gap-4 md:grid-cols-2"
                                >
                                    <div className="md:col-span-2">
                                        <label className="text-sm font-medium text-slate-700">
                                            Client
                                        </label>

                                        <select
                                            value={
                                                uploadForm
                                                    .data
                                                    .client_profile_id
                                            }
                                            onChange={(
                                                event,
                                            ) =>
                                                uploadForm.setData(
                                                    "client_profile_id",
                                                    event
                                                        .target
                                                        .value,
                                                )
                                            }
                                            className="mt-1 w-full rounded-md border-slate-300 shadow-sm"
                                        >
                                            {clients.map(
                                                (
                                                    client,
                                                ) => (
                                                    <option
                                                        key={
                                                            client.id
                                                        }
                                                        value={
                                                            client.id
                                                        }
                                                    >
                                                        {client
                                                            .user
                                                            ?.name ??
                                                            `Client #${client.id}`}
                                                        {client
                                                            .user
                                                            ?.email
                                                            ? ` — ${client.user.email}`
                                                            : ""}
                                                    </option>
                                                ),
                                            )}
                                        </select>

                                        <InputError
                                            className="mt-2"
                                            message={
                                                uploadForm
                                                    .errors
                                                    .client_profile_id
                                            }
                                        />
                                    </div>

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
                                    </div>

                                    <div>
                                        <label className="text-sm font-medium text-slate-700">
                                            Access scope
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
                                    </div>

                                    <div>
                                        <label className="text-sm font-medium text-slate-700">
                                            File
                                        </label>

                                        <input
                                            id="admin-document-file"
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
                            )}
                        </div>
                    </section>

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

                        <div>
                            <label className="text-sm font-medium text-slate-700">
                                Scope
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
                                            "admin.documents.index",
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
                                No administrative
                                documents found.
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
                                    <div className="flex flex-wrap justify-between gap-4">
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

                                            <p className="mt-1 break-all text-xs text-slate-400">
                                                {
                                                    documentRecord.original_name
                                                }
                                            </p>
                                        </div>

                                        <div className="text-right text-xs text-slate-500">
                                            <p>
                                                {formatBytes(
                                                    documentRecord.size_bytes,
                                                )}
                                            </p>

                                            <p className="mt-1">
                                                {formatLabel(
                                                    documentRecord.scan_status,
                                                )}
                                            </p>
                                        </div>
                                    </div>

                                    <div className="mt-4 flex flex-wrap gap-2 text-xs">
                                        <span className="rounded-full bg-indigo-50 px-3 py-1 text-indigo-700">
                                            {formatLabel(
                                                documentRecord.category,
                                            )}
                                        </span>

                                        <span className="rounded-full bg-slate-100 px-3 py-1 text-slate-700">
                                            {formatLabel(
                                                documentRecord.access_scope,
                                            )}
                                        </span>
                                    </div>

                                    <div className="mt-5 flex flex-wrap gap-2">
                                        <a
                                            href={route(
                                                "admin.documents.download",
                                                documentRecord.uuid,
                                            )}
                                            className="inline-flex items-center gap-2 rounded-md bg-indigo-600 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white hover:bg-indigo-500"
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
                                                className="inline-flex items-center gap-2 rounded-md border border-red-200 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-red-600 hover:bg-red-50"
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
        </AdminLayout>
    );
}
