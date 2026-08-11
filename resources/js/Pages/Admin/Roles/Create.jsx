import AdminLayout from '@/Layouts/AdminLayout';
import RoleForm from './RoleForm';
import { Head, useForm } from '@inertiajs/react';

export default function Create({ permissionGroups }) { const form = useForm({ name: '', permissions: [] }); form.submit = (e) => { e.preventDefault(); form.post(route('admin.roles.store')); }; return <AdminLayout title="Create Role"><Head title="Create Role" /><div className="mb-6"><h2 className="text-2xl font-bold">Create role</h2><p className="mt-1 text-sm text-slate-500">Build a reusable permission set for user accounts.</p></div><RoleForm form={form} permissionGroups={permissionGroups} submitLabel="Create role" /></AdminLayout>; }
