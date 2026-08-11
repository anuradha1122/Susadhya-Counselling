import AdminLayout from '@/Layouts/AdminLayout';
import UserForm from './UserForm';
import { Head, useForm } from '@inertiajs/react';

export default function Edit({ managedUser, roles }) {
    const form = useForm({ name: managedUser.name, email: managedUser.email, phone: managedUser.phone ?? '', role: managedUser.role ?? '', password: '', password_confirmation: '', is_active: managedUser.is_active });
    form.submit = (e) => { e.preventDefault(); form.put(route('admin.users.update', managedUser.id)); };

    return <AdminLayout title="Edit User"><Head title="Edit User" /><div className="mb-6"><h2 className="text-2xl font-bold">Edit user account</h2><p className="mt-1 text-sm text-slate-500">Update identity, access role, password, and account status.</p></div><UserForm form={form} roles={roles} submitLabel="Save changes" lockRole={managedUser.is_super_admin} /></AdminLayout>;
}
