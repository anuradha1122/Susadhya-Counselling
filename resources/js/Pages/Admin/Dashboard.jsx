import AdminLayout from '@/Layouts/AdminLayout';
import { Head } from '@inertiajs/react';
import {
    CalendarDays,
    CircleDollarSign,
    Stethoscope,
    Users,
} from 'lucide-react';

export default function Dashboard({
    statistics = {},
}) {
    const cards = [
        {
            label: 'Counsellors',
            value: statistics.counsellors ?? 0,
            icon: Stethoscope,
            color: 'bg-teal-100 text-teal-700',
        },
        {
            label: 'Appointments',
            value: statistics.appointments ?? 0,
            icon: CalendarDays,
            color: 'bg-sky-100 text-sky-700',
        },
        {
            label: 'Users',
            value: statistics.users ?? 0,
            icon: Users,
            color: 'bg-violet-100 text-violet-700',
        },
        {
            label: 'Revenue',
            value: statistics.revenue ?? 'LKR 0',
            icon: CircleDollarSign,
            color: 'bg-amber-100 text-amber-700',
        },
    ];

    return (
        <AdminLayout title="Admin Dashboard">
            <Head title="Admin Dashboard" />

            <div className="space-y-6">
                <section>
                    <h2 className="text-2xl font-bold text-slate-900">
                        Welcome back
                    </h2>

                    <p className="mt-1 text-sm text-slate-500">
                        Here is an overview of the counselling management
                        system.
                    </p>
                </section>

                <section className="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    {cards.map((card) => {
                        const Icon = card.icon;

                        return (
                            <div
                                key={card.label}
                                className="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"
                            >
                                <div className="flex items-center justify-between">
                                    <div>
                                        <p className="text-sm font-medium text-slate-500">
                                            {card.label}
                                        </p>

                                        <p className="mt-2 text-2xl font-bold text-slate-900">
                                            {card.value}
                                        </p>
                                    </div>

                                    <div
                                        className={`rounded-xl p-3 ${card.color}`}
                                    >
                                        <Icon className="h-6 w-6" />
                                    </div>
                                </div>
                            </div>
                        );
                    })}
                </section>
            </div>
        </AdminLayout>
    );
}
