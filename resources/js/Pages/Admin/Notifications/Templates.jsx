import InputError from "@/Components/InputError";
import PrimaryButton from "@/Components/PrimaryButton";
import AdminLayout from "@/Layouts/AdminLayout";
import {
    Head,
    useForm,
    usePage,
} from "@inertiajs/react";
import { BellRing } from "lucide-react";

export default function Templates({
    templates,
}) {
    const { auth } = usePage().props;

    return (
        <AdminLayout
            user={auth.user}
            header="Notification Templates"
        >
            <Head title="Notification Templates" />

            <div className="py-6">
                <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div className="mb-6">
                        <h1 className="text-2xl font-semibold text-slate-900">
                            Notification templates
                        </h1>

                        <p className="mt-1 max-w-3xl text-sm leading-6 text-slate-500">
                            Manage operational notification
                            wording. Templates must not contain
                            sensitive clinical information.
                        </p>
                    </div>

                    <div className="space-y-6">
                        {templates.map((template) => (
                            <TemplateCard
                                key={template.id}
                                template={template}
                            />
                        ))}
                    </div>
                </div>
            </div>
        </AdminLayout>
    );
}

function TemplateCard({ template }) {
    const {
        data,
        setData,
        put,
        processing,
        errors,
    } = useForm({
        name: template.name ?? "",
        subject: template.subject ?? "",
        in_app_body:
            template.in_app_body ?? "",
        email_body: template.email_body ?? "",
        sms_body: template.sms_body ?? "",
    });

    const submit = (event) => {
        event.preventDefault();

        put(
            route(
                "admin.notifications.templates.update",
                template.id,
            ),
            {
                preserveScroll: true,
            },
        );
    };

    return (
        <form
            onSubmit={submit}
            className="overflow-hidden bg-white shadow-sm sm:rounded-lg"
        >
            <div className="border-b border-slate-100 px-6 py-4">
                <div className="flex items-start gap-3">
                    <div className="rounded-lg bg-indigo-50 p-2 text-indigo-600">
                        <BellRing className="h-5 w-5" />
                    </div>

                    <div>
                        <h2 className="font-semibold text-slate-900">
                            {template.name}
                        </h2>

                        <code className="mt-1 block text-xs text-slate-500">
                            {template.key}
                        </code>

                        {template.variables.length >
                            0 && (
                            <p className="mt-2 text-xs text-slate-500">
                                Variables:{" "}
                                {template.variables
                                    .map(
                                        (variable) =>
                                            `{{ ${variable} }}`,
                                    )
                                    .join(", ")}
                            </p>
                        )}
                    </div>
                </div>
            </div>

            <div className="grid gap-5 p-6 lg:grid-cols-2">
                <Field
                    label="Template name"
                    value={data.name}
                    onChange={(value) =>
                        setData("name", value)
                    }
                    error={errors.name}
                />

                <Field
                    label="Email subject"
                    value={data.subject}
                    onChange={(value) =>
                        setData("subject", value)
                    }
                    error={errors.subject}
                />

                <TextArea
                    label="In-app body"
                    value={data.in_app_body}
                    onChange={(value) =>
                        setData(
                            "in_app_body",
                            value,
                        )
                    }
                    error={errors.in_app_body}
                />

                <TextArea
                    label="Email body"
                    value={data.email_body}
                    onChange={(value) =>
                        setData(
                            "email_body",
                            value,
                        )
                    }
                    error={errors.email_body}
                />

                <div className="lg:col-span-2">
                    <TextArea
                        label="SMS body"
                        value={data.sms_body}
                        onChange={(value) =>
                            setData(
                                "sms_body",
                                value,
                            )
                        }
                        error={errors.sms_body}
                        rows={3}
                    />
                </div>
            </div>

            <div className="border-t bg-slate-50 px-6 py-4">
                <PrimaryButton
                    type="submit"
                    disabled={processing}
                >
                    Save template
                </PrimaryButton>
            </div>
        </form>
    );
}

function Field({
    label,
    value,
    onChange,
    error,
}) {
    return (
        <div>
            <label className="block text-sm font-medium text-slate-700">
                {label}
            </label>

            <input
                type="text"
                value={value}
                onChange={(event) =>
                    onChange(event.target.value)
                }
                className="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
            />

            <InputError
                message={error}
                className="mt-2"
            />
        </div>
    );
}

function TextArea({
    label,
    value,
    onChange,
    error,
    rows = 5,
}) {
    return (
        <div>
            <label className="block text-sm font-medium text-slate-700">
                {label}
            </label>

            <textarea
                rows={rows}
                value={value}
                onChange={(event) =>
                    onChange(event.target.value)
                }
                className="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
            />

            <InputError
                message={error}
                className="mt-2"
            />
        </div>
    );
}