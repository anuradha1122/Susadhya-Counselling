function formatNumber(value) {
    return new Intl.NumberFormat().format(value ?? 0);
}

function MetricCard({ label, value, description }) {
    return (
        <div className="rounded-lg border border-gray-100 bg-white p-5 shadow-sm">
            <p className="text-sm font-medium text-gray-500">{label}</p>

            <p className="mt-2 text-3xl font-bold text-gray-900">
                {formatNumber(value)}
            </p>

            {description && (
                <p className="mt-2 text-xs leading-5 text-gray-500">
                    {description}
                </p>
            )}
        </div>
    );
}

export default function AppointmentMetricGrid({
    metrics,
    title = "Appointment Summary",
    description = "Live appointment counts from the scheduling module.",
}) {
    const safeMetrics = metrics ?? {};

    const primaryMetrics = [
        {
            label: "Total",
            value: safeMetrics.total,
            description: "All appointment records in this view.",
        },
        {
            label: "Pending",
            value: safeMetrics.pending,
            description: "Waiting for counsellor or admin confirmation.",
        },
        {
            label: "Confirmed",
            value: safeMetrics.confirmed,
            description: "Approved appointments with time and access details.",
        },
        {
            label: "Today",
            value: safeMetrics.today,
            description: "Appointments scheduled for today.",
        },
    ];

    const secondaryMetrics = [
        {
            label: "Upcoming",
            value: safeMetrics.upcoming,
            description: "Appointments from today onward.",
        },
        {
            label: "Next 7 Days",
            value: safeMetrics.upcoming_next_7_days,
            description: "Appointments due within the next week.",
        },
        {
            label: "Completed",
            value: safeMetrics.completed,
            description: "Sessions marked as completed.",
        },
        {
            label: "Cancelled",
            value: safeMetrics.cancelled,
            description: "Appointments cancelled by client or admin.",
        },
        {
            label: "No Show",
            value: safeMetrics.no_show,
            description: "Confirmed sessions where the client did not attend.",
        },
        {
            label: "Rescheduled",
            value: safeMetrics.rescheduled,
            description: "Old appointment records replaced by new requests.",
        },
        {
            label: "Past",
            value: safeMetrics.past,
            description: "Appointments before today.",
        },
        {
            label: "Due Reminders",
            value: safeMetrics.due_reminders,
            description: "Confirmed appointments waiting for reminder sending.",
        },
    ];

    return (
        <div className="space-y-5">
            <div>
                <h3 className="text-lg font-semibold text-gray-900">{title}</h3>

                <p className="mt-1 text-sm text-gray-500">{description}</p>
            </div>

            <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                {primaryMetrics.map((metric) => (
                    <MetricCard
                        key={metric.label}
                        label={metric.label}
                        value={metric.value}
                        description={metric.description}
                    />
                ))}
            </div>

            <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                {secondaryMetrics.map((metric) => (
                    <MetricCard
                        key={metric.label}
                        label={metric.label}
                        value={metric.value}
                        description={metric.description}
                    />
                ))}
            </div>
        </div>
    );
}
