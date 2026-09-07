import AdminLayout from '@/Layouts/AdminLayout';
import { Head, router, useForm } from '@inertiajs/react';

export default function Index({ summaries }) {
    const rows = summaries?.data ?? [];

    const { data, setData, post, processing, reset, errors } = useForm({
        source_type: 'admin_note',
        source_label: '',
        title: '',
        source_text: '',
        summary: '',
        risk_flags: [],
    });

    const submit = (event) => {
        event.preventDefault();

        post(route('admin.advanced-products.ai-summaries.store'), {
            onSuccess: () => reset(),
        });
    };

    const review = (uuid, status) => {
        router.patch(
            route('admin.advanced-products.ai-summaries.review', uuid),
            { status },
            { preserveScroll: true },
        );
    };

    return (
        <AdminLayout>
            <Head title="AI-assisted Admin Summaries" />

            <div className="mx-auto grid max-w-7xl gap-6 lg:grid-cols-[1fr_1.2fr]">
                <form onSubmit={submit} className="space-y-4 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div>
                        <h1 className="text-xl font-semibold text-slate-900">Admin Summary Draft</h1>
                        <p className="mt-1 text-sm text-slate-500">
                            Paste reviewed text or AI-assisted output. It stays draft until human review.
                        </p>
                    </div>

                    <input className="w-full rounded-lg border-slate-300 text-sm" placeholder="Source label" value={data.source_label} onChange={(e) => setData('source_label', e.target.value)} />
                    {errors.source_label && <p className="text-xs text-rose-600">{errors.source_label}</p>}

                    <input className="w-full rounded-lg border-slate-300 text-sm" placeholder="Title" value={data.title} onChange={(e) => setData('title', e.target.value)} />
                    {errors.title && <p className="text-xs text-rose-600">{errors.title}</p>}

                    <textarea className="min-h-28 w-full rounded-lg border-slate-300 text-sm" placeholder="Source text" value={data.source_text} onChange={(e) => setData('source_text', e.target.value)} />

                    <textarea className="min-h-40 w-full rounded-lg border-slate-300 text-sm" placeholder="Summary" value={data.summary} onChange={(e) => setData('summary', e.target.value)} />
                    {errors.summary && <p className="text-xs text-rose-600">{errors.summary}</p>}

                    <button disabled={processing} className="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white">
                        Save Draft
                    </button>
                </form>

                <div className="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h2 className="text-lg font-semibold text-slate-900">Saved Summaries</h2>

                    <div className="mt-4 divide-y divide-slate-100">
                        {rows.map((summary) => (
                            <div key={summary.uuid} className="py-4">
                                <div className="flex items-center justify-between gap-3">
                                    <div>
                                        <div className="font-medium text-slate-900">{summary.title}</div>
                                        <p className="mt-1 text-sm text-slate-500">{summary.source_label}</p>
                                    </div>

                                    <select className="rounded-lg border-slate-300 text-sm" value={summary.status} onChange={(e) => review(summary.uuid, e.target.value)}>
                                        <option value="draft">draft</option>
                                        <option value="reviewed">reviewed</option>
                                        <option value="archived">archived</option>
                                    </select>
                                </div>

                                <p className="mt-2 line-clamp-3 text-sm text-slate-700">{summary.summary}</p>
                            </div>
                        ))}

                        {rows.length === 0 && (
                            <div className="py-8 text-center text-sm text-slate-500">No summaries found.</div>
                        )}
                    </div>
                </div>
            </div>
        </AdminLayout>
    );
}
