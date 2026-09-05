import ComplianceLayout from "@/Layouts/ComplianceLayout";
import { Head, Link } from "@inertiajs/react";
import {
    ArchiveRestore,
    FileClock,
    ShieldCheck,
    Siren,
} from "lucide-react";

function StatCard({ label, value, icon: Icon, tone }) {
    const tones = {
        indigo: "bg-indigo-50 text-indigo-700",
        amber: "bg-amber-50 text-amber-700",
        rose: "bg-rose-50 text-rose-700",
        emerald: "bg-emerald-50 text-emerald-700",
    };

    return (
        <div className="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <div className="flex items-start justify-between gap-4">
                <div>
                    <p className="text-sm font-medium text-slate-500">{label}</p>
                    <p className="mt-2 text-3xl font-bold text-slate-900">
                        {value}
                    </p>
                </div>

                <div className={`rounded-xl p-3 ${tones[tone]}`}>
                    <Icon className="h-5 w-5" />
                </div>
            </div>
        </div>
    );
}

function formatDate(value) {
    if (!value) {
        return "Not provided";
    }

    return new Intl.DateTimeFormat("en-LK", {
        dateStyle: "medium",
        timeStyle: "short",
    }).format(new Date(value));
}

export default function Dashboard({ stats, recentAuditEvents }) {
    return (
        <ComplianceLayout title="Privacy & Compliance">
            <Head title="Privacy & Compliance" />

            <div className="mx-auto max-w-7xl space-y-6">
                <div className="rounded-xl border border-indigo-200 bg-indigo-50 p-6">
                    <h2 className="text-xl font-semibold text-indigo-950">
                        Audit, Privacy & Compliance
                    </h2>

                    <p className="mt-2 max-w-3xl text-sm leading-6 text-indigo-800">
                        Review platform audit activity, privacy requests, consent
                        history, retention controls and data-breach records.
                        Clinical narratives and payment secrets are deliberately
                        excluded from this general compliance workspace.
                    </p>
                </div>

                <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    <StatCard
                        label="Audit events"
                        value={stats.auditEvents}
                        icon={FileClock}
                        tone="indigo"
                    />

                    <StatCard
                        label="Pending privacy requests"
                        value={stats.pendingPrivacyRequests}
                        icon={ShieldCheck}
                        tone="amber"
                    />

                    <StatCard
                        label="Open breaches"
                        value={stats.openBreaches}
                        icon={Siren}
                        tone="rose"
                    />

                    <StatCard
                        label="Enabled retention policies"
                        value={stats.enabledRetentionPolicies}
                        icon={ArchiveRestore}
                        tone="emerald"
                    />
                </div>

                <div className="rounded-xl border border-slate-200 bg-white shadow-sm">
                    <div className="flex items-center justify-between border-b border-slate-200 px-6 py-4">
                        <div>
                            <h3 className="font-semibold text-slate-900">
                                Recent audit activity
                            </h3>
                            <p className="mt-1 text-sm text-slate-500">
                                Latest platform-wide compliance events.
                            </p>
                        </div>

                        <Link
                            href={route("compliance.audit-events.index")}
                            className="text-sm font-medium text-indigo-600 hover:text-indigo-800"
                        >
                            View all
                        </Link>
                    </div>

                    <div className="divide-y divide-slate-100">
                        {recentAuditEvents.length === 0 ? (
                            <p className="p-6 text-sm text-slate-500">
                                No audit events recorded yet.
                            </p>
                        ) : (
                            recentAuditEvents.map((event) => (
                                <div
                                    key={event.uuid}
                                    className="flex flex-col gap-2 px-6 py-4 sm:flex-row sm:items-center sm:justify-between"
                                >
                                    <div>
                                        <p className="font-medium text-slate-900">
                                            {event.event}
                                        </p>
                                        <p className="mt-1 text-sm text-slate-500">
                                            {event.category} · {event.action} ·{" "}
                                            {event.actor ?? "System"}
                                        </p>
                                    </div>

                                    <p className="text-xs text-slate-500">
                                        {formatDate(event.occurred_at)}
                                    </p>
                                </div>
                            ))
                        )}
                    </div>
                </div>
            </div>
        </ComplianceLayout>
    );
}
