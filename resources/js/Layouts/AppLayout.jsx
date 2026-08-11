import ApplicationLogo from '@/Components/ApplicationLogo';
import useAuthorization from '@/Hooks/useAuthorization';
import { Link, router, usePage } from '@inertiajs/react';
import { LogOut, Menu, UserRound, X } from 'lucide-react';
import { useState } from 'react';

export default function AppLayout({
    title,
    navigation = [],
    children,
}) {
    const [sidebarOpen, setSidebarOpen] = useState(false);
    const { user, can } = useAuthorization();
    const { flash } = usePage().props;

    const logout = () => {
        router.post(route('logout'));
    };

    const renderNavigation = () => (
        <>
            <div className="flex h-20 items-center justify-between border-b border-slate-200 px-5">
                <ApplicationLogo />

                <button
                    type="button"
                    onClick={() => setSidebarOpen(false)}
                    className="rounded-lg p-2 text-slate-500 hover:bg-slate-100 lg:hidden"
                    aria-label="Close navigation"
                >
                    <X className="h-5 w-5" />
                </button>
            </div>

            <nav className="flex-1 space-y-1 overflow-y-auto p-4">
                {navigation.map((item) => {
                    if (
                        item.permission &&
                        !can(item.permission)
                    ) {
                        return null;
                    }

                    const Icon = item.icon;

                    if (item.disabled) {
                        return (
                            <div
                                key={item.label}
                                title="Available in a later module"
                                className="flex cursor-not-allowed items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-slate-400"
                            >
                                <Icon className="h-5 w-5" />

                                <span>{item.label}</span>

                                <span className="ml-auto text-[10px] font-semibold uppercase tracking-wide">
                                    Soon
                                </span>
                            </div>
                        );
                    }

                    if (
                        !item.routeName ||
                        !route().has(item.routeName)
                    ) {
                        return null;
                    }

                    const active = route().current(
                        item.active ?? item.routeName,
                    );

                    return (
                        <Link
                            key={item.routeName}
                            href={route(item.routeName)}
                            onClick={() => setSidebarOpen(false)}
                            className={`flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition ${
                                active
                                    ? 'bg-teal-600 text-white shadow-sm'
                                    : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900'
                            }`}
                        >
                            <Icon className="h-5 w-5" />

                            <span>{item.label}</span>
                        </Link>
                    );
                })}
            </nav>
        </>
    );

    return (
        <div className="min-h-screen bg-slate-50 text-slate-900">
            <aside className="fixed inset-y-0 left-0 z-30 hidden w-72 flex-col border-r border-slate-200 bg-white lg:flex">
                {renderNavigation()}
            </aside>

            {sidebarOpen && (
                <div className="fixed inset-0 z-40 lg:hidden">
                    <button
                        type="button"
                        onClick={() => setSidebarOpen(false)}
                        className="absolute inset-0 bg-slate-950/40"
                        aria-label="Close navigation overlay"
                    />

                    <aside className="relative flex h-full w-72 flex-col bg-white shadow-xl">
                        {renderNavigation()}
                    </aside>
                </div>
            )}

            <div className="lg:pl-72">
                <header className="sticky top-0 z-20 flex h-20 items-center gap-4 border-b border-slate-200 bg-white/95 px-4 backdrop-blur sm:px-6 lg:px-8">
                    <button
                        type="button"
                        onClick={() => setSidebarOpen(true)}
                        className="rounded-lg p-2 text-slate-600 hover:bg-slate-100 lg:hidden"
                        aria-label="Open navigation"
                    >
                        <Menu className="h-6 w-6" />
                    </button>

                    <div className="min-w-0 flex-1">
                        <h1 className="truncate text-lg font-semibold">
                            {title}
                        </h1>

                        <p className="truncate text-xs text-slate-500">
                            Secure counselling management
                        </p>
                    </div>

                    {route().has('profile.edit') && (
                        <Link
                            href={route('profile.edit')}
                            className="hidden items-center gap-2 rounded-xl px-3 py-2 text-sm text-slate-600 hover:bg-slate-100 sm:flex"
                        >
                            <UserRound className="h-5 w-5" />

                            <span className="max-w-40 truncate">
                                {user?.name}
                            </span>
                        </Link>
                    )}

                    <button
                        type="button"
                        onClick={logout}
                        className="rounded-xl p-2.5 text-slate-500 hover:bg-rose-50 hover:text-rose-600"
                        aria-label="Log out"
                    >
                        <LogOut className="h-5 w-5" />
                    </button>
                </header>

                <main className="p-4 sm:p-6 lg:p-8">
                    {flash?.success && (
                        <div className="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                            {flash.success}
                        </div>
                    )}

                    {flash?.error && (
                        <div className="mb-5 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                            {flash.error}
                        </div>
                    )}

                    {children}
                </main>
            </div>
        </div>
    );
}
