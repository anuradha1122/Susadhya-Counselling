import AdminLayout from '@/Layouts/AdminLayout';
import { Head, Link, router } from '@inertiajs/react';
import { Plus, Search } from 'lucide-react';

export default function Index({ programs, filters = {} }) {
    const rows = programs?.data ?? [];

    return (
        <AdminLayout>
            <Head title="Group Counselling" />

            <div className="mx-auto max-w-7xl space-y-6">
                <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h1 className="text-2xl font-semibold text-slate-900">Group Counselling</h1>
                        <p className="mt-1 text-sm text-slate-500">
                            Manage Post-MVP group counselling programs.
                        </p>
                    </div>

                    <Link
                        href={route('admin.advanced-products.group-counselling.create')}
                        className="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white"
                    >
                        <Plus className="h-4 w-4" />
                        New Program
                    </Link>
                </div>

                <div className="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                    <div className="mb-4 flex items-center gap-2">
                        <Search className="h-4 w-4 text-slate-400" />
                        <input
                            defaultValue={filters.search ?? ''}
                            onChange={(event) =>
                                router.get(
                                    route('admin.advanced-products.group-counselling.index'),
                                    { search: event.target.value },
                                    { preserveState: true, replace: true },
                                )
                            }
                            className="w-full rounded-lg border-slate-300 text-sm"
                            placeholder="Search programs"
                        />
                    </div>

                    <div className="overflow-x-auto">
                        <table className="min-w-full divide-y divide-slate-200 text-sm">
                            <thead className="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                <tr>
                                    <th className="px-4 py-3">Program</th>
                                    <th className="px-4 py-3">Counsellor</th>
                                    <th className="px-4 py-3">Mode</th>
                                    <th className="px-4 py-3">Capacity</th>
                                    <th className="px-4 py-3">Enrollments</th>
                                    <th className="px-4 py-3">Status</th>
                                    <th className="px-4 py-3 text-right">Actions</th>
                                </tr>
                            </thead>

                            <tbody className="divide-y divide-slate-100">
                                {rows.map((program) => (
                                    <tr key={program.uuid}>
                                        <td className="px-4 py-3">
                                            <div className="font-medium text-slate-900">{program.title}</div>
                                            <div className="text-xs text-slate-500">
                                                {program.service?.name ?? program.service?.title ?? 'No service mapped'}
                                            </div>
                                        </td>
                                        <td className="px-4 py-3">
                                            {program.lead_counsellor_profile?.user?.name ?? 'Not assigned'}
                                        </td>
                                        <td className="px-4 py-3 capitalize">{program.mode?.replace('_', ' ')}</td>
                                        <td className="px-4 py-3">{program.capacity}</td>
                                        <td className="px-4 py-3">{program.enrollments_count}</td>
                                        <td className="px-4 py-3">{program.is_active ? 'Active' : 'Draft'}</td>
                                        <td className="px-4 py-3 text-right">
                                            <Link
                                                className="text-sm font-medium text-indigo-600"
                                                href={route('admin.advanced-products.group-counselling.edit', program.uuid)}
                                            >
                                                Edit
                                            </Link>
                                        </td>
                                    </tr>
                                ))}

                                {rows.length === 0 && (
                                    <tr>
                                        <td className="px-4 py-8 text-center text-slate-500" colSpan="7">
                                            No group counselling programs found.
                                        </td>
                                    </tr>
                                )}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </AdminLayout>
    );
}
