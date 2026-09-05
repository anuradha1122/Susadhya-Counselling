import InputError from "@/Components/InputError";
import Pagination from "@/Components/Pagination";
import ClientLayout from "@/Layouts/ClientLayout";
import { Head, router, useForm } from "@inertiajs/react";

function humanize(value) {
    return String(value ?? "")
        .replaceAll("_", " ")
        .replace(/\b\w/g, (character) => character.toUpperCase());
}

function formatDate(value) {
    if (!value) return "Not provided";

    return new Intl.DateTimeFormat("en-LK", {
        dateStyle: "medium",
        timeStyle: "short",
    }).format(new Date(value));
}

export default function Index({ privacyRequests, types }) {
    const form = useForm({
        type: "access",
        request_details: "",
        scope: [],
    });

    const submit = (event) => {
        event.preventDefault();

        form.post(route("client.privacy-requests.store"), {
            preserveScroll: true,
            onSuccess: () => {
                form.reset("request_details");
            },
        });
    };

    return (
        <ClientLayout
            header={
                <div>
                    <h2 className="text-xl font-semibold leading-tight text-gray-800">
                        Privacy Requests
                    </h2>
                    <p className="mt-1 text-sm text-gray-500">
                        Request access, export, correction or reviewed deletion
                        of your personal information.
                    </p>
                </div>
            }
        >
            <Head title="Privacy Requests" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                    <div className="rounded-lg border border-indigo-200 bg-indigo-50 p-5 text-sm leading-6 text-indigo-800">
                        Deletion requests are reviewed before action. Clinical,
                        financial or other records may need to be retained where
                        legal, professional or safety obligations apply.
                    </div>

                    <form
                        onSubmit={submit}
                        className="overflow-hidden bg-white p-6 shadow-sm sm:rounded-lg"
                    >
                        <h3 className="text-lg font-semibold text-gray-900">
                            Submit privacy request
                        </h3>

                        <div className="mt-5">
                            <label className="text-sm font-medium text-gray-700">
                                Request type
                            </label>

                            <select
                                value={form.data.type}
                                onChange={(event) =>
                                    form.setData("type", event.target.value)
                                }
                                className="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                            >
                                {types.map((type) => (
                                    <option key={type} value={type}>
                                        {humanize(type)}
                                    </option>
                                ))}
                            </select>

                            <InputError
                                message={form.errors.type}
                                className="mt-2"
                            />
                        </div>

                        <div className="mt-5">
                            <label className="text-sm font-medium text-gray-700">
                                Request details
                            </label>

                            <textarea
                                rows="6"
                                value={form.data.request_details}
                                onChange={(event) =>
                                    form.setData(
                                        "request_details",
                                        event.target.value,
                                    )
                                }
                                placeholder="Describe what you are requesting."
                                className="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                            />

                            <InputError
                                message={form.errors.request_details}
                                className="mt-2"
                            />
                        </div>

                        <button
                            disabled={form.processing}
                            className="mt-5 rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white disabled:opacity-50"
                        >
                            Submit request
                        </button>
                    </form>

                    <div className="space-y-4">
                        {privacyRequests.data.map((item) => (
                            <div
                                key={item.uuid}
                                className="overflow-hidden bg-white p-6 shadow-sm sm:rounded-lg"
                            >
                                <div className="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                                    <div>
                                        <div className="flex flex-wrap gap-2">
                                            <span className="rounded-full bg-indigo-50 px-3 py-1 text-xs font-medium text-indigo-700">
                                                {humanize(item.type)}
                                            </span>

                                            <span className="rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700">
                                                {humanize(item.status)}
                                            </span>
                                        </div>

                                        <p className="mt-4 whitespace-pre-wrap text-sm leading-6 text-gray-700">
                                            {item.request_details}
                                        </p>

                                        <p className="mt-3 text-xs text-gray-500">
                                            Submitted:{" "}
                                            {formatDate(item.submitted_at)}
                                        </p>
                                    </div>

                                    <div className="flex flex-wrap gap-2">
                                        {item.export_available &&
                                            ["export_ready", "completed"].includes(
                                                item.status,
                                            ) && (
                                                <a
                                                    href={route(
                                                        "client.privacy-requests.download",
                                                        item.uuid,
                                                    )}
                                                    className="rounded-md bg-emerald-600 px-3 py-2 text-sm font-medium text-white"
                                                >
                                                    Download export
                                                </a>
                                            )}

                                        {item.status === "submitted" && (
                                            <button
                                                type="button"
                                                onClick={() => {
                                                    if (
                                                        window.confirm(
                                                            "Cancel this privacy request?",
                                                        )
                                                    ) {
                                                        router.patch(
                                                            route(
                                                                "client.privacy-requests.cancel",
                                                                item.uuid,
                                                            ),
                                                        );
                                                    }
                                                }}
                                                className="rounded-md border border-rose-300 px-3 py-2 text-sm font-medium text-rose-700"
                                            >
                                                Cancel request
                                            </button>
                                        )}
                                    </div>
                                </div>
                            </div>
                        ))}

                        {privacyRequests.data.length === 0 && (
                            <div className="overflow-hidden bg-white p-8 text-center text-sm text-gray-500 shadow-sm sm:rounded-lg">
                                You have not submitted any privacy requests.
                            </div>
                        )}
                    </div>

                    <Pagination links={privacyRequests.links} />
                </div>
            </div>
        </ClientLayout>
    );
}
