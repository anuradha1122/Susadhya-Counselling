import AppointmentMetricGrid from "@/Components/Appointments/AppointmentMetricGrid";
import PrimaryButton from "@/Components/PrimaryButton";
import SecondaryButton from "@/Components/SecondaryButton";
import ClientLayout from "@/Layouts/ClientLayout";
import { Head, Link } from "@inertiajs/react";

export default function Dashboard({ appointmentMetrics }) {
    return (
        <ClientLayout
            header={
                <div>
                    <h2 className="text-xl font-semibold leading-tight text-gray-800">
                        Client Dashboard
                    </h2>
                    <p className="mt-1 text-sm text-gray-500">
                        Your counselling appointments and scheduling summary.
                    </p>
                </div>
            }
        >
            <Head title="Client Dashboard" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                    <div className="rounded-lg border border-indigo-100 bg-indigo-50 p-5">
                        <div className="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                            <div>
                                <h3 className="text-base font-semibold text-indigo-950">
                                    Your Counselling Schedule
                                </h3>
                                <p className="mt-1 text-sm text-indigo-900">
                                    Book, reschedule, or cancel appointments
                                    from your client area. Tiny miracle: the
                                    numbers below are scoped only to you.
                                </p>
                            </div>

                            <div className="flex flex-wrap gap-3">
                                <Link href={route("client.counsellors.index")}>
                                    <SecondaryButton type="button">
                                        Find counsellors
                                    </SecondaryButton>
                                </Link>

                                <Link href={route("client.appointments.index")}>
                                    <PrimaryButton type="button">
                                        My appointments
                                    </PrimaryButton>
                                </Link>
                            </div>
                        </div>
                    </div>

                    <AppointmentMetricGrid
                        metrics={appointmentMetrics}
                        title="My Appointment Metrics"
                        description="Counts for your own appointment requests, confirmations, cancellations, and history."
                    />
                </div>
            </div>
        </ClientLayout>
    );
}
