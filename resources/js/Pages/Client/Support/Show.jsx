import InputError from "@/Components/InputError";
import ClientLayout from "@/Layouts/ClientLayout";
import {
    Head,
    Link,
    useForm,
} from "@inertiajs/react";
import {
    Clock3,
    Send,
} from "lucide-react";

function humanize(value) {
    return String(value ?? "")
        .replaceAll("_", " ")
        .replace(/\b\w/g, (char) =>
            char.toUpperCase(),
        );
}

export default function Show({
    ticket,
}) {
    const {
        data,
        setData,
        post,
        processing,
        errors,
        reset,
    } = useForm({
        body: "",
    });

    const submit = (event) => {
        event.preventDefault();

        post(
            route(
                "client.support.replies.store",
                ticket.uuid,
            ),
            {
                preserveScroll: true,
                onSuccess: () =>
                    reset("body"),
            },
        );
    };

    const closed =
        ticket.status === "closed";

    return (
        <ClientLayout>
            <Head title={ticket.subject} />

            <div className="mx-auto max-w-5xl space-y-6">
                <div>
                    <Link
                        href={route(
                            "client.support.index",
                        )}
                        className="text-sm font-medium text-indigo-600"
                    >
                        ← Back to Support
                    </Link>

                    <h1 className="mt-3 text-2xl font-bold text-slate-900">
                        {ticket.subject}
                    </h1>

                    <div className="mt-2 flex flex-wrap gap-2 text-xs text-slate-500">
                        <span>
                            {humanize(
                                ticket.category,
                            )}
                        </span>

                        <span>•</span>

                        <span>
                            {humanize(
                                ticket.status,
                            )}
                        </span>

                        <span>•</span>

                        <span>
                            {humanize(
                                ticket.priority,
                            )}{" "}
                            priority
                        </span>
                    </div>
                </div>

                <div className="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                    <p className="whitespace-pre-wrap text-sm leading-7 text-slate-700">
                        {
                            ticket.description
                        }
                    </p>
                </div>

                <div className="rounded-xl border border-slate-200 bg-white shadow-sm">
                    <div className="border-b border-slate-200 px-6 py-4">
                        <h2 className="font-semibold text-slate-900">
                            Conversation
                        </h2>
                    </div>

                    <div className="space-y-4 p-6">
                        {ticket.replies
                            .length ===
                        0 ? (
                            <p className="text-sm text-slate-500">
                                No replies yet.
                            </p>
                        ) : (
                            ticket.replies.map(
                                (reply) => (
                                    <div
                                        key={
                                            reply.uuid
                                        }
                                        className={`rounded-xl p-4 ${
                                            reply.is_mine
                                                ? "ml-8 bg-indigo-50"
                                                : "mr-8 bg-slate-50"
                                        }`}
                                    >
                                        <div className="text-xs font-semibold text-slate-500">
                                            {
                                                reply.author
                                            }
                                        </div>

                                        <p className="mt-2 whitespace-pre-wrap text-sm leading-6 text-slate-700">
                                            {
                                                reply.body
                                            }
                                        </p>
                                    </div>
                                ),
                            )
                        )}
                    </div>
                </div>

                {!closed && (
                    <form
                        onSubmit={submit}
                        className="rounded-xl border border-slate-200 bg-white p-6 shadow-sm"
                    >
                        <label className="text-sm font-semibold text-slate-700">
                            Add a reply
                        </label>

                        <textarea
                            rows="5"
                            value={data.body}
                            onChange={(
                                event,
                            ) =>
                                setData(
                                    "body",
                                    event
                                        .target
                                        .value,
                                )
                            }
                            className="mt-2 block w-full rounded-lg border-slate-300"
                        />

                        <InputError
                            className="mt-2"
                            message={errors.body}
                        />

                        <button
                            disabled={
                                processing
                            }
                            className="mt-4 inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white"
                        >
                            <Send className="h-4 w-4" />
                            Reply
                        </button>
                    </form>
                )}

                <div className="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h2 className="flex items-center gap-2 font-semibold text-slate-900">
                        <Clock3 className="h-4 w-4" />
                        Status History
                    </h2>

                    <div className="mt-4 space-y-3">
                        {ticket.activity.map(
                            (item) => (
                                <div
                                    key={
                                        item.uuid
                                    }
                                    className="border-l-2 border-slate-200 pl-4 text-sm text-slate-600"
                                >
                                    {item.to_status
                                        ? `${humanize(
                                              item.from_status,
                                          )} → ${humanize(
                                              item.to_status,
                                          )}`
                                        : humanize(
                                              item.event,
                                          )}
                                </div>
                            ),
                        )}
                    </div>
                </div>
            </div>
        </ClientLayout>
    );
}
