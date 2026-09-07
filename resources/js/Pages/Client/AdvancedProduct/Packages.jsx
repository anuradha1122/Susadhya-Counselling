import ClientLayout from '@/Layouts/ClientLayout';
import { Head, router } from '@inertiajs/react';

export default function Packages({ packages = [], subscriptions = [] }) {
    return (
        <ClientLayout>
            <Head title="Packages" />

            <div className="mx-auto max-w-7xl space-y-6">
                <div>
                    <h1 className="text-2xl font-semibold text-slate-900">Packages</h1>
                    <p className="mt-1 text-sm text-slate-500">
                        Request package enrollment. Payment confirmation remains an admin process.
                    </p>
                </div>

                <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                    {packages.map((item) => (
                        <div key={item.uuid} className="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                            <div className="text-lg font-semibold text-slate-900">{item.name}</div>

                            <p className="mt-2 line-clamp-3 text-sm text-slate-600">{item.description}</p>

                            <div className="mt-4 text-sm text-slate-500">
                                {item.sessions_count} sessions | {item.validity_days} days
                            </div>

                            <div className="mt-2 text-lg font-semibold text-slate-900">
                                Rs. {Number(item.price).toLocaleString()}
                            </div>

                            <button
                                onClick={() => router.post(route('client.advanced-products.packages.subscribe', item.uuid))}
                                className="mt-4 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white"
                            >
                                Request package
                            </button>
                        </div>
                    ))}

                    {packages.length === 0 && (
                        <div className="rounded-xl border border-slate-200 bg-white p-6 text-sm text-slate-500 shadow-sm">
                            No active packages are available.
                        </div>
                    )}
                </div>

                <div className="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h2 className="text-lg font-semibold text-slate-900">My package requests</h2>

                    <div className="mt-3 divide-y divide-slate-100">
                        {subscriptions.map((item) => (
                            <div key={item.uuid} className="flex items-center justify-between py-3 text-sm">
                                <span>{item.package?.name}</span>
                                <span className="rounded-full bg-slate-100 px-2 py-1 text-xs text-slate-600">{item.status}</span>
                            </div>
                        ))}

                        {subscriptions.length === 0 && (
                            <div className="py-6 text-sm text-slate-500">No package requests yet.</div>
                        )}
                    </div>
                </div>
            </div>
        </ClientLayout>
    );
}
