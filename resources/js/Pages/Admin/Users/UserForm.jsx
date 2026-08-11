import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import { Link } from '@inertiajs/react';

export default function UserForm({ form, roles, submitLabel, lockRole = false }) {
    const { data, setData, errors, processing, submit } = form;

    return (
        <form onSubmit={submit} className="space-y-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div className="grid gap-6 md:grid-cols-2">
                <Field label="Full name" error={errors.name}>
                    <TextInput value={data.name} onChange={(e) => setData('name', e.target.value)} className="mt-1 block w-full" required />
                </Field>
                <Field label="Email address" error={errors.email}>
                    <TextInput type="email" value={data.email} onChange={(e) => setData('email', e.target.value)} className="mt-1 block w-full" required />
                </Field>
                <Field label="Phone" error={errors.phone}>
                    <TextInput value={data.phone} onChange={(e) => setData('phone', e.target.value)} className="mt-1 block w-full" />
                </Field>
                <Field label="Role" error={errors.role}>
                    <select value={data.role} disabled={lockRole} onChange={(e) => setData('role', e.target.value)} className="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 disabled:bg-slate-100" required>
                        <option value="">Select a role</option>
                        {roles.map((role) => <option key={role.id} value={role.name}>{role.name.replaceAll('_', ' ')}</option>)}
                    </select>
                </Field>
                <Field label="Password" error={errors.password}>
                    <TextInput type="password" value={data.password} onChange={(e) => setData('password', e.target.value)} className="mt-1 block w-full" />
                    <p className="mt-1 text-xs text-slate-500">Leave blank when editing to keep the current password.</p>
                </Field>
                <Field label="Confirm password" error={errors.password_confirmation}>
                    <TextInput type="password" value={data.password_confirmation} onChange={(e) => setData('password_confirmation', e.target.value)} className="mt-1 block w-full" />
                </Field>
            </div>
            <label className="flex items-center gap-3 rounded-xl bg-slate-50 p-4">
                <input type="checkbox" checked={data.is_active} onChange={(e) => setData('is_active', e.target.checked)} className="rounded border-slate-300 text-teal-600 focus:ring-teal-500" />
                <span><span className="block text-sm font-medium text-slate-800">Active account</span><span className="text-xs text-slate-500">Inactive users are signed out and cannot access the system.</span></span>
            </label>
            <InputError message={errors.is_active} />
            <div className="flex justify-end gap-3 border-t border-slate-100 pt-5">
                <Link href={route('admin.users.index')} className="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50">Cancel</Link>
                <PrimaryButton disabled={processing}>{submitLabel}</PrimaryButton>
            </div>
        </form>
    );
}

function Field({ label, error, children }) {
    return <div><InputLabel value={label} />{children}<InputError message={error} className="mt-2" /></div>;
}
