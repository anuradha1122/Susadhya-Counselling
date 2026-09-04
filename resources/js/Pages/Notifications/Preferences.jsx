import PrimaryButton from "@/Components/PrimaryButton";
import SecondaryButton from "@/Components/SecondaryButton";
import NotificationLayout from "@/Layouts/NotificationLayout";
import {
    Head,
    Link,
    useForm,
    usePage,
} from "@inertiajs/react";
import {
    Bell,
    Mail,
    MessageSquareText,
    ShieldCheck,
} from "lucide-react";

export default function Preferences({
    preferences,
    emailAvailable,
    smsAvailable,
}) {
    const { auth } = usePage().props;

    const { data, setData, put, processing } =
        useForm({
            preferences,
        });

    const updatePreference = (
        index,
        field,
        value,
    ) => {
        setData(
            "preferences",
            data.preferences.map(
                (preference, preferenceIndex) =>
                    preferenceIndex === index
                        ? {
                              ...preference,
                              [field]: value,
                          }
                        : preference,
            ),
        );
    };

    const submit = (event) => {
        event.preventDefault();

        put(
            route(
                "notifications.preferences.update",
            ),
            {
                preserveScroll: true,
            },
        );
    };

    return (
        <NotificationLayout
            user={auth.user}
            header="Notification Preferences"
        >
            <Head title="Notification Preferences" />

            <div className="py-6">
                <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div className="mb-6">
                        <Link
                            href={route(
                                "notifications.index",
                            )}
                            className="text-sm font-medium text-indigo-600 hover:text-indigo-700"
                        >
                            ← Back to notifications
                        </Link>

                        <h1 className="mt-3 text-2xl font-semibold text-slate-900">
                            Notification preferences
                        </h1>

                        <p className="mt-1 max-w-3xl text-sm leading-6 text-slate-500">
                            Choose how Susadhya should
                            deliver account, appointment and
                            payment updates. Critical
                            transactional events always retain
                            an in-app record.
                        </p>
                    </div>

                    <form onSubmit={submit}>
                        <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                            <div className="hidden grid-cols-[minmax(0,1fr)_120px_120px_120px] gap-4 border-b bg-slate-50 px-6 py-3 text-xs font-semibold uppercase tracking-wide text-slate-500 md:grid">
                                <span>Event</span>

                                <span className="text-center">
                                    In-app
                                </span>

                                <span className="text-center">
                                    Email
                                </span>

                                <span className="text-center">
                                    SMS
                                </span>
                            </div>

                            <div className="divide-y divide-slate-100">
                                {data.preferences.map(
                                    (
                                        preference,
                                        index,
                                    ) => (
                                        <div
                                            key={
                                                preference.event_type
                                            }
                                            className="grid gap-4 px-6 py-5 md:grid-cols-[minmax(0,1fr)_120px_120px_120px] md:items-center"
                                        >
                                            <div>
                                                <div className="flex items-center gap-2">
                                                    <p className="font-medium text-slate-900">
                                                        {
                                                            preference.label
                                                        }
                                                    </p>

                                                    {preference.is_critical && (
                                                        <span className="inline-flex items-center gap-1 rounded-full bg-indigo-50 px-2 py-1 text-[11px] font-medium text-indigo-700">
                                                            <ShieldCheck className="h-3 w-3" />
                                                            Critical
                                                        </span>
                                                    )}
                                                </div>

                                                <p className="mt-1 text-xs text-slate-500">
                                                    {
                                                        preference.event_type
                                                    }
                                                </p>
                                            </div>

                                            <ChannelToggle
                                                icon={
                                                    Bell
                                                }
                                                label="In-app"
                                                checked={
                                                    preference.in_app_enabled
                                                }
                                                disabled={
                                                    preference.is_critical
                                                }
                                                onChange={(
                                                    checked,
                                                ) =>
                                                    updatePreference(
                                                        index,
                                                        "in_app_enabled",
                                                        checked,
                                                    )
                                                }
                                            />

                                            <ChannelToggle
                                                icon={
                                                    Mail
                                                }
                                                label="Email"
                                                checked={
                                                    preference.email_enabled
                                                }
                                                disabled={
                                                    !emailAvailable
                                                }
                                                onChange={(
                                                    checked,
                                                ) =>
                                                    updatePreference(
                                                        index,
                                                        "email_enabled",
                                                        checked,
                                                    )
                                                }
                                            />

                                            <ChannelToggle
                                                icon={
                                                    MessageSquareText
                                                }
                                                label="SMS"
                                                checked={
                                                    preference.sms_enabled
                                                }
                                                disabled={
                                                    !smsAvailable
                                                }
                                                onChange={(
                                                    checked,
                                                ) =>
                                                    updatePreference(
                                                        index,
                                                        "sms_enabled",
                                                        checked,
                                                    )
                                                }
                                            />
                                        </div>
                                    ),
                                )}
                            </div>
                        </div>

                        {!smsAvailable && (
                            <div className="mt-4 rounded-lg border border-slate-200 bg-slate-50 p-4 text-sm text-slate-600">
                                SMS delivery is prepared but
                                no production SMS provider is
                                currently enabled.
                            </div>
                        )}

                        <div className="mt-6 flex gap-3">
                            <PrimaryButton
                                type="submit"
                                disabled={processing}
                            >
                                Save preferences
                            </PrimaryButton>

                            <Link
                                href={route(
                                    "notifications.index",
                                )}
                            >
                                <SecondaryButton type="button">
                                    Cancel
                                </SecondaryButton>
                            </Link>
                        </div>
                    </form>
                </div>
            </div>
        </NotificationLayout>
    );
}

function ChannelToggle({
    icon: Icon,
    label,
    checked,
    disabled,
    onChange,
}) {
    return (
        <label className="flex items-center justify-between gap-3 md:justify-center">
            <span className="flex items-center gap-2 text-sm text-slate-600 md:hidden">
                <Icon className="h-4 w-4" />
                {label}
            </span>

            <input
                type="checkbox"
                checked={Boolean(checked)}
                disabled={disabled}
                onChange={(event) =>
                    onChange(event.target.checked)
                }
                className="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 disabled:cursor-not-allowed disabled:opacity-50"
            />
        </label>
    );
}