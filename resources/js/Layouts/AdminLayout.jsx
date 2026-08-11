import { adminNavigation } from '@/Config/navigation';
import AppLayout from '@/Layouts/AppLayout';
import { usePage } from '@inertiajs/react';
import { CircleCheck, CircleX, X } from 'lucide-react';
import { useEffect, useState } from 'react';

function FlashMessage({ type, message, onClose }) {
    if (!message) {
        return null;
    }

    const success = type === 'success';

    return (
        <div
            className={`mb-6 flex items-start justify-between gap-4 rounded-xl border px-4 py-3 shadow-sm ${
                success
                    ? 'border-emerald-200 bg-emerald-50 text-emerald-800'
                    : 'border-rose-200 bg-rose-50 text-rose-800'
            }`}
            role="alert"
        >
            <div className="flex items-start gap-3">
                {success ? (
                    <CircleCheck className="mt-0.5 h-5 w-5 shrink-0" />
                ) : (
                    <CircleX className="mt-0.5 h-5 w-5 shrink-0" />
                )}

                <p className="text-sm font-medium">{message}</p>
            </div>

            <button
                type="button"
                onClick={onClose}
                className="rounded-md p-1 opacity-70 hover:bg-black/5 hover:opacity-100"
                aria-label="Close notification"
            >
                <X className="h-4 w-4" />
            </button>
        </div>
    );
}

export default function AdminLayout({ title, children }) {
    const { flash = {} } = usePage().props;

    const [successMessage, setSuccessMessage] = useState(
        flash.success ?? null,
    );

    const [errorMessage, setErrorMessage] = useState(
        flash.error ?? null,
    );

    useEffect(() => {
        setSuccessMessage(flash.success ?? null);
        setErrorMessage(flash.error ?? null);

        if (!flash.success && !flash.error) {
            return undefined;
        }

        const timeout = window.setTimeout(() => {
            setSuccessMessage(null);
            setErrorMessage(null);
        }, 5000);

        return () => window.clearTimeout(timeout);
    }, [flash.success, flash.error]);

    return (
        <AppLayout title={title} navigation={adminNavigation}>
            <FlashMessage
                type="success"
                message={successMessage}
                onClose={() => setSuccessMessage(null)}
            />

            <FlashMessage
                type="error"
                message={errorMessage}
                onClose={() => setErrorMessage(null)}
            />

            {children}
        </AppLayout>
    );
}
