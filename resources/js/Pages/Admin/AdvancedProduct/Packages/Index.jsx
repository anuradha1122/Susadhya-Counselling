import AdminLayout from '@/Layouts/AdminLayout';
import { Head, Link } from '@inertiajs/react';
import { Plus } from 'lucide-react';

export default function Index({ packages }) {
    const rows = packages?.data ?? [];

    return (
        <AdminLayout>
            <Head title="Packages" />

            <div className="mx-auto max-w-7xl space-y-6">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-semibold text-slate-900">Packages</h1>
                        <p className="mt-1 text-sm text-slate-500">Manage service packages and subscription-ready offers.</p>
                    </div>

                    <Link href={route('admin.advanced-products.packages.create')} className="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white">
                        <Plus className="h-4 w-4" />
                        New Package
                    </Link>
                </div>

                <div className="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                    <table className="min-w-full divide-y divide-slate-200 text-sm">
                        <thead className="bg-slate-50 text-left text-xs font-semibold uppercase text-slate-500">
                            <tr>
                                <th className="px-4 py-3">Package</th>
                                <th className="px-4 py-3">Sessions</th>
                                <th className="px-4 py-3">Validity</th>
                                <th className="px-4 py-3">Price</th>
                                <th className="px-4 py-3">Status</th>
                                <th className="px-4 py-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-slate-100">
                            {rows.map((item) => (
                                <tr key={item.uuid}>
                                    <td className="px-4 py-3 font-medium text-slate-900">{item.name}</td>
                                    <td className="px-4 py-3">{item.sessions_count}</td>
                                    <td className="px-4 py-3">{item.validity_days} days</td>
                                    <td className="px-4 py-3">Rs. {Number(item.price).toLocaleString()}</td>
                                    <td className="px-4 py-3">{item.is_active ? 'Active' : 'Draft'}</td>
                                    <td className="px-4 py-3 text-right">
                                        <Link className="font-medium text-indigo-600" href={route('admin.advanced-products.packages.edit', item.uuid)}>
                                            Edit
                                        </Link>
                                    </td>
                                </tr>
                            ))}

                            {rows.length === 0 && (
                                <tr>
                                    <td colSpan="6" className="px-4 py-8 text-center text-slate-500">No packages found.</td>
                                </tr>
                            )}
                        </tbody>
                    </table>
                </div>
            </div>
        </AdminLayout>
    );
}
