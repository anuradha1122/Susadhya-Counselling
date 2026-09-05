import Pagination from "@/Components/Pagination";
import AdminLayout from "@/Layouts/AdminLayout";
import { Head, useForm } from "@inertiajs/react";

const label = (value) =>
    String(value ?? "")
        .replaceAll("_", " ")
        .replace(/\b\w/g, (letter) => letter.toUpperCase());

function SnippetCard({ snippet, statuses }) {
    const form = useForm({
        title: snippet.title,
        body: snippet.body,
        placement: snippet.placement,
        status: snippet.status,
    });

    return (
        <form
            onSubmit={(event) => {
                event.preventDefault();

                form.patch(
                    route(
                        "admin.content-snippets.update",
                        snippet.uuid,
                    ),
                    {
                        preserveScroll: true,
                    },
                );
            }}
            className="rounded-xl border border-slate-200 bg-white p-6 shadow-sm"
        >
            <p className="text-xs font-medium text-indigo-600">
                {snippet.key}
            </p>

            <div className="mt-4 grid gap-4 md:grid-cols-2">
                <input
                    value={form.data.title}
                    onChange={(event) =>
                        form.setData(
                            "title",
                            event.target.value,
                        )
                    }
                    className="rounded-lg border-slate-300"
                />

                <input
                    value={form.data.placement}
                    onChange={(event) =>
                        form.setData(
                            "placement",
                            event.target.value,
                        )
                    }
                    className="rounded-lg border-slate-300"
                />

                <textarea
                    rows="5"
                    value={form.data.body}
                    onChange={(event) =>
                        form.setData(
                            "body",
                            event.target.value,
                        )
                    }
                    className="rounded-lg border-slate-300 md:col-span-2"
                />

                <select
                    value={form.data.status}
                    onChange={(event) =>
                        form.setData(
                            "status",
                            event.target.value,
                        )
                    }
                    className="rounded-lg border-slate-300"
                >
                    {statuses.map((status) => (
                        <option key={status} value={status}>
                            {label(status)}
                        </option>
                    ))}
                </select>
            </div>

            <div className="mt-4 flex justify-end">
                <button
                    type="submit"
                    disabled={form.processing}
                    className="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white"
                >
                    Save snippet
                </button>
            </div>
        </form>
    );
}

export default function Index({ snippets, statuses }) {
    const createForm = useForm({
        key: "",
        title: "",
        body: "",
        placement: "admin_operations",
        status: "draft",
    });

    return (
        <AdminLayout title="Content Snippets">
            <Head title="Content Snippets" />

            <div className="mx-auto max-w-7xl space-y-6">
                <section className="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h2 className="text-xl font-semibold text-slate-900">
                        Operational content snippets
                    </h2>

                    <p className="mt-2 text-sm leading-6 text-slate-600">
                        These are small reusable operational notices.
                        Full public website CMS remains M19.
                    </p>
                </section>

                <section className="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h3 className="text-lg font-semibold">
                        Create snippet
                    </h3>

                    <form
                        onSubmit={(event) => {
                            event.preventDefault();

                            createForm.post(
                                route(
                                    "admin.content-snippets.store",
                                ),
                                {
                                    preserveScroll: true,
                                    onSuccess: () =>
                                        createForm.reset(),
                                },
                            );
                        }}
                        className="mt-5 grid gap-4 md:grid-cols-2"
                    >
                        <input
                            value={createForm.data.key}
                            onChange={(event) =>
                                createForm.setData(
                                    "key",
                                    event.target.value,
                                )
                            }
                            placeholder="operations.example"
                            className="rounded-lg border-slate-300"
                        />

                        <input
                            value={createForm.data.title}
                            onChange={(event) =>
                                createForm.setData(
                                    "title",
                                    event.target.value,
                                )
                            }
                            placeholder="Title"
                            className="rounded-lg border-slate-300"
                        />

                        <input
                            value={createForm.data.placement}
                            onChange={(event) =>
                                createForm.setData(
                                    "placement",
                                    event.target.value,
                                )
                            }
                            placeholder="Placement"
                            className="rounded-lg border-slate-300"
                        />

                        <select
                            value={createForm.data.status}
                            onChange={(event) =>
                                createForm.setData(
                                    "status",
                                    event.target.value,
                                )
                            }
                            className="rounded-lg border-slate-300"
                        >
                            {statuses.map((status) => (
                                <option key={status} value={status}>
                                    {label(status)}
                                </option>
                            ))}
                        </select>

                        <textarea
                            rows="5"
                            value={createForm.data.body}
                            onChange={(event) =>
                                createForm.setData(
                                    "body",
                                    event.target.value,
                                )
                            }
                            placeholder="Snippet content"
                            className="rounded-lg border-slate-300 md:col-span-2"
                        />

                        <div className="md:col-span-2 flex justify-end">
                            <button
                                type="submit"
                                className="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white"
                            >
                                Create snippet
                            </button>
                        </div>
                    </form>
                </section>

                <section className="space-y-4">
                    {snippets.data.map((snippet) => (
                        <SnippetCard
                            key={snippet.uuid}
                            snippet={snippet}
                            statuses={statuses}
                        />
                    ))}

                    <Pagination links={snippets.links} />
                </section>
            </div>
        </AdminLayout>
    );
}