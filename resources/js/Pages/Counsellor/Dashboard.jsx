import AppointmentMetricGrid from "@/Components/Appointments/AppointmentMetricGrid";
import PrimaryButton from "@/Components/PrimaryButton";
import SecondaryButton from "@/Components/SecondaryButton";
import CounsellorLayout from "@/Layouts/CounsellorLayout";
import { Head, Link } from "@inertiajs/react";

export default function Dashboard({ appointmentMetrics }) {
    return (
        <CounsellorLayout
            header={
                <div>
                    <h2 className="text-xl font-semibold leading-tight text-gray-800">
                        Counsellor Dashboard
                    </h2>
                    <p className="mt-1 text-sm text-gray-500">
                        Appointment requests, confirmed sessions, and
                        counselling workflow summary.
                    </p>
                </div>
            }
        >
            <Head title="Counsellor Dashboard" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                    <div className="rounded-lg border border-indigo-100 bg-indigo-50 p-5">
                        <div className="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                            <div>
                                <h3 className="text-base font-semibold text-indigo-950">
                                    Counsellor Appointment Workbench
                                </h3>
                                <p className="mt-1 text-sm text-indigo-900">
                                    Review appointment requests, confirm
                                    sessions, and close completed or no-show
                                    appointments without needing a separate
                                    spreadsheet shrine.
                                </p>
                            </div>

                            <div className="flex flex-wrap gap-3">
                                <Link
                                    href={route(
                                        "counsellor.availability.index",
                                    )}
                                >
                                    <SecondaryButton type="button">
                                        My availability
                                    </SecondaryButton>
                                </Link>

                                <Link
                                    href={route(
                                        "counsellor.appointments.index",
                                    )}
                                >
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
                        description="Counts for appointments assigned to your counsellor profile."
                    />
                </div>
            </div>
        </CounsellorLayout>
    );
}
