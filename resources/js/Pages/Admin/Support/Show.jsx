import InputError from "@/Components/InputError";
import AdminLayout from "@/Layouts/AdminLayout";
import {
    Head,
    Link,
    useForm,
} from "@inertiajs/react";

function humanize(value) {
    return String(value ?? "")
        .replaceAll("_", " ")
        .replace(/\b\w/g, (char) =>
            char.toUpperCase(),
        );
}

export default function Show({
    ticket,
    owners,
    statuses,
    priorities,
}) {
    const management =
        useForm({
            status: ticket.status,
            priority:
                ticket.priority,
            owner_id:
                ticket.owner_id ??
                "",
            resolution_note: "",
        });

    const reply =
        useForm({
            body: "",
            is_internal: false,
        });

    const updateTicket = (
        event,
    ) => {
        event.preventDefault();

        management.patch(
            route(
                "admin.support.update",
                ticket.uuid,
            ),
            {
                preserveScroll:
                    true,
            },
        );
    };

    const submitReply = (
        event,
    ) => {
        event.preventDefault();

        reply.post(
            route(
                "admin.support.replies.store",
                ticket.uuid,
            ),
            {
                preserveScroll:
                    true,

                onSuccess: () =>
                    reply.reset(
                        "body",
                    ),
            },
        );
    };

    return (
        <AdminLayout>
            <Head
                title={
                    ticket.subject
                }
            />

            <div className="mx-auto grid max-w-7xl gap-6 lg:grid-cols-[1fr_380px]">
                <div className="space-y-6">
                    <div>
                        <Link
                            href={route(
                                "admin.support.index",
                            )}
                            className="text-sm font-medium text-indigo-600"
                        >
                            ← Back to
                            Support
                        </Link>

                        <h1 className="mt-3 text-2xl font-bold text-slate-900">
                            {
                                ticket.subject
                            }
                        </h1>

                        <p className="mt-1 text-sm text-slate-500">
                            {humanize(
                                ticket.category,
                            )}
                            {" · "}
                            {
                                ticket.requester
                                    .name
                            }
                        </p>
                    </div>

                    <div className="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                        <p className="whitespace-pre-wrap text-sm leading-7 text-slate-700">
                            {
                                ticket.description
                            }
                        </p>
                    </div>

                    <div className="rounded-xl border border-slate-200 bg-white shadow-sm">
                        <div className="border-b px-6 py-4">
                            <h2 className="font-semibold text-slate-900">
                                Conversation
                                & Internal
                                Notes
                            </h2>
                        </div>

                        <div className="space-y-4 p-6">
                            {ticket.replies.map(
                                (item) => (
                                    <div
                                        key={
                                            item.uuid
                                        }
                                        className={`rounded-xl p-4 ${
                                            item.is_internal
                                                ? "border border-amber-200 bg-amber-50"
                                                : "bg-slate-50"
                                        }`}
                                    >
                                        <div className="flex justify-between gap-4">
                                            <span className="text-xs font-semibold text-slate-600">
                                                {
                                                    item.author
                                                }
                                            </span>

                                            {item.is_internal && (
                                                <span className="text-xs font-semibold text-amber-700">
                                                    Internal
                                                    only
                                                </span>
                                            )}
                                        </div>

                                        <p className="mt-2 whitespace-pre-wrap text-sm leading-6 text-slate-700">
                                            {
                                                item.body
                                            }
                                        </p>
                                    </div>
                                ),
                            )}
                        </div>
                    </div>

                    <form
                        onSubmit={
                            submitReply
                        }
                        className="rounded-xl border border-slate-200 bg-white p-6 shadow-sm"
                    >
                        <h2 className="font-semibold text-slate-900">
                            Add Reply /
                            Note
                        </h2>

                        <textarea
                            rows="5"
                            value={
                                reply.data
                                    .body
                            }
                            onChange={(
                                event,
                            ) =>
                                reply.setData(
                                    "body",
                                    event
                                        .target
                                        .value,
                                )
                            }
                            className="mt-4 block w-full rounded-lg border-slate-300"
                        />

                        <InputError
                            className="mt-2"
                            message={
                                reply.errors
                                    .body
                            }
                        />

                        <label className="mt-4 flex items-center gap-3 text-sm text-slate-700">
                            <input
                                type="checkbox"
                                checked={
                                    reply.data
                                        .is_internal
                                }
                                onChange={(
                                    event,
                                ) =>
                                    reply.setData(
                                        "is_internal",
                                        event
                                            .target
                                            .checked,
                                    )
                                }
                                className="rounded border-slate-300 text-indigo-600"
                            />

                            Internal note
                            only. Never
                            visible to
                            client.
                        </label>

                        <button
                            disabled={
                                reply.processing
                            }
                            className="mt-4 rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white"
                        >
                            Save
                        </button>
                    </form>
                </div>

                <aside className="space-y-6">
                    <form
                        onSubmit={
                            updateTicket
                        }
                        className="rounded-xl border border-slate-200 bg-white p-6 shadow-sm"
                    >
                        <h2 className="font-semibold text-slate-900">
                            Manage Request
                        </h2>

                        <div className="mt-5 space-y-4">
                            <div>
                                <label className="text-sm font-medium text-slate-700">
                                    Owner
                                </label>

                                <select
                                    value={
                                        management
                                            .data
                                            .owner_id
                                    }
                                    onChange={(
                                        event,
                                    ) =>
                                        management.setData(
                                            "owner_id",
                                            event
                                                .target
                                                .value,
                                        )
                                    }
                                    className="mt-1 block w-full rounded-lg border-slate-300"
                                >
                                    <option value="">
                                        Unassigned
                                    </option>

                                    {owners.map(
                                        (
                                            owner,
                                        ) => (
                                            <option
                                                key={
                                                    owner.id
                                                }
                                                value={
                                                    owner.id
                                                }
                                            >
                                                {
                                                    owner.name
                                                }
                                            </option>
                                        ),
                                    )}
                                </select>

                                <InputError
                                    message={
                                        management
                                            .errors
                                            .owner_id
                                    }
                                />
                            </div>

                            <div>
                                <label className="text-sm font-medium text-slate-700">
                                    Priority
                                </label>

                                <select
                                    value={
                                        management
                                            .data
                                            .priority
                                    }
                                    onChange={(
                                        event,
                                    ) =>
                                        management.setData(
                                            "priority",
                                            event
                                                .target
                                                .value,
                                        )
                                    }
                                    className="mt-1 block w-full rounded-lg border-slate-300"
                                >
                                    {priorities.map(
                                        (
                                            priority,
                                        ) => (
                                            <option
                                                key={
                                                    priority
                                                }
                                                value={
                                                    priority
                                                }
                                            >
                                                {humanize(
                                                    priority,
                                                )}
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
                                    value={
                                        management
                                            .data
                                            .status
                                    }
                                    onChange={(
                                        event,
                                    ) =>
                                        management.setData(
                                            "status",
                                            event
                                                .target
                                                .value,
                                        )
                                    }
                                    className="mt-1 block w-full rounded-lg border-slate-300"
                                >
                                    {statuses.map(
                                        (
                                            status,
                                        ) => (
                                            <option
                                                key={
                                                    status
                                                }
                                                value={
                                                    status
                                                }
                                            >
                                                {humanize(
                                                    status,
                                                )}
                                            </option>
                                        ),
                                    )}
                                </select>

                                <InputError
                                    message={
                                        management
                                            .errors
                                            .status
                                    }
                                />
                            </div>

                            <div>
                                <label className="text-sm font-medium text-slate-700">
                                    Resolution /
                                    routing note
                                </label>

                                <textarea
                                    rows="4"
                                    value={
                                        management
                                            .data
                                            .resolution_note
                                    }
                                    onChange={(
                                        event,
                                    ) =>
                                        management.setData(
                                            "resolution_note",
                                            event
                                                .target
                                                .value,
                                        )
                                    }
                                    className="mt-1 block w-full rounded-lg border-slate-300"
                                />

                                <InputError
                                    message={
                                        management
                                            .errors
                                            .resolution_note
                                    }
                                />
                            </div>

                            <button
                                disabled={
                                    management.processing
                                }
                                className="w-full rounded-lg bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white"
                            >
                                Update Request
                            </button>
                        </div>
                    </form>

                    <div className="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                        <h2 className="font-semibold text-slate-900">
                            Resolution
                            History
                        </h2>

                        <div className="mt-4 space-y-4">
                            {ticket.history.map(
                                (item) => (
                                    <div
                                        key={
                                            item.uuid
                                        }
                                        className="border-l-2 border-slate-200 pl-4"
                                    >
                                        <div className="text-xs font-semibold text-slate-700">
                                            {humanize(
                                                item.event,
                                            )}
                                        </div>

                                        {item.to_status && (
                                            <div className="mt-1 text-xs text-slate-500">
                                                {humanize(
                                                    item.from_status,
                                                )}
                                                {" → "}
                                                {humanize(
                                                    item.to_status,
                                                )}
                                            </div>
                                        )}

                                        {item.note && (
                                            <p className="mt-2 text-xs leading-5 text-slate-600">
                                                {
                                                    item.note
                                                }
                                            </p>
                                        )}
                                    </div>
                                ),
                            )}
                        </div>
                    </div>
                </aside>
            </div>
        </AdminLayout>
    );
}
