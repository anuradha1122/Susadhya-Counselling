import AdminLayout from '@/Layouts/AdminLayout';
import RoleForm from './RoleForm';
import { Head, useForm } from '@inertiajs/react';

export default function Edit({ managedRole, permissionGroups }) { const form = useForm({ name: managedRole.name, permissions: managedRole.permissions }); form.submit = (e) => { e.preventDefault(); form.put(route('admin.roles.update', managedRole.id)); }; return <AdminLayout title="Edit Role"><Head title="Edit Role" /><div className="mb-6"><h2 className="text-2xl font-bold">Edit role</h2><p className="mt-1 text-sm text-slate-500">Changes apply to every account assigned this role.</p></div><RoleForm form={form} permissionGroups={permissionGroups} submitLabel="Save changes" /></AdminLayout>; }
