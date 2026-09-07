import AdminLayout from '@/Layouts/AdminLayout';
import { Head, Link, useForm } from '@inertiajs/react';

export default function Form({ translation, locales = ['en', 'si', 'ta'] }) {
    const isEdit = Boolean(translation);

    const { data, setData, post, put, processing, errors } = useForm({
        translatable_type: translation?.translatable_type ?? '',
        translatable_id: translation?.translatable_id ?? '',
        locale: translation?.locale ?? 'si',
        field: translation?.field ?? 'title',
        value: translation?.value ?? '',
    });

    const submit = (event) => {
        event.preventDefault();

        if (isEdit) {
            put(route('admin.advanced-products.translations.update', translation.uuid));
            return;
        }

        post(route('admin.advanced-products.translations.store'));
    };

    return (
        <AdminLayout>
            <Head title={isEdit ? 'Edit Translation' : 'Create Translation'} />

            <div className="mx-auto max-w-3xl">
                <form onSubmit={submit} className="space-y-5 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h1 className="text-2xl font-semibold text-slate-900">
                        {isEdit ? 'Edit Translation' : 'Create Translation'}
                    </h1>

                    <div className="grid gap-4 md:grid-cols-2">
                        <Field label="Type" error={errors.translatable_type}>
                            <input className="w-full rounded-lg border-slate-300 text-sm" value={data.translatable_type} onChange={(e) => setData('translatable_type', e.target.value)} placeholder="App\Models\CmsPage" />
                        </Field>

                        <Field label="Record ID" error={errors.translatable_id}>
                            <input type="number" className="w-full rounded-lg border-slate-300 text-sm" value={data.translatable_id} onChange={(e) => setData('translatable_id', e.target.value)} />
                        </Field>

                        <Field label="Locale" error={errors.locale}>
                            <select className="w-full rounded-lg border-slate-300 text-sm" value={data.locale} onChange={(e) => setData('locale', e.target.value)}>
                                {locales.map((locale) => (
                                    <option key={locale} value={locale}>{locale}</option>
                                ))}
                            </select>
                        </Field>

                        <Field label="Field" error={errors.field}>
                            <input className="w-full rounded-lg border-slate-300 text-sm" value={data.field} onChange={(e) => setData('field', e.target.value)} />
                        </Field>
                    </div>

                    <Field label="Value" error={errors.value}>
                        <textarea className="min-h-40 w-full rounded-lg border-slate-300 text-sm" value={data.value} onChange={(e) => setData('value', e.target.value)} />
                    </Field>

                    <div className="flex justify-end gap-3">
                        <Link href={route('admin.advanced-products.translations.index')} className="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700">
                            Cancel
                        </Link>
                        <button disabled={processing} className="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white">
                            Save
                        </button>
                    </div>
                </form>
            </div>
        </AdminLayout>
    );
}

function Field({ label, error, children }) {
    return (
        <label className="block space-y-1">
            <span className="text-sm font-medium text-slate-700">{label}</span>
            {children}
            {error && <span className="text-xs text-rose-600">{error}</span>}
        </label>
    );
}
