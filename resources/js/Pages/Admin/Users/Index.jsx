import Pagination from '@/Components/Pagination';
import useAuthorization from '@/Hooks/useAuthorization';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, Link, router } from '@inertiajs/react';
import { Pencil, Plus, Search, Trash2 } from 'lucide-react';
import { useState } from 'react';

export default function Index({ users, filters, roles, can: abilities }) {
    const [values, setValues] = useState({ search: filters.search ?? '', role: filters.role ?? '', status: filters.status ?? '' });
    const { can } = useAuthorization();
    const apply = (e) => { e.preventDefault(); router.get(route('admin.users.index'), values, { preserveState: true, replace: true }); };
    const remove = (user) => { if (confirm(`Delete ${user.name}? This cannot be undone.`)) router.delete(route('admin.users.destroy', user.id)); };

    return (
        <AdminLayout title="Users"><Head title="Users" />
            <div className="mb-6 flex flex-col justify-between gap-4 sm:flex-row sm:items-center"><div><h2 className="text-2xl font-bold">User management</h2><p className="mt-1 text-sm text-slate-500">Search, create, update, activate, and safely remove accounts.</p></div>{abilities.create && <Link href={route('admin.users.create')} className="inline-flex items-center justify-center gap-2 rounded-xl bg-teal-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-teal-700"><Plus className="h-4 w-4" /> New user</Link>}</div>
            <form onSubmit={apply} className="mb-5 grid gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm md:grid-cols-[1fr_180px_160px_auto]">
                <input value={values.search} onChange={(e) => setValues({ ...values, search: e.target.value })} placeholder="Search name, email or phone" className="rounded-xl border-slate-300 text-sm focus:border-teal-500 focus:ring-teal-500" />
                <select value={values.role} onChange={(e) => setValues({ ...values, role: e.target.value })} className="rounded-xl border-slate-300 text-sm"><option value="">All roles</option>{roles.map((role) => <option key={role.id} value={role.name}>{role.name.replaceAll('_', ' ')}</option>)}</select>
                <select value={values.status} onChange={(e) => setValues({ ...values, status: e.target.value })} className="rounded-xl border-slate-300 text-sm"><option value="">All statuses</option><option value="active">Active</option><option value="inactive">Inactive</option></select>
                <button className="inline-flex items-center justify-center gap-2 rounded-xl bg-slate-800 px-4 py-2 text-sm font-medium text-white"><Search className="h-4 w-4" /> Filter</button>
            </form>
            <div className="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"><div className="overflow-x-auto"><table className="min-w-full divide-y divide-slate-200 text-sm"><thead className="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500"><tr><th className="px-5 py-3">User</th><th className="px-5 py-3">Role</th><th className="px-5 py-3">Status</th><th className="px-5 py-3">Created</th><th className="px-5 py-3 text-right">Actions</th></tr></thead><tbody className="divide-y divide-slate-100">{users.data.map((user) => <tr key={user.id} className="hover:bg-slate-50"><td className="px-5 py-4"><p className="font-medium text-slate-900">{user.name}</p><p className="text-xs text-slate-500">{user.email}{user.phone ? ` · ${user.phone}` : ''}</p></td><td className="px-5 py-4 capitalize">{user.role?.replaceAll('_', ' ') ?? 'Unassigned'}</td><td className="px-5 py-4"><span className={`rounded-full px-2.5 py-1 text-xs font-semibold ${user.is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700'}`}>{user.is_active ? 'Active' : 'Inactive'}</span></td><td className="px-5 py-4 text-slate-500">{user.created_at}</td><td className="px-5 py-4"><div className="flex justify-end gap-2">{can('users.update') && <Link href={route('admin.users.edit', user.id)} className="rounded-lg p-2 text-slate-500 hover:bg-teal-50 hover:text-teal-700" aria-label="Edit"><Pencil className="h-4 w-4" /></Link>}{abilities.delete && !user.is_super_admin && <button onClick={() => remove(user)} className="rounded-lg p-2 text-slate-500 hover:bg-rose-50 hover:text-rose-700" aria-label="Delete"><Trash2 className="h-4 w-4" /></button>}</div></td></tr>)}</tbody></table></div>{users.data.length === 0 && <p className="p-10 text-center text-sm text-slate-500">No users match the selected filters.</p>}</div>
            <Pagination links={users.links} />
        </AdminLayout>
    );
}
