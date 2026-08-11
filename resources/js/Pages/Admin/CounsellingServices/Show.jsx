import AdminLayout from "@/Layouts/AdminLayout";
import { Head, Link, router } from "@inertiajs/react";
import { Archive, ArrowLeft, Pencil, RotateCcw } from "lucide-react";

function readable(value) {
    return value?.replaceAll("_", " ") ?? "—";
}

function statusClass(status) {
    if (status === "active") {
        return "bg-emerald-50 text-emerald-700";
    }

    if (status === "archived") {
        return "bg-slate-100 text-slate-600";
    }

    return "bg-amber-50 text-amber-700";
}

export default function Show({ service, permissions }) {
    const archiveService = () => {
        if (confirm(`Archive "${service.name}"?`)) {
            router.delete(
                route("admin.counselling-services.destroy", service.id),
            );
        }
    };

    const restoreService = () => {
        if (confirm(`Restore "${service.name}"?`)) {
            router.patch(
                route("admin.counselling-services.restore", service.id),
            );
        }
    };

    const customAgeRange =
        service.target_age_group === "custom"
            ? `${service.minimum_age}–${service.maximum_age} years`
            : readable(service.target_age_group);

    return (
        <AdminLayout title={service.name}>
            <Head title={service.name} />

            <div className="mb-6 flex flex-col justify-between gap-4 lg:flex-row lg:items-start">
                <div>
                    <Link
                        href={route("admin.counselling-services.index")}
                        className="mb-3 inline-flex items-center gap-2 text-sm font-medium text-slate-500 hover:text-teal-700"
                    >
                        <ArrowLeft className="h-4 w-4" />
                        Counselling services
                    </Link>

                    <div className="flex flex-wrap items-center gap-3">
                        <h2 className="text-2xl font-bold text-slate-900">
                            {service.name}
                        </h2>

                        <span
                            className={`rounded-full px-2.5 py-1 text-xs font-semibold capitalize ${statusClass(
                                service.status,
                            )}`}
                        >
                            {service.status}
                        </span>
                    </div>

                    <p className="mt-1 text-sm text-slate-500">
                        {service.slug}
                    </p>
                </div>

                <div className="flex flex-wrap gap-2">
                    {permissions.update && (
                        <Link
                            href={route(
                                "admin.counselling-services.edit",
                                service.id,
                            )}
                            className="inline-flex items-center gap-2 rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50"
                        >
                            <Pencil className="h-4 w-4" />
                            Edit
                        </Link>
                    )}

                    {permissions.archive && (
                        <button
                            type="button"
                            onClick={archiveService}
                            className="inline-flex items-center gap-2 rounded-xl border border-rose-200 bg-white px-4 py-2 text-sm font-semibold text-rose-700 hover:bg-rose-50"
                        >
                            <Archive className="h-4 w-4" />
                            Archive
                        </button>
                    )}

                    {permissions.restore && (
                        <button
                            type="button"
                            onClick={restoreService}
                            className="inline-flex items-center gap-2 rounded-xl border border-emerald-200 bg-white px-4 py-2 text-sm font-semibold text-emerald-700 hover:bg-emerald-50"
                        >
                            <RotateCcw className="h-4 w-4" />
                            Restore
                        </button>
                    )}
                </div>
            </div>

            <div className="mb-6 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                <Info title="Category" value={service.category?.name ?? "—"} />
                <Info
                    title="Duration"
                    value={`${service.duration_minutes} minutes`}
                />
                <Info
                    title="Service mode"
                    value={readable(service.service_mode)}
                />
                <Info title="Target age" value={customAgeRange} />
            </div>

            <div className="grid gap-6 lg:grid-cols-[2fr_1fr]">
                <div className="space-y-6">
                    <section className="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <h3 className="text-lg font-semibold text-slate-900">
                            Short description
                        </h3>

                        <p className="mt-3 whitespace-pre-line text-sm leading-6 text-slate-600">
                            {service.short_description}
                        </p>
                    </section>

                    <section className="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <h3 className="text-lg font-semibold text-slate-900">
                            Full description
                        </h3>

                        <p className="mt-3 whitespace-pre-line text-sm leading-6 text-slate-600">
                            {service.description ||
                                "No full description has been provided."}
                        </p>
                    </section>
                </div>

                <aside className="space-y-6">
                    <section className="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <h3 className="text-lg font-semibold text-slate-900">
                            Pricing
                        </h3>

                        <p className="mt-3 text-3xl font-bold text-slate-900">
                            {service.currency}{" "}
                            {Number(service.price).toLocaleString(undefined, {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2,
                            })}
                        </p>

                        <p className="mt-1 text-sm text-slate-500">
                            Per {service.duration_minutes}-minute session
                        </p>
                    </section>

                    <section className="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <h3 className="text-lg font-semibold text-slate-900">
                            Record details
                        </h3>

                        <dl className="mt-4 space-y-4 text-sm">
                            <Detail
                                label="Display order"
                                value={service.display_order}
                            />
                            <Detail
                                label="Created"
                                value={new Date(
                                    service.created_at,
                                ).toLocaleString()}
                            />
                            <Detail
                                label="Last updated"
                                value={new Date(
                                    service.updated_at,
                                ).toLocaleString()}
                            />

                            {service.archived_at && (
                                <>
                                    <Detail
                                        label="Archived"
                                        value={new Date(
                                            service.archived_at,
                                        ).toLocaleString()}
                                    />
                                    <Detail
                                        label="Archived by"
                                        value={
                                            service.archived_by?.name ??
                                            "Unknown user"
                                        }
                                    />
                                </>
                            )}
                        </dl>
                    </section>
                </aside>
            </div>
        </AdminLayout>
    );
}

function Info({ title, value }) {
    return (
        <div className="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <p className="text-sm text-slate-500">{title}</p>
            <p className="mt-2 font-semibold capitalize text-slate-900">
                {value}
            </p>
        </div>
    );
}

function Detail({ label, value }) {
    return (
        <div className="flex items-start justify-between gap-4">
            <dt className="text-slate-500">{label}</dt>
            <dd className="text-right font-medium text-slate-800">{value}</dd>
        </div>
    );
}
