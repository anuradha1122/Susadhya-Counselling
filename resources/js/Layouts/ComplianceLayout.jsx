import NotificationBell from "@/Components/Notifications/NotificationBell";
import { complianceNavigation } from "@/Config/navigation";
import AppLayout from "@/Layouts/AppLayout";
import { usePage } from "@inertiajs/react";
import { useEffect, useState } from "react";

export default function ComplianceLayout({ title, children }) {
    const { flash } = usePage().props;
    const [success, setSuccess] = useState(flash?.success ?? null);
    const [error, setError] = useState(flash?.error ?? null);

    useEffect(() => {
        setSuccess(flash?.success ?? null);
        setError(flash?.error ?? null);
    }, [flash?.success, flash?.error]);

    useEffect(() => {
        if (!success && !error) {
            return undefined;
        }

        const timer = window.setTimeout(() => {
            setSuccess(null);
            setError(null);
        }, 5000);

        return () => window.clearTimeout(timer);
    }, [success, error]);

    return (
        <AppLayout title={title} navigation={complianceNavigation}>
            <div className="mb-4 flex justify-end">
                <NotificationBell />
            </div>

            {success && (
                <div className="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                    {success}
                </div>
            )}

            {error && (
                <div className="mb-4 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                    {error}
                </div>
            )}

            {children}
        </AppLayout>
    );
}
