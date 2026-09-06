import InputError from "@/Components/InputError";
import Pagination from "@/Components/Pagination";
import AdminLayout from "@/Layouts/AdminLayout";
import CmsTabs from "@/Pages/Admin/Cms/Components/CmsTabs";
import { Head, router, useForm } from "@inertiajs/react";
import { Copy, Upload } from "lucide-react";

function formatBytes(bytes) {
    if (!bytes) return "0 B";

    const units = ["B", "KB", "MB", "GB"];
    const index = Math.min(
        Math.floor(Math.log(bytes) / Math.log(1024)),
        units.length - 1,
    );

    return `${(bytes / 1024 ** index).toFixed(1)} ${units[index]}`;
}

export default function Index({
    media,
    filters,
}) {
    const form = useForm({
        file: null,
        alt_text: "",
    });

    const submit = (event) => {
        event.preventDefault();

        form.post(
            route("admin.cms.media.store"),
            {
                forceFormData: true,
                preserveScroll: true,
                onSuccess: () => form.reset(),
            },
        );
    };

    return (
        <AdminLayout title="CMS Media">
            <Head title="CMS Media" />

            <div className="mx-auto max-w-7xl space-y-6">
                <CmsTabs />

                <form
                    onSubmit={submit}
                    className="rounded-xl border border-indigo-200 bg-indigo-50 p-5"
                >
                    <h3 className="flex items-center gap-2 font-semibold text-slate-900">
                        <Upload className="h-4 w-4" />
                        Upload media
                    </h3>

                    <div className="mt-4 grid gap-4 md:grid-cols-2">
                        <input
                            type="file"
                            accept="image/jpeg,image/png,image/webp"
                            onChange={(event) =>
                                form.setData(
                                    "file",
                                    event.target.files?.[0] ?? null,
                                )
                            }
                            className="rounded-md border border-slate-300 bg-white p-2"
                        />

                        <input
                            value={form.data.alt_text}
                            onChange={(event) =>
                                form.setData(
                                    "alt_text",
                                    event.target.value,
                                )
                            }
                            placeholder="Alternative text"
                            className="rounded-md border-slate-300"
                        />
                    </div>

                    <InputError
                        message={form.errors.file}
                        className="mt-2"
                    />

                    <InputError
                        message={form.errors.alt_text}
                        className="mt-2"
                    />

                    <button
                        disabled={form.processing}
                        className="mt-4 rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white"
                    >
                        Upload image
                    </button>
                </form>

                <div className="grid gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                    {media.data.map((item) => (
                        <div
                            key={item.uuid}
                            className="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm"
                        >
                            <div className="aspect-[4/3] bg-slate-100">
                                <img
                                    src={item.url}
                                    alt={item.alt_text}
                                    className="h-full w-full object-cover"
                                />
                            </div>

                            <div className="p-4">
                                <p className="truncate font-medium text-slate-900">
                                    {item.original_name}
                                </p>

                                <p className="mt-1 text-xs text-slate-500">
                                    {item.width} × {item.height} ·{" "}
                                    {formatBytes(item.size_bytes)}
                                </p>

                                <p className="mt-2 line-clamp-2 text-sm text-slate-600">
                                    {item.alt_text}
                                </p>

                                <div className="mt-4 flex flex-wrap gap-2">
                                    <button
                                        type="button"
                                        onClick={() =>
                                            navigator.clipboard.writeText(
                                                item.url,
                                            )
                                        }
                                        className="inline-flex items-center gap-1 rounded-md border border-slate-300 px-3 py-2 text-xs"
                                    >
                                        <Copy className="h-3.5 w-3.5" />
                                        Copy URL
                                    </button>

                                    {item.is_active ? (
                                        <button
                                            type="button"
                                            onClick={() =>
                                                router.post(
                                                    route(
                                                        "admin.cms.media.archive",
                                                        item.uuid,
                                                    ),
                                                )
                                            }
                                            className="rounded-md border border-rose-300 px-3 py-2 text-xs text-rose-700"
                                        >
                                            Archive
                                        </button>
                                    ) : (
                                        <button
                                            type="button"
                                            onClick={() =>
                                                router.post(
                                                    route(
                                                        "admin.cms.media.restore",
                                                        item.uuid,
                                                    ),
                                                )
                                            }
                                            className="rounded-md border border-emerald-300 px-3 py-2 text-xs text-emerald-700"
                                        >
                                            Restore
                                        </button>
                                    )}
                                </div>
                            </div>
                        </div>
                    ))}
                </div>

                <Pagination links={media.links} />
            </div>
        </AdminLayout>
    );
}
