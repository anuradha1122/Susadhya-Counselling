import ClientLayout from '@/Layouts/ClientLayout';
import { Head, router } from '@inertiajs/react';

export default function Groups({ programs, enrolledProgramUuids = [] }) {
    const rows = programs?.data ?? [];

    return (
        <ClientLayout>
            <Head title="Group Counselling" />

            <div className="mx-auto max-w-7xl space-y-6">
                <div>
                    <h1 className="text-2xl font-semibold text-slate-900">Group Counselling</h1>
                    <p className="mt-1 text-sm text-slate-500">Available group counselling programs.</p>
                </div>

                <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                    {rows.map((program) => {
                        const enrolled = enrolledProgramUuids.includes(program.uuid);

                        return (
                            <div key={program.uuid} className="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                                <div className="text-lg font-semibold text-slate-900">{program.title}</div>

                                <p className="mt-2 line-clamp-3 text-sm text-slate-600">{program.description}</p>

                                <div className="mt-4 text-sm text-slate-500">
                                    {program.mode?.replace('_', ' ')} | Capacity {program.capacity}
                                </div>

                                <button
                                    disabled={enrolled}
                                    onClick={() => router.post(route('client.advanced-products.groups.enroll', program.uuid))}
                                    className="mt-4 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white disabled:bg-slate-300"
                                >
                                    {enrolled ? 'Already requested' : 'Request enrollment'}
                                </button>
                            </div>
                        );
                    })}

                    {rows.length === 0 && (
                        <div className="rounded-xl border border-slate-200 bg-white p-6 text-sm text-slate-500 shadow-sm">
                            No active group counselling programs are available.
                        </div>
                    )}
                </div>
            </div>
        </ClientLayout>
    );
}
