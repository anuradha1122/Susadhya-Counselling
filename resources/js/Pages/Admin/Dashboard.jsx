import AppointmentMetricGrid from "@/Components/Appointments/AppointmentMetricGrid";
import PrimaryButton from "@/Components/PrimaryButton";
import AdminLayout from "@/Layouts/AdminLayout";
import { Head, Link } from "@inertiajs/react";

export default function Dashboard({ appointmentMetrics }) {
    return (
        <AdminLayout
            header={
                <div>
                    <h2 className="text-xl font-semibold leading-tight text-gray-800">
                        Admin Dashboard
                    </h2>
                    <p className="mt-1 text-sm text-gray-500">
                        Platform overview and appointment operation summary.
                    </p>
                </div>
            }
        >
            <Head title="Admin Dashboard" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                    <div className="rounded-lg border border-indigo-100 bg-indigo-50 p-5">
                        <div className="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                            <div>
                                <h3 className="text-base font-semibold text-indigo-950">
                                    Appointment Operations
                                </h3>
                                <p className="mt-1 text-sm text-indigo-900">
                                    Admin can monitor all appointment activity,
                                    pending confirmations, cancellations, and
                                    reminder status from one place.
                                </p>
                            </div>

                            <Link href={route("admin.appointments.index")}>
                                <PrimaryButton type="button">
                                    Manage appointments
                                </PrimaryButton>
                            </Link>
                        </div>
                    </div>

                    <AppointmentMetricGrid
                        metrics={appointmentMetrics}
                        title="All Appointment Metrics"
                        description="Counts across all clients, counsellors, statuses, reminders, and appointment periods."
                    />
                </div>
            </div>
        </AdminLayout>
    );
}
