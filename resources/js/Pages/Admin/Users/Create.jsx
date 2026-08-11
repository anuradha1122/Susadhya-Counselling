import AdminLayout from '@/Layouts/AdminLayout';
import UserForm from './UserForm';
import { Head, useForm } from '@inertiajs/react';

export default function Create({ roles }) {
    const form = useForm({ name: '', email: '', phone: '', role: '', password: '', password_confirmation: '', is_active: true });
    form.submit = (e) => { e.preventDefault(); form.post(route('admin.users.store')); };

    return <AdminLayout title="Create User"><Head title="Create User" /><div className="mb-6"><h2 className="text-2xl font-bold">Create user account</h2><p className="mt-1 text-sm text-slate-500">Create an administrator, counsellor, or custom-role account.</p></div><UserForm form={form} roles={roles} submitLabel="Create user" /></AdminLayout>;
}
