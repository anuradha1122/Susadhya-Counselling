import AdminLayout from "@/Layouts/AdminLayout";
import { Head, Link, router } from "@inertiajs/react";
import { Archive, ArrowLeft, Pencil, Plus, RotateCcw } from "lucide-react";

function label(value) {
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

export default function Show({ category, services, permissions }) {
    const archiveCategory = () => {
        if (confirm(`Archive "${category.name}"?`)) {
            router.delete(
                route("admin.service-categories.destroy", category.id),
            );
        }
    };

    const restoreCategory = () => {
        if (confirm(`Restore "${category.name}"?`)) {
            router.patch(
                route("admin.service-categories.restore", category.id),
            );
        }
    };

    return (
        <AdminLayout title={category.name}>
            <Head title={category.name} />

            <div className="mb-6 flex flex-col justify-between gap-4 lg:flex-row lg:items-start">
                <div>
                    <Link
                        href={route("admin.service-categories.index")}
                        className="mb-3 inline-flex items-center gap-2 text-sm font-medium text-slate-500 hover:text-teal-700"
                    >
                        <ArrowLeft className="h-4 w-4" />
                        Service categories
                    </Link>

                    <div className="flex flex-wrap items-center gap-3">
                        <h2 className="text-2xl font-bold text-slate-900">
                            {category.name}
                        </h2>

                        <span
                            className={`rounded-full px-2.5 py-1 text-xs font-semibold capitalize ${statusClass(
                                category.status,
                            )}`}
                        >
                            {category.status}
                        </span>
                    </div>

                    <p className="mt-1 text-sm text-slate-500">
                        {category.slug}
                    </p>
                </div>

                <div className="flex flex-wrap gap-2">
                    {permissions.createService &&
                        category.status === "active" && (
                            <Link
                                href={route(
                                    "admin.counselling-services.create",
                                    {
                                        service_category_id: category.id,
                                    },
                                )}
                                className="inline-flex items-center gap-2 rounded-xl bg-teal-600 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-700"
                            >
                                <Plus className="h-4 w-4" />
                                Add service
                            </Link>
                        )}

                    {permissions.update && (
                        <Link
                            href={route(
                                "admin.service-categories.edit",
                                category.id,
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
                            onClick={archiveCategory}
                            className="inline-flex items-center gap-2 rounded-xl border border-rose-200 bg-white px-4 py-2 text-sm font-semibold text-rose-700 hover:bg-rose-50"
                        >
                            <Archive className="h-4 w-4" />
                            Archive
                        </button>
                    )}

                    {permissions.restore && (
                        <button
                            type="button"
                            onClick={restoreCategory}
                            className="inline-flex items-center gap-2 rounded-xl border border-emerald-200 bg-white px-4 py-2 text-sm font-semibold text-emerald-700 hover:bg-emerald-50"
                        >
                            <RotateCcw className="h-4 w-4" />
                            Restore
                        </button>
                    )}
                </div>
            </div>

            <div className="mb-6 grid gap-4 md:grid-cols-3">
                <Info title="Total services" value={category.services_count} />
                <Info
                    title="Active services"
                    value={category.active_services_count}
                />
                <Info title="Display order" value={category.display_order} />
            </div>

            <section className="mb-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h3 className="text-lg font-semibold text-slate-900">
                    Description
                </h3>

                <p className="mt-3 whitespace-pre-line text-sm leading-6 text-slate-600">
                    {category.description ||
                        "No description has been provided."}
                </p>

                {category.archived_at && (
                    <p className="mt-4 text-xs text-slate-500">
                        Archived by{" "}
                        {category.archived_by?.name ?? "Unknown user"}
                        {" on "}
                        {new Date(category.archived_at).toLocaleString()}
                    </p>
                )}
            </section>

            <section className="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div className="border-b border-slate-200 px-6 py-4">
                    <h3 className="text-lg font-semibold text-slate-900">
                        Counselling services
                    </h3>
                </div>

                <div className="overflow-x-auto">
                    <table className="min-w-full divide-y divide-slate-200 text-sm">
                        <thead className="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                            <tr>
                                <th className="px-5 py-3">Service</th>
                                <th className="px-5 py-3">Duration</th>
                                <th className="px-5 py-3">Mode</th>
                                <th className="px-5 py-3">Age group</th>
                                <th className="px-5 py-3">Price</th>
                                <th className="px-5 py-3">Status</th>
                            </tr>
                        </thead>

                        <tbody className="divide-y divide-slate-100">
                            {services.map((service) => (
                                <tr
                                    key={service.id}
                                    className="hover:bg-slate-50"
                                >
                                    <td className="px-5 py-4">
                                        <Link
                                            href={route(
                                                "admin.counselling-services.show",
                                                service.id,
                                            )}
                                            className="font-medium text-teal-700 hover:text-teal-900"
                                        >
                                            {service.name}
                                        </Link>

                                        <p className="text-xs text-slate-500">
                                            {service.slug}
                                        </p>
                                    </td>

                                    <td className="px-5 py-4 text-slate-600">
                                        {service.duration_minutes} minutes
                                    </td>

                                    <td className="px-5 py-4 capitalize text-slate-600">
                                        {label(service.service_mode)}
                                    </td>

                                    <td className="px-5 py-4 capitalize text-slate-600">
                                        {label(service.target_age_group)}
                                    </td>

                                    <td className="px-5 py-4 text-slate-600">
                                        {service.currency}{" "}
                                        {Number(service.price).toLocaleString(
                                            undefined,
                                            {
                                                minimumFractionDigits: 2,
                                            },
                                        )}
                                    </td>

                                    <td className="px-5 py-4">
                                        <span
                                            className={`rounded-full px-2.5 py-1 text-xs font-semibold capitalize ${statusClass(
                                                service.status,
                                            )}`}
                                        >
                                            {service.status}
                                        </span>
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>

                {services.length === 0 && (
                    <p className="p-10 text-center text-sm text-slate-500">
                        No counselling services belong to this category.
                    </p>
                )}
            </section>
        </AdminLayout>
    );
}

function Info({ title, value }) {
    return (
        <div className="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <p className="text-sm text-slate-500">{title}</p>
            <p className="mt-2 text-2xl font-bold text-slate-900">{value}</p>
        </div>
    );
}
