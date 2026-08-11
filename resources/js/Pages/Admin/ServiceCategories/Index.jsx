import Pagination from "@/Components/Pagination";
import AdminLayout from "@/Layouts/AdminLayout";
import { Head, Link, router } from "@inertiajs/react";
import { Archive, Eye, Pencil, Plus, RotateCcw, Search } from "lucide-react";
import { useState } from "react";

function statusClass(status) {
    if (status === "active") {
        return "bg-emerald-50 text-emerald-700";
    }

    if (status === "archived") {
        return "bg-slate-100 text-slate-600";
    }

    return "bg-amber-50 text-amber-700";
}

export default function Index({ categories, filters, permissions }) {
    const [values, setValues] = useState({
        search: filters.search ?? "",
        status: filters.status ?? "",
    });

    const applyFilters = (event) => {
        event.preventDefault();

        router.get(route("admin.service-categories.index"), values, {
            preserveState: true,
            replace: true,
        });
    };

    const archiveCategory = (category) => {
        if (confirm(`Archive "${category.name}"?`)) {
            router.delete(
                route("admin.service-categories.destroy", category.id),
            );
        }
    };

    const restoreCategory = (category) => {
        if (confirm(`Restore "${category.name}"?`)) {
            router.patch(
                route("admin.service-categories.restore", category.id),
            );
        }
    };

    return (
        <AdminLayout title="Service Categories">
            <Head title="Service Categories" />

            <div className="mb-6 flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
                <div>
                    <h2 className="text-2xl font-bold text-slate-900">
                        Service categories
                    </h2>

                    <p className="mt-1 text-sm text-slate-500">
                        Organize counselling services into searchable
                        categories.
                    </p>
                </div>

                {permissions.create && (
                    <Link
                        href={route("admin.service-categories.create")}
                        className="inline-flex items-center justify-center gap-2 rounded-xl bg-teal-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-teal-700"
                    >
                        <Plus className="h-4 w-4" />
                        New category
                    </Link>
                )}
            </div>

            <form
                onSubmit={applyFilters}
                className="mb-5 grid gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm md:grid-cols-[1fr_180px_auto]"
            >
                <input
                    value={values.search}
                    onChange={(event) =>
                        setValues({
                            ...values,
                            search: event.target.value,
                        })
                    }
                    placeholder="Search name, slug or description"
                    className="rounded-xl border-slate-300 text-sm focus:border-teal-500 focus:ring-teal-500"
                />

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

                <button className="inline-flex items-center justify-center gap-2 rounded-xl bg-slate-800 px-4 py-2 text-sm font-medium text-white hover:bg-slate-900">
                    <Search className="h-4 w-4" />
                    Filter
                </button>
            </form>

            <div className="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div className="overflow-x-auto">
                    <table className="min-w-full divide-y divide-slate-200 text-sm">
                        <thead className="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                            <tr>
                                <th className="px-5 py-3">Category</th>
                                <th className="px-5 py-3">Services</th>
                                <th className="px-5 py-3">Order</th>
                                <th className="px-5 py-3">Status</th>
                                <th className="px-5 py-3 text-right">
                                    Actions
                                </th>
                            </tr>
                        </thead>

                        <tbody className="divide-y divide-slate-100">
                            {categories.data.map((category) => (
                                <tr
                                    key={category.id}
                                    className="hover:bg-slate-50"
                                >
                                    <td className="px-5 py-4">
                                        <p className="font-medium text-slate-900">
                                            {category.name}
                                        </p>
                                        <p className="text-xs text-slate-500">
                                            {category.slug}
                                        </p>
                                    </td>

                                    <td className="px-5 py-4 text-slate-600">
                                        {category.active_services_count}
                                        {" active / "}
                                        {category.services_count}
                                        {" total"}
                                    </td>

                                    <td className="px-5 py-4 text-slate-600">
                                        {category.display_order}
                                    </td>

                                    <td className="px-5 py-4">
                                        <span
                                            className={`rounded-full px-2.5 py-1 text-xs font-semibold capitalize ${statusClass(
                                                category.status,
                                            )}`}
                                        >
                                            {category.status}
                                        </span>
                                    </td>

                                    <td className="px-5 py-4">
                                        <div className="flex justify-end gap-2">
                                            <Link
                                                href={route(
                                                    "admin.service-categories.show",
                                                    category.id,
                                                )}
                                                className="rounded-lg p-2 text-slate-500 hover:bg-teal-50 hover:text-teal-700"
                                                aria-label="View"
                                            >
                                                <Eye className="h-4 w-4" />
                                            </Link>

                                            {permissions.update &&
                                                category.status !==
                                                    "archived" && (
                                                    <Link
                                                        href={route(
                                                            "admin.service-categories.edit",
                                                            category.id,
                                                        )}
                                                        className="rounded-lg p-2 text-slate-500 hover:bg-teal-50 hover:text-teal-700"
                                                        aria-label="Edit"
                                                    >
                                                        <Pencil className="h-4 w-4" />
                                                    </Link>
                                                )}

                                            {permissions.archive &&
                                                category.status !==
                                                    "archived" &&
                                                Number(
                                                    category.active_services_count,
                                                ) === 0 && (
                                                    <button
                                                        type="button"
                                                        onClick={() =>
                                                            archiveCategory(
                                                                category,
                                                            )
                                                        }
                                                        className="rounded-lg p-2 text-slate-500 hover:bg-rose-50 hover:text-rose-700"
                                                        aria-label="Archive"
                                                    >
                                                        <Archive className="h-4 w-4" />
                                                    </button>
                                                )}

                                            {permissions.update &&
                                                category.status ===
                                                    "archived" && (
                                                    <button
                                                        type="button"
                                                        onClick={() =>
                                                            restoreCategory(
                                                                category,
                                                            )
                                                        }
                                                        className="rounded-lg p-2 text-slate-500 hover:bg-emerald-50 hover:text-emerald-700"
                                                        aria-label="Restore"
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

                {categories.data.length === 0 && (
                    <p className="p-10 text-center text-sm text-slate-500">
                        No service categories match the selected filters.
                    </p>
                )}
            </div>

            <Pagination links={categories.links} />
        </AdminLayout>
    );
}
