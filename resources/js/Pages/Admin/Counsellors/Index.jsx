import AdminLayout from "@/Layouts/AdminLayout";
import { Head, Link, router, usePage } from "@inertiajs/react";
import { Eye, Pencil, Plus, Search } from "lucide-react";
import { useState } from "react";

const statusClasses = {
    active: "bg-emerald-100 text-emerald-700",
    inactive: "bg-amber-100 text-amber-700",
    archived: "bg-slate-200 text-slate-700",
};

export default function Index({
    counsellors,
    filters,
    specializations,
    permissions,
}) {
    const { flash = {} } = usePage().props;

    const [search, setSearch] = useState(filters.search ?? "");
    const [status, setStatus] = useState(filters.status ?? "");
    const [specializationId, setSpecializationId] = useState(
        filters.specialization_id ?? "",
    );

    const applyFilters = (event) => {
        event.preventDefault();

        router.get(
            route("admin.counsellors.index"),
            {
                search,
                status,
                specialization_id: specializationId,
            },
            {
                preserveState: true,
                replace: true,
            },
        );
    };

    const clearFilters = () => {
        setSearch("");
        setStatus("");
        setSpecializationId("");

        router.get(route("admin.counsellors.index"));
    };

    return (
        <AdminLayout title="Counsellors">
            <Head title="Counsellors" />

            <div className="space-y-6">
                <div className="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
                    <div>
                        <h1 className="text-2xl font-bold text-slate-900">
                            Counsellors
                        </h1>

                        <p className="mt-1 text-sm text-slate-500">
                            Manage counsellor profiles, qualifications,
                            specializations and languages.
                        </p>
                    </div>

                    {permissions.create && (
                        <Link
                            href={route("admin.counsellors.create")}
                            className="inline-flex items-center justify-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-indigo-700"
                        >
                            <Plus className="h-4 w-4" />
                            Add counsellor
                        </Link>
                    )}
                </div>

                {flash.success && (
                    <div className="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                        {flash.success}
                    </div>
                )}

                <form
                    onSubmit={applyFilters}
                    className="grid gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm md:grid-cols-4"
                >
                    <div className="relative md:col-span-2">
                        <Search className="absolute left-3 top-3 h-5 w-5 text-slate-400" />

                        <input
                            type="search"
                            value={search}
                            onChange={(event) => setSearch(event.target.value)}
                            placeholder="Search name, email, NIC or registration number"
                            className="w-full rounded-xl border-slate-300 pl-10 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        />
                    </div>

                    <select
                        value={status}
                        onChange={(event) => setStatus(event.target.value)}
                        className="rounded-xl border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >
                        <option value="">All statuses</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                        <option value="archived">Archived</option>
                    </select>

                    <select
                        value={specializationId}
                        onChange={(event) =>
                            setSpecializationId(event.target.value)
                        }
                        className="rounded-xl border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >
                        <option value="">All specializations</option>

                        {specializations.map((specialization) => (
                            <option
                                key={specialization.id}
                                value={specialization.id}
                            >
                                {specialization.name}
                            </option>
                        ))}
                    </select>

                    <div className="flex gap-3 md:col-span-4 md:justify-end">
                        <button
                            type="button"
                            onClick={clearFilters}
                            className="rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
                        >
                            Clear
                        </button>

                        <button
                            type="submit"
                            className="rounded-xl bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800"
                        >
                            Apply filters
                        </button>
                    </div>
                </form>

                <div className="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div className="overflow-x-auto">
                        <table className="min-w-full divide-y divide-slate-200">
                            <thead className="bg-slate-50">
                                <tr>
                                    <th className="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                        Counsellor
                                    </th>
                                    <th className="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                        Registration
                                    </th>
                                    <th className="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                        Specializations
                                    </th>
                                    <th className="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                        Experience
                                    </th>
                                    <th className="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                        Status
                                    </th>
                                    <th className="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">
                                        Actions
                                    </th>
                                </tr>
                            </thead>

                            <tbody className="divide-y divide-slate-100">
                                {counsellors.data.map((counsellor) => (
                                    <tr key={counsellor.id}>
                                        <td className="px-5 py-4">
                                            <p className="font-medium text-slate-900">
                                                {counsellor.user?.name}
                                            </p>

                                            <p className="text-sm text-slate-500">
                                                {counsellor.user?.email}
                                            </p>
                                        </td>

                                        <td className="px-5 py-4 text-sm text-slate-700">
                                            {counsellor.registration_number}
                                        </td>

                                        <td className="px-5 py-4">
                                            <div className="flex max-w-md flex-wrap gap-1">
                                                {counsellor.specializations.map(
                                                    (specialization) => (
                                                        <span
                                                            key={
                                                                specialization.id
                                                            }
                                                            className="rounded-full bg-indigo-50 px-2 py-1 text-xs text-indigo-700"
                                                        >
                                                            {
                                                                specialization.name
                                                            }
                                                        </span>
                                                    ),
                                                )}

                                                {counsellor.specializations
                                                    .length === 0 && (
                                                    <span className="text-sm text-slate-400">
                                                        None
                                                    </span>
                                                )}
                                            </div>
                                        </td>

                                        <td className="px-5 py-4 text-sm text-slate-700">
                                            {counsellor.years_of_experience}{" "}
                                            years
                                        </td>

                                        <td className="px-5 py-4">
                                            <span
                                                className={`rounded-full px-2.5 py-1 text-xs font-medium capitalize ${
                                                    statusClasses[
                                                        counsellor.status
                                                    ]
                                                }`}
                                            >
                                                {counsellor.status}
                                            </span>
                                        </td>

                                        <td className="px-5 py-4">
                                            <div className="flex justify-end gap-2">
                                                <Link
                                                    href={route(
                                                        "admin.counsellors.show",
                                                        counsellor.id,
                                                    )}
                                                    className="rounded-lg p-2 text-slate-600 hover:bg-slate-100"
                                                    title="View"
                                                >
                                                    <Eye className="h-4 w-4" />
                                                </Link>

                                                {permissions.update &&
                                                    counsellor.status !==
                                                        "archived" && (
                                                        <Link
                                                            href={route(
                                                                "admin.counsellors.edit",
                                                                counsellor.id,
                                                            )}
                                                            className="rounded-lg p-2 text-indigo-600 hover:bg-indigo-50"
                                                            title="Edit"
                                                        >
                                                            <Pencil className="h-4 w-4" />
                                                        </Link>
                                                    )}
                                            </div>
                                        </td>
                                    </tr>
                                ))}

                                {counsellors.data.length === 0 && (
                                    <tr>
                                        <td
                                            colSpan="6"
                                            className="px-5 py-12 text-center text-sm text-slate-500"
                                        >
                                            No counsellors found.
                                        </td>
                                    </tr>
                                )}
                            </tbody>
                        </table>
                    </div>

                    {counsellors.links.length > 3 && (
                        <div className="flex flex-wrap gap-2 border-t border-slate-200 px-5 py-4">
                            {counsellors.links.map((link, index) =>
                                link.url ? (
                                    <Link
                                        key={index}
                                        href={link.url}
                                        preserveScroll
                                        className={`rounded-lg px-3 py-2 text-sm ${
                                            link.active
                                                ? "bg-indigo-600 text-white"
                                                : "border border-slate-300 text-slate-700 hover:bg-slate-50"
                                        }`}
                                        dangerouslySetInnerHTML={{
                                            __html: link.label,
                                        }}
                                    />
                                ) : (
                                    <span
                                        key={index}
                                        className="cursor-not-allowed rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-400"
                                        dangerouslySetInnerHTML={{
                                            __html: link.label,
                                        }}
                                    />
                                ),
                            )}
                        </div>
                    )}
                </div>
            </div>
        </AdminLayout>
    );
}
