import InputError from "@/Components/InputError";
import ClientLayout from "@/Layouts/ClientLayout";
import {
    Head,
    Link,
    useForm,
} from "@inertiajs/react";
import { Send } from "lucide-react";

function humanize(value) {
    return String(value ?? "")
        .replaceAll("_", " ")
        .replace(/\b\w/g, (char) =>
            char.toUpperCase(),
        );
}

export default function Create({
    categories,
}) {
    const {
        data,
        setData,
        post,
        processing,
        errors,
    } = useForm({
        category: "general",
        subject: "",
        description: "",
    });

    const submit = (event) => {
        event.preventDefault();

        post(
            route(
                "client.support.store",
            ),
        );
    };

    return (
        <ClientLayout>
            <Head title="New Support Request" />

            <div className="mx-auto max-w-3xl">
                <div className="mb-6">
                    <Link
                        href={route(
                            "client.support.index",
                        )}
                        className="text-sm font-medium text-indigo-600 hover:text-indigo-700"
                    >
                        ← Back to Support
                    </Link>

                    <h1 className="mt-3 text-2xl font-bold text-slate-900">
                        New Support Request
                    </h1>
                </div>

                <div className="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div className="mb-6 rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm leading-6 text-amber-900">
                        Do not use support
                        requests for counselling
                        emergencies or detailed
                        clinical notes.
                    </div>

                    <form
                        onSubmit={submit}
                        className="space-y-6"
                    >
                        <div>
                            <label className="text-sm font-medium text-slate-700">
                                Category
                            </label>

                            <select
                                value={
                                    data.category
                                }
                                onChange={(
                                    event,
                                ) =>
                                    setData(
                                        "category",
                                        event
                                            .target
                                            .value,
                                    )
                                }
                                className="mt-1 block w-full rounded-lg border-slate-300"
                            >
                                {categories.map(
                                    (
                                        category,
                                    ) => (
                                        <option
                                            key={
                                                category
                                            }
                                            value={
                                                category
                                            }
                                        >
                                            {humanize(
                                                category,
                                            )}
                                        </option>
                                    ),
                                )}
                            </select>

                            <InputError
                                className="mt-2"
                                message={
                                    errors.category
                                }
                            />
                        </div>

                        <div>
                            <label className="text-sm font-medium text-slate-700">
                                Subject
                            </label>

                            <input
                                value={
                                    data.subject
                                }
                                onChange={(
                                    event,
                                ) =>
                                    setData(
                                        "subject",
                                        event
                                            .target
                                            .value,
                                    )
                                }
                                className="mt-1 block w-full rounded-lg border-slate-300"
                            />

                            <InputError
                                className="mt-2"
                                message={
                                    errors.subject
                                }
                            />
                        </div>

                        <div>
                            <label className="text-sm font-medium text-slate-700">
                                Describe the issue
                            </label>

                            <textarea
                                rows="8"
                                value={
                                    data.description
                                }
                                onChange={(
                                    event,
                                ) =>
                                    setData(
                                        "description",
                                        event
                                            .target
                                            .value,
                                    )
                                }
                                className="mt-1 block w-full rounded-lg border-slate-300"
                            />

                            <InputError
                                className="mt-2"
                                message={
                                    errors.description
                                }
                            />
                        </div>

                        <button
                            disabled={
                                processing
                            }
                            className="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700 disabled:opacity-60"
                        >
                            <Send className="h-4 w-4" />

                            {processing
                                ? "Submitting..."
                                : "Submit Request"}
                        </button>
                    </form>
                </div>
            </div>
        </ClientLayout>
    );
}
