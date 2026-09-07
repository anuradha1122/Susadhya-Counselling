import AdminLayout from '@/Layouts/AdminLayout';
import { Head, Link, useForm } from '@inertiajs/react';

export default function Form({ package: servicePackage, services = [], selectedServices = [] }) {
    const isEdit = Boolean(servicePackage);

    const { data, setData, post, put, processing, errors } = useForm({
        name: servicePackage?.name ?? '',
        slug: servicePackage?.slug ?? '',
        description: servicePackage?.description ?? '',
        sessions_count: servicePackage?.sessions_count ?? 1,
        validity_days: servicePackage?.validity_days ?? 30,
        price: servicePackage?.price ?? 0,
        is_subscription: servicePackage?.is_subscription ?? false,
        is_active: servicePackage?.is_active ?? false,
        sort_order: servicePackage?.sort_order ?? 0,
        service_ids: selectedServices ?? [],
    });

    const toggleService = (id) => {
        setData(
            'service_ids',
            data.service_ids.includes(id)
                ? data.service_ids.filter((item) => item !== id)
                : [...data.service_ids, id],
        );
    };

    const submit = (event) => {
        event.preventDefault();

        if (isEdit) {
            put(route('admin.advanced-products.packages.update', servicePackage.uuid));
            return;
        }

        post(route('admin.advanced-products.packages.store'));
    };

    return (
        <AdminLayout>
            <Head title={isEdit ? 'Edit Package' : 'Create Package'} />

            <div className="mx-auto max-w-4xl">
                <form onSubmit={submit} className="space-y-6 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h1 className="text-2xl font-semibold text-slate-900">{isEdit ? 'Edit Package' : 'Create Package'}</h1>

                    <div className="grid gap-4 md:grid-cols-2">
                        <Field label="Name" error={errors.name}>
                            <input className="w-full rounded-lg border-slate-300 text-sm" value={data.name} onChange={(e) => setData('name', e.target.value)} />
                        </Field>

                        <Field label="Slug" error={errors.slug}>
                            <input className="w-full rounded-lg border-slate-300 text-sm" value={data.slug} onChange={(e) => setData('slug', e.target.value)} />
                        </Field>

                        <Field label="Sessions" error={errors.sessions_count}>
                            <input type="number" className="w-full rounded-lg border-slate-300 text-sm" value={data.sessions_count} onChange={(e) => setData('sessions_count', e.target.value)} />
                        </Field>

                        <Field label="Validity days" error={errors.validity_days}>
                            <input type="number" className="w-full rounded-lg border-slate-300 text-sm" value={data.validity_days} onChange={(e) => setData('validity_days', e.target.value)} />
                        </Field>

                        <Field label="Price" error={errors.price}>
                            <input type="number" step="0.01" className="w-full rounded-lg border-slate-300 text-sm" value={data.price} onChange={(e) => setData('price', e.target.value)} />
                        </Field>

                        <Field label="Sort order" error={errors.sort_order}>
                            <input type="number" className="w-full rounded-lg border-slate-300 text-sm" value={data.sort_order} onChange={(e) => setData('sort_order', e.target.value)} />
                        </Field>
                    </div>

                    <Field label="Description" error={errors.description}>
                        <textarea className="min-h-28 w-full rounded-lg border-slate-300 text-sm" value={data.description ?? ''} onChange={(e) => setData('description', e.target.value)} />
                    </Field>

                    <div className="grid gap-2 rounded-lg border border-slate-200 p-4">
                        <div className="text-sm font-medium text-slate-700">Included services</div>

                        {services.map((service) => (
                            <label key={service.id} className="flex items-center gap-2 text-sm text-slate-700">
                                <input type="checkbox" checked={data.service_ids.includes(service.id)} onChange={() => toggleService(service.id)} />
                                {service.name}
                            </label>
                        ))}
                    </div>

                    <div className="flex flex-wrap gap-6">
                        <label className="flex items-center gap-2 text-sm text-slate-700">
                            <input type="checkbox" checked={data.is_subscription} onChange={(e) => setData('is_subscription', e.target.checked)} />
                            Subscription package
                        </label>

                        <label className="flex items-center gap-2 text-sm text-slate-700">
                            <input type="checkbox" checked={data.is_active} onChange={(e) => setData('is_active', e.target.checked)} />
                            Active
                        </label>
                    </div>

                    <div className="flex justify-end gap-3">
                        <Link href={route('admin.advanced-products.packages.index')} className="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700">
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
