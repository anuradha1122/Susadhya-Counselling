import AdminLayout from '@/Layouts/AdminLayout';
import { Head, router } from '@inertiajs/react';

export default function Index({ subscriptions, statuses = [] }) {
    const rows = subscriptions?.data ?? [];

    const updateStatus = (uuid, status) => {
        router.patch(
            route('admin.advanced-products.subscriptions.update', uuid),
            { status },
            { preserveScroll: true },
        );
    };

    return (
        <AdminLayout>
            <Head title="Client Subscriptions" />

            <div className="mx-auto max-w-7xl space-y-6">
                <div>
                    <h1 className="text-2xl font-semibold text-slate-900">Client Subscriptions</h1>
                    <p className="mt-1 text-sm text-slate-500">Operational overview of package usage and status.</p>
                </div>

                <div className="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                    <table className="min-w-full divide-y divide-slate-200 text-sm">
                        <thead className="bg-slate-50 text-left text-xs font-semibold uppercase text-slate-500">
                            <tr>
                                <th className="px-4 py-3">Client</th>
                                <th className="px-4 py-3">Package</th>
                                <th className="px-4 py-3">Usage</th>
                                <th className="px-4 py-3">Period</th>
                                <th className="px-4 py-3">Status</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-slate-100">
                            {rows.map((item) => (
                                <tr key={item.uuid}>
                                    <td className="px-4 py-3">{item.client_profile?.user?.name ?? 'Client'}</td>
                                    <td className="px-4 py-3">{item.package?.name}</td>
                                    <td className="px-4 py-3">{item.used_sessions} / {item.total_sessions}</td>
                                    <td className="px-4 py-3">{item.starts_on} - {item.expires_on ?? 'Open'}</td>
                                    <td className="px-4 py-3">
                                        <select className="rounded-lg border-slate-300 text-sm" value={item.status} onChange={(e) => updateStatus(item.uuid, e.target.value)}>
                                            {statuses.map((status) => (
                                                <option key={status} value={status}>{status}</option>
                                            ))}
                                        </select>
                                    </td>
                                </tr>
                            ))}

                            {rows.length === 0 && (
                                <tr>
                                    <td colSpan="5" className="px-4 py-8 text-center text-slate-500">No subscriptions found.</td>
                                </tr>
                            )}
                        </tbody>
                    </table>
                </div>
            </div>
        </AdminLayout>
    );
}
