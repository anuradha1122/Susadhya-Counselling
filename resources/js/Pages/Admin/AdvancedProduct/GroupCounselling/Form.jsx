import AdminLayout from '@/Layouts/AdminLayout';
import { Head, Link, useForm } from '@inertiajs/react';

export default function Form({ program, services = [], counsellors = [] }) {
    const isEdit = Boolean(program);

    const { data, setData, post, put, processing, errors } = useForm({
        counselling_service_id: program?.counselling_service_id ?? '',
        lead_counsellor_profile_id: program?.lead_counsellor_profile_id ?? '',
        title: program?.title ?? '',
        slug: program?.slug ?? '',
        description: program?.description ?? '',
        mode: program?.mode ?? 'online',
        location: program?.location ?? '',
        capacity: program?.capacity ?? 10,
        starts_on: program?.starts_on ?? '',
        ends_on: program?.ends_on ?? '',
        price: program?.price ?? 0,
        requires_approval: program?.requires_approval ?? true,
        is_active: program?.is_active ?? false,
    });

    const submit = (event) => {
        event.preventDefault();

        if (isEdit) {
            put(route('admin.advanced-products.group-counselling.update', program.uuid));
            return;
        }

        post(route('admin.advanced-products.group-counselling.store'));
    };

    return (
        <AdminLayout>
            <Head title={isEdit ? 'Edit Group Program' : 'Create Group Program'} />

            <div className="mx-auto max-w-4xl">
                <div className="mb-6">
                    <h1 className="text-2xl font-semibold text-slate-900">
                        {isEdit ? 'Edit Group Program' : 'Create Group Program'}
                    </h1>
                    <p className="mt-1 text-sm text-slate-500">
                        Keep programs inactive until clinical and product review is complete.
                    </p>
                </div>

                <form onSubmit={submit} className="space-y-6 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div className="grid gap-4 md:grid-cols-2">
                        <Field label="Title" error={errors.title}>
                            <input className="w-full rounded-lg border-slate-300 text-sm" value={data.title} onChange={(e) => setData('title', e.target.value)} />
                        </Field>

                        <Field label="Slug" error={errors.slug}>
                            <input className="w-full rounded-lg border-slate-300 text-sm" value={data.slug} onChange={(e) => setData('slug', e.target.value)} />
                        </Field>

                        <Field label="Service" error={errors.counselling_service_id}>
                            <select className="w-full rounded-lg border-slate-300 text-sm" value={data.counselling_service_id} onChange={(e) => setData('counselling_service_id', e.target.value)}>
                                <option value="">Select service</option>
                                {services.map((service) => (
                                    <option key={service.id} value={service.id}>{service.name}</option>
                                ))}
                            </select>
                        </Field>

                        <Field label="Lead Counsellor" error={errors.lead_counsellor_profile_id}>
                            <select className="w-full rounded-lg border-slate-300 text-sm" value={data.lead_counsellor_profile_id} onChange={(e) => setData('lead_counsellor_profile_id', e.target.value)}>
                                <option value="">Select counsellor</option>
                                {counsellors.map((counsellor) => (
                                    <option key={counsellor.id} value={counsellor.id}>{counsellor.name}</option>
                                ))}
                            </select>
                        </Field>

                        <Field label="Mode" error={errors.mode}>
                            <select className="w-full rounded-lg border-slate-300 text-sm" value={data.mode} onChange={(e) => setData('mode', e.target.value)}>
                                <option value="online">Online</option>
                                <option value="in_person">In person</option>
                                <option value="hybrid">Hybrid</option>
                            </select>
                        </Field>

                        <Field label="Capacity" error={errors.capacity}>
                            <input type="number" className="w-full rounded-lg border-slate-300 text-sm" value={data.capacity} onChange={(e) => setData('capacity', e.target.value)} />
                        </Field>

                        <Field label="Start date" error={errors.starts_on}>
                            <input type="date" className="w-full rounded-lg border-slate-300 text-sm" value={data.starts_on ?? ''} onChange={(e) => setData('starts_on', e.target.value)} />
                        </Field>

                        <Field label="End date" error={errors.ends_on}>
                            <input type="date" className="w-full rounded-lg border-slate-300 text-sm" value={data.ends_on ?? ''} onChange={(e) => setData('ends_on', e.target.value)} />
                        </Field>

                        <Field label="Price" error={errors.price}>
                            <input type="number" step="0.01" className="w-full rounded-lg border-slate-300 text-sm" value={data.price} onChange={(e) => setData('price', e.target.value)} />
                        </Field>

                        <Field label="Location" error={errors.location}>
                            <input className="w-full rounded-lg border-slate-300 text-sm" value={data.location ?? ''} onChange={(e) => setData('location', e.target.value)} />
                        </Field>
                    </div>

                    <Field label="Description" error={errors.description}>
                        <textarea className="min-h-32 w-full rounded-lg border-slate-300 text-sm" value={data.description ?? ''} onChange={(e) => setData('description', e.target.value)} />
                    </Field>

                    <div className="flex flex-wrap gap-6">
                        <label className="flex items-center gap-2 text-sm text-slate-700">
                            <input type="checkbox" checked={data.requires_approval} onChange={(e) => setData('requires_approval', e.target.checked)} />
                            Requires approval
                        </label>

                        <label className="flex items-center gap-2 text-sm text-slate-700">
                            <input type="checkbox" checked={data.is_active} onChange={(e) => setData('is_active', e.target.checked)} />
                            Active
                        </label>
                    </div>

                    <div className="flex justify-end gap-3">
                        <Link href={route('admin.advanced-products.group-counselling.index')} className="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700">
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
