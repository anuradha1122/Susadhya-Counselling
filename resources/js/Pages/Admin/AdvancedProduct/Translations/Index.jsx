import AdminLayout from '@/Layouts/AdminLayout';
import { Head, Link } from '@inertiajs/react';
import { Plus } from 'lucide-react';

export default function Index({ translations }) {
    const rows = translations?.data ?? [];

    return (
        <AdminLayout>
            <Head title="Translations" />

            <div className="mx-auto max-w-7xl space-y-6">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-semibold text-slate-900">Multilingual Content</h1>
                        <p className="mt-1 text-sm text-slate-500">Reviewed Sinhala and Tamil content overrides.</p>
                    </div>

                    <Link href={route('admin.advanced-products.translations.create')} className="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white">
                        <Plus className="h-4 w-4" />
                        New Translation
                    </Link>
                </div>

                <div className="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                    <table className="min-w-full divide-y divide-slate-200 text-sm">
                        <thead className="bg-slate-50 text-left text-xs font-semibold uppercase text-slate-500">
                            <tr>
                                <th className="px-4 py-3">Locale</th>
                                <th className="px-4 py-3">Type</th>
                                <th className="px-4 py-3">Field</th>
                                <th className="px-4 py-3">Value</th>
                                <th className="px-4 py-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-slate-100">
                            {rows.map((item) => (
                                <tr key={item.uuid}>
                                    <td className="px-4 py-3 font-medium">{item.locale}</td>
                                    <td className="px-4 py-3">{item.translatable_type} #{item.translatable_id}</td>
                                    <td className="px-4 py-3">{item.field}</td>
                                    <td className="max-w-md truncate px-4 py-3">{item.value}</td>
                                    <td className="px-4 py-3 text-right">
                                        <Link className="font-medium text-indigo-600" href={route('admin.advanced-products.translations.edit', item.uuid)}>
                                            Edit
                                        </Link>
                                    </td>
                                </tr>
                            ))}

                            {rows.length === 0 && (
                                <tr>
                                    <td colSpan="5" className="px-4 py-8 text-center text-slate-500">No translations found.</td>
                                </tr>
                            )}
                        </tbody>
                    </table>
                </div>
            </div>
        </AdminLayout>
    );
}
