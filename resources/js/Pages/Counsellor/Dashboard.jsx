import StatCard from '@/Components/StatCard';
import CounsellorLayout from '@/Layouts/CounsellorLayout';
import { Head } from '@inertiajs/react';
import {
    CalendarCheck2,
    CalendarDays,
    CircleDollarSign,
    Clock3,
} from 'lucide-react';

export default function Dashboard({
    stats,
}) {
    return (
        <CounsellorLayout title="Counsellor Dashboard">
            <Head title="Counsellor Dashboard" />

            <section className="mb-6 rounded-3xl bg-gradient-to-r from-teal-700 to-emerald-600 p-6 text-white shadow-lg sm:p-8">
                <p className="text-sm font-semibold uppercase tracking-[0.18em] text-teal-100">
                    My workspace
                </p>

                <h2 className="mt-2 text-2xl font-bold sm:text-3xl">
                    Your counselling schedule at a glance
                </h2>

                <p className="mt-3 max-w-2xl text-sm leading-6 text-teal-50 sm:text-base">
                    Availability and appointment tools will
                    appear here as the operational modules
                    are completed.
                </p>
            </section>

            <section className="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <StatCard
                    title="Today's appointments"
                    value={stats.todayAppointments}
                    icon={CalendarDays}
                />

                <StatCard
                    title="Upcoming"
                    value={stats.upcomingAppointments}
                    icon={Clock3}
                    tone="blue"
                />

                <StatCard
                    title="Completed"
                    value={stats.completedAppointments}
                    icon={CalendarCheck2}
                    tone="violet"
                />

                <StatCard
                    title="Monthly earnings"
                    value={`LKR ${Number(
                        stats.monthlyEarnings
                    ).toLocaleString()}`}
                    icon={CircleDollarSign}
                    tone="amber"
                />
            </section>
        </CounsellorLayout>
    );
}
