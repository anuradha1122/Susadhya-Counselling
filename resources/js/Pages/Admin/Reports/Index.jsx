import Pagination from "@/Components/Pagination";
import AdminLayout from "@/Layouts/AdminLayout";
import { Head, router, useForm } from "@inertiajs/react";
import {
    CalendarDays,
    CheckCircle2,
    Download,
    Gauge,
    RotateCcw,
    UserRoundCheck,
    XCircle,
} from "lucide-react";

const label = (value) =>
    String(value ?? "")
        .replaceAll("_", " ")
        .replace(/\b\w/g, (letter) => letter.toUpperCase());

function MetricCard({ title, value, icon: Icon, suffix = "" }) {
    return (
        <div className="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <div className="flex items-center justify-between gap-4">
                <div>
                    <p className="text-sm text-slate-500">{title}</p>

                    <p className="mt-2 text-2xl font-semibold text-slate-900">
                        {value}
                        {suffix}
                    </p>
                </div>

                <div className="rounded-xl bg-slate-100 p-3 text-slate-600">
                    <Icon className="h-5 w-5" />
                </div>
            </div>
        </div>
    );
}

function cleanFilters(filters) {
    return Object.fromEntries(
        Object.entries(filters).filter(
            ([, value]) =>
                value !== "" &&
                value !== null &&
                value !== undefined,
        ),
    );
}

export default function Index({
    filters,
    summary,
    appointments,
    counsellorActivity,
    cancellationsByService,
    counsellors,
    options,
}) {
    const form = useForm({
        from: filters.from ?? "",
        to: filters.to ?? "",
        counsellor_profile_id:
            filters.counsellor_profile_id ?? "",
        status: filters.status ?? "",
        mode: filters.mode ?? "",
    });

    const applyFilters = (event) => {
        event.preventDefault();

        router.get(
            route("admin.reports.index"),
            cleanFilters(form.data),
            {
                preserveState: true,
                replace: true,
            },
        );
    };

    const exportUrl = (routeName) =>
        route(
            routeName,
            cleanFilters(form.data),
        );

    return (
        <AdminLayout title="Reports & Exports">
            <Head title="Reports & Exports" />

            <div className="mx-auto max-w-7xl space-y-6">
                <section className="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                    <p className="text-sm font-medium text-indigo-600">
                        M17 · Operational Reports
                    </p>

                    <h2 className="mt-1 text-2xl font-semibold text-slate-900">
                        Appointments & utilisation
                    </h2>

                    <p className="mt-2 max-w-3xl text-sm leading-6 text-slate-600">
                        Operational reporting contains scheduling and
                        service activity only. Clinical note content and
                        financial revenue data are intentionally excluded.
                    </p>
                </section>

                <section className="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                    <form
                        onSubmit={applyFilters}
                        className="grid gap-4 md:grid-cols-5"
                    >
                        <div>
                            <label className="text-sm font-medium text-slate-700">
                                From
                            </label>
                            <input
                                type="date"
                                value={form.data.from}
                                onChange={(event) =>
                                    form.setData(
                                        "from",
                                        event.target.value,
                                    )
                                }
                                className="mt-1 block w-full rounded-lg border-slate-300"
                            />
                        </div>

                        <div>
                            <label className="text-sm font-medium text-slate-700">
                                To
                            </label>
                            <input
                                type="date"
                                value={form.data.to}
                                onChange={(event) =>
                                    form.setData(
                                        "to",
                                        event.target.value,
                                    )
                                }
                                className="mt-1 block w-full rounded-lg border-slate-300"
                            />
                        </div>

                        <div>
                            <label className="text-sm font-medium text-slate-700">
                                Counsellor
                            </label>
                            <select
                                value={
                                    form.data
                                        .counsellor_profile_id
                                }
                                onChange={(event) =>
                                    form.setData(
                                        "counsellor_profile_id",
                                        event.target.value,
                                    )
                                }
                                className="mt-1 block w-full rounded-lg border-slate-300"
                            >
                                <option value="">
                                    All counsellors
                                </option>

                                {counsellors.map(
                                    (counsellor) => (
                                        <option
                                            key={counsellor.id}
                                            value={counsellor.id}
                                        >
                                            {counsellor.name}
                                        </option>
                                    ),
                                )}
                            </select>
                        </div>

                        <div>
                            <label className="text-sm font-medium text-slate-700">
                                Status
                            </label>
                            <select
                                value={form.data.status}
                                onChange={(event) =>
                                    form.setData(
                                        "status",
                                        event.target.value,
                                    )
                                }
                                className="mt-1 block w-full rounded-lg border-slate-300"
                            >
                                <option value="">
                                    All statuses
                                </option>

                                {options.statuses.map(
                                    (status) => (
                                        <option
                                            key={status}
                                            value={status}
                                        >
                                            {label(status)}
                                        </option>
                                    ),
                                )}
                            </select>
                        </div>

                        <div>
                            <label className="text-sm font-medium text-slate-700">
                                Mode
                            </label>
                            <select
                                value={form.data.mode}
                                onChange={(event) =>
                                    form.setData(
                                        "mode",
                                        event.target.value,
                                    )
                                }
                                className="mt-1 block w-full rounded-lg border-slate-300"
                            >
                                <option value="">
                                    All modes
                                </option>

                                {options.modes.map((mode) => (
                                    <option
                                        key={mode}
                                        value={mode}
                                    >
                                        {label(mode)}
                                    </option>
                                ))}
                            </select>
                        </div>

                        <div className="md:col-span-5 flex flex-wrap justify-end gap-2">
                            <button
                                type="button"
                                onClick={() =>
                                    router.get(
                                        route(
                                            "admin.reports.index",
                                        ),
                                    )
                                }
                                className="inline-flex items-center gap-2 rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700"
                            >
                                <RotateCcw className="h-4 w-4" />
                                Reset
                            </button>

                            <button
                                type="submit"
                                className="rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white"
                            >
                                Apply filters
                            </button>

                            <a
                                href={exportUrl(
                                    "admin.reports.csv",
                                )}
                                className="inline-flex items-center gap-2 rounded-lg border border-indigo-300 px-4 py-2 text-sm font-medium text-indigo-700"
                            >
                                <Download className="h-4 w-4" />
                                CSV
                            </a>

                            <a
                                href={exportUrl(
                                    "admin.reports.pdf",
                                )}
                                className="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white"
                            >
                                <Download className="h-4 w-4" />
                                PDF
                            </a>
                        </div>
                    </form>
                </section>

                <section className="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    <MetricCard
                        title="Appointments"
                        value={summary.total}
                        icon={CalendarDays}
                    />

                    <MetricCard
                        title="Completed"
                        value={summary.completed}
                        icon={CheckCircle2}
                    />

                    <MetricCard
                        title="Cancelled"
                        value={summary.cancelled}
                        icon={XCircle}
                    />

                    <MetricCard
                        title="Utilisation"
                        value={summary.utilisation_rate}
                        suffix="%"
                        icon={Gauge}
                    />

                    <MetricCard
                        title="Confirmed"
                        value={summary.confirmed}
                        icon={UserRoundCheck}
                    />

                    <MetricCard
                        title="No show"
                        value={summary.no_show}
                        icon={XCircle}
                    />

                    <MetricCard
                        title="Cancellation rate"
                        value={summary.cancellation_rate}
                        suffix="%"
                        icon={Gauge}
                    />

                    <MetricCard
                        title="No-show rate"
                        value={summary.no_show_rate}
                        suffix="%"
                        icon={Gauge}
                    />
                </section>

                <section className="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h3 className="text-lg font-semibold text-slate-900">
                        Counsellor activity
                    </h3>

                    <div className="mt-5 overflow-x-auto">
                        <table className="min-w-full divide-y divide-slate-200 text-sm">
                            <thead>
                                <tr className="text-left text-xs uppercase tracking-wide text-slate-500">
                                    <th className="px-3 py-3">
                                        Counsellor
                                    </th>
                                    <th className="px-3 py-3">
                                        Total
                                    </th>
                                    <th className="px-3 py-3">
                                        Completed
                                    </th>
                                    <th className="px-3 py-3">
                                        No show
                                    </th>
                                    <th className="px-3 py-3">
                                        Cancelled
                                    </th>
                                    <th className="px-3 py-3">
                                        Utilisation
                                    </th>
                                </tr>
                            </thead>

                            <tbody className="divide-y divide-slate-100">
                                {counsellorActivity.map(
                                    (row) => (
                                        <tr key={row.id}>
                                            <td className="px-3 py-3 font-medium text-slate-900">
                                                {row.name}
                                            </td>
                                            <td className="px-3 py-3">
                                                {
                                                    row.total_appointments
                                                }
                                            </td>
                                            <td className="px-3 py-3">
                                                {
                                                    row.completed_appointments
                                                }
                                            </td>
                                            <td className="px-3 py-3">
                                                {
                                                    row.no_show_appointments
                                                }
                                            </td>
                                            <td className="px-3 py-3">
                                                {
                                                    row.cancelled_appointments
                                                }
                                            </td>
                                            <td className="px-3 py-3">
                                                {
                                                    row.utilisation_rate
                                                }
                                                %
                                            </td>
                                        </tr>
                                    ),
                                )}
                            </tbody>
                        </table>
                    </div>
                </section>

                <section className="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h3 className="text-lg font-semibold text-slate-900">
                        Cancellations by service
                    </h3>

                    <div className="mt-5 space-y-3">
                        {cancellationsByService.map(
                            (row) => (
                                <div
                                    key={row.service_name}
                                    className="flex items-center justify-between rounded-lg border border-slate-200 px-4 py-3"
                                >
                                    <span className="text-sm text-slate-700">
                                        {row.service_name}
                                    </span>

                                    <span className="font-semibold text-slate-900">
                                        {
                                            row.cancellation_count
                                        }
                                    </span>
                                </div>
                            ),
                        )}

                        {cancellationsByService.length ===
                            0 && (
                            <p className="text-sm text-slate-500">
                                No cancellations in this period.
                            </p>
                        )}
                    </div>
                </section>

                <section className="rounded-xl border border-slate-200 bg-white shadow-sm">
                    <div className="border-b border-slate-200 p-6">
                        <h3 className="text-lg font-semibold text-slate-900">
                            Appointment records
                        </h3>
                    </div>

                    <div className="overflow-x-auto">
                        <table className="min-w-full divide-y divide-slate-200 text-sm">
                            <thead>
                                <tr className="text-left text-xs uppercase tracking-wide text-slate-500">
                                    <th className="px-4 py-3">
                                        Date
                                    </th>
                                    <th className="px-4 py-3">
                                        Client
                                    </th>
                                    <th className="px-4 py-3">
                                        Counsellor
                                    </th>
                                    <th className="px-4 py-3">
                                        Service
                                    </th>
                                    <th className="px-4 py-3">
                                        Mode
                                    </th>
                                    <th className="px-4 py-3">
                                        Status
                                    </th>
                                </tr>
                            </thead>

                            <tbody className="divide-y divide-slate-100">
                                {appointments.data.map(
                                    (appointment) => (
                                        <tr
                                            key={
                                                appointment.uuid
                                            }
                                        >
                                            <td className="px-4 py-3">
                                                {
                                                    appointment.appointment_date
                                                }
                                                <div className="text-xs text-slate-500">
                                                    {
                                                        appointment.start_time
                                                    }
                                                    {" – "}
                                                    {
                                                        appointment.end_time
                                                    }
                                                </div>
                                            </td>

                                            <td className="px-4 py-3">
                                                {
                                                    appointment.client_name
                                                }
                                            </td>

                                            <td className="px-4 py-3">
                                                {
                                                    appointment.counsellor_name
                                                }
                                            </td>

                                            <td className="px-4 py-3">
                                                {
                                                    appointment.service_name
                                                }
                                            </td>

                                            <td className="px-4 py-3">
                                                {label(
                                                    appointment.mode,
                                                )}
                                            </td>

                                            <td className="px-4 py-3">
                                                {label(
                                                    appointment.status,
                                                )}
                                            </td>
                                        </tr>
                                    ),
                                )}
                            </tbody>
                        </table>
                    </div>

                    <div className="p-6">
                        <Pagination
                            links={appointments.links}
                        />
                    </div>
                </section>
            </div>
        </AdminLayout>
    );
}
