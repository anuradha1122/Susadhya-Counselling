import Pagination from "@/Components/Pagination";
import AdminLayout from "@/Layouts/AdminLayout";
import { Head, Link, router } from "@inertiajs/react";
import { Archive, Eye, Pencil, Plus, RotateCcw, Search } from "lucide-react";
import { useState } from "react";

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

export default function Index({ services, filters, categories, permissions }) {
    const [values, setValues] = useState({
        search: filters.search ?? "",
        status: filters.status ?? "",
        service_category_id: filters.service_category_id ?? "",
        service_mode: filters.service_mode ?? "",
        target_age_group: filters.target_age_group ?? "",
    });

    const applyFilters = (event) => {
        event.preventDefault();

        router.get(route("admin.counselling-services.index"), values, {
            preserveState: true,
            replace: true,
        });
    };

    const clearFilters = () => {
        const emptyFilters = {
            search: "",
            status: "",
            service_category_id: "",
            service_mode: "",
            target_age_group: "",
        };

        setValues(emptyFilters);

        router.get(
            route("admin.counselling-services.index"),
            {},
            {
                preserveState: true,
                replace: true,
            },
        );
    };

    const archiveService = (service) => {
        if (confirm(`Archive "${service.name}"?`)) {
            router.delete(
                route("admin.counselling-services.destroy", service.id),
            );
        }
    };

    const restoreService = (service) => {
        if (confirm(`Restore "${service.name}"?`)) {
            router.patch(
                route("admin.counselling-services.restore", service.id),
            );
        }
    };

    return (
        <AdminLayout title="Counselling Services">
            <Head title="Counselling Services" />

            <div className="mb-6 flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
                <div>
                    <h2 className="text-2xl font-bold text-slate-900">
                        Counselling services
                    </h2>

                    <p className="mt-1 text-sm text-slate-500">
                        Manage the counselling sessions available to clients.
                    </p>
                </div>

                {permissions.create && (
                    <Link
                        href={route("admin.counselling-services.create")}
                        className="inline-flex items-center justify-center gap-2 rounded-xl bg-teal-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-teal-700"
                    >
                        <Plus className="h-4 w-4" />
                        New service
                    </Link>
                )}
            </div>

            <form
                onSubmit={applyFilters}
                className="mb-5 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm"
            >
                <div className="grid gap-3 md:grid-cols-2 xl:grid-cols-5">
                    <input
                        value={values.search}
                        onChange={(event) =>
                            setValues({
                                ...values,
                                search: event.target.value,
                            })
                        }
                        placeholder="Search services"
                        className="rounded-xl border-slate-300 text-sm focus:border-teal-500 focus:ring-teal-500"
                    />

                    <select
                        value={values.service_category_id}
                        onChange={(event) =>
                            setValues({
                                ...values,
                                service_category_id: event.target.value,
                            })
                        }
                        className="rounded-xl border-slate-300 text-sm focus:border-teal-500 focus:ring-teal-500"
                    >
                        <option value="">All categories</option>

                        {categories.map((category) => (
                            <option key={category.id} value={category.id}>
                                {category.name}
                            </option>
                        ))}
                    </select>

                    <select
                        value={values.status}
                        onChange={(event) =>
                            setValues({
                                ...values,
                                status: event.target.value,
                            })
                        }
                        className="rounded-xl border-slate-300 text-sm focus:border-teal-500 focus:ring-teal-500"
                    >
                        <option value="">All statuses</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                        <option value="archived">Archived</option>
                    </select>

                    <select
                        value={values.service_mode}
                        onChange={(event) =>
                            setValues({
                                ...values,
                                service_mode: event.target.value,
                            })
                        }
                        className="rounded-xl border-slate-300 text-sm focus:border-teal-500 focus:ring-teal-500"
                    >
                        <option value="">All modes</option>
                        <option value="online">Online</option>
                        <option value="in_person">In person</option>
                        <option value="both">Online and in person</option>
                    </select>

                    <select
                        value={values.target_age_group}
                        onChange={(event) =>
                            setValues({
                                ...values,
                                target_age_group: event.target.value,
                            })
                        }
                        className="rounded-xl border-slate-300 text-sm focus:border-teal-500 focus:ring-teal-500"
                    >
                        <option value="">All age groups</option>
                        <option value="children">Children</option>
                        <option value="adolescents">Adolescents</option>
                        <option value="adults">Adults</option>
                        <option value="seniors">Seniors</option>
                        <option value="all_ages">All ages</option>
                        <option value="custom">Custom range</option>
                    </select>
                </div>

                <div className="mt-3 flex justify-end gap-2">
                    <button
                        type="button"
                        onClick={clearFilters}
                        className="rounded-xl border border-slate-300 px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50"
                    >
                        Clear
                    </button>

                    <button className="inline-flex items-center justify-center gap-2 rounded-xl bg-slate-800 px-4 py-2 text-sm font-medium text-white hover:bg-slate-900">
                        <Search className="h-4 w-4" />
                        Filter
                    </button>
                </div>
            </form>

            <div className="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div className="overflow-x-auto">
                    <table className="min-w-full divide-y divide-slate-200 text-sm">
                        <thead className="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                            <tr>
                                <th className="px-5 py-3">Service</th>
                                <th className="px-5 py-3">Category</th>
                                <th className="px-5 py-3">Session</th>
                                <th className="px-5 py-3">Price</th>
                                <th className="px-5 py-3">Status</th>
                                <th className="px-5 py-3 text-right">
                                    Actions
                                </th>
                            </tr>
                        </thead>

                        <tbody className="divide-y divide-slate-100">
                            {services.data.map((service) => (
                                <tr
                                    key={service.id}
                                    className="hover:bg-slate-50"
                                >
                                    <td className="px-5 py-4">
                                        <p className="font-medium text-slate-900">
                                            {service.name}
                                        </p>
                                        <p className="text-xs text-slate-500">
                                            {service.slug}
                                        </p>
                                    </td>

                                    <td className="px-5 py-4">
                                        <p className="text-slate-700">
                                            {service.category?.name ?? "—"}
                                        </p>

                                        {service.category?.status !==
                                            "active" && (
                                            <p className="text-xs capitalize text-amber-600">
                                                Category{" "}
                                                {service.category?.status}
                                            </p>
                                        )}
                                    </td>

                                    <td className="px-5 py-4 text-slate-600">
                                        <p>
                                            {service.duration_minutes} minutes
                                        </p>
                                        <p className="text-xs capitalize text-slate-500">
                                            {readable(service.service_mode)}
                                            {" · "}
                                            {readable(service.target_age_group)}
                                        </p>
                                    </td>

                                    <td className="px-5 py-4 text-slate-600">
                                        {service.currency}{" "}
                                        {Number(service.price).toLocaleString(
                                            undefined,
                                            {
                                                minimumFractionDigits: 2,
                                                maximumFractionDigits: 2,
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

                                    <td className="px-5 py-4">
                                        <div className="flex justify-end gap-2">
                                            <Link
                                                href={route(
                                                    "admin.counselling-services.show",
                                                    service.id,
                                                )}
                                                className="rounded-lg p-2 text-slate-500 hover:bg-teal-50 hover:text-teal-700"
                                                aria-label={`View ${service.name}`}
                                                title="View"
                                            >
                                                <Eye className="h-4 w-4" />
                                            </Link>

                                            {permissions.update &&
                                                service.status !==
                                                    "archived" && (
                                                    <Link
                                                        href={route(
                                                            "admin.counselling-services.edit",
                                                            service.id,
                                                        )}
                                                        className="rounded-lg p-2 text-slate-500 hover:bg-teal-50 hover:text-teal-700"
                                                        aria-label={`Edit ${service.name}`}
                                                        title="Edit"
                                                    >
                                                        <Pencil className="h-4 w-4" />
                                                    </Link>
                                                )}

                                            {permissions.archive &&
                                                service.status !==
                                                    "archived" && (
                                                    <button
                                                        type="button"
                                                        onClick={() =>
                                                            archiveService(
                                                                service,
                                                            )
                                                        }
                                                        className="rounded-lg p-2 text-slate-500 hover:bg-rose-50 hover:text-rose-700"
                                                        aria-label={`Archive ${service.name}`}
                                                        title="Archive"
                                                    >
                                                        <Archive className="h-4 w-4" />
                                                    </button>
                                                )}

                                            {permissions.update &&
                                                service.status ===
                                                    "archived" && (
                                                    <button
                                                        type="button"
                                                        onClick={() =>
                                                            restoreService(
                                                                service,
                                                            )
                                                        }
                                                        className="rounded-lg p-2 text-slate-500 hover:bg-emerald-50 hover:text-emerald-700"
                                                        aria-label={`Restore ${service.name}`}
                                                        title="Restore"
                                                    >
                                                        <RotateCcw className="h-4 w-4" />
                                                    </button>
                                                )}
                                        </div>
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>

                {services.data.length === 0 && (
                    <p className="p-10 text-center text-sm text-slate-500">
                        No counselling services match the selected filters.
                    </p>
                )}
            </div>

            <Pagination links={services.links} />
        </AdminLayout>
    );
}
