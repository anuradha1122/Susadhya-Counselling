import Pagination from "@/Components/Pagination";
import AdminLayout from "@/Layouts/AdminLayout";
import {
    Head,
    Link,
    router,
} from "@inertiajs/react";
import { useState } from "react";

export default function Feedback({
    feedback,
    filters,
}) {
    const [form, setForm] =
        useState(filters);

    const submit = (event) => {
        event.preventDefault();

        router.get(
            route(
                "admin.support.feedback.index",
            ),
            form,
            {
                preserveState: true,
                replace: true,
            },
        );
    };

    return (
        <AdminLayout>
            <Head title="Session Feedback" />

            <div className="mx-auto max-w-7xl space-y-6">
                <div>
                    <Link
                        href={route(
                            "admin.support.index",
                        )}
                        className="text-sm font-medium text-indigo-600"
                    >
                        ← Support Requests
                    </Link>

                    <h1 className="mt-3 text-2xl font-bold text-slate-900">
                        Session Feedback
                    </h1>

                    <p className="mt-2 text-sm text-slate-600">
                        Feedback remains
                        internal operational
                        data. It is never
                        automatically converted
                        into a public
                        testimonial.
                    </p>
                </div>

                <form
                    onSubmit={submit}
                    className="flex flex-wrap gap-3 rounded-xl border border-slate-200 bg-white p-4 shadow-sm"
                >
                    <select
                        value={
                            form.rating ?? ""
                        }
                        onChange={(event) =>
                            setForm({
                                ...form,
                                rating:
                                    event
                                        .target
                                        .value,
                            })
                        }
                        className="rounded-lg border-slate-300 text-sm"
                    >
                        <option value="">
                            All ratings
                        </option>

                        {[5, 4, 3, 2, 1].map(
                            (rating) => (
                                <option
                                    key={
                                        rating
                                    }
                                    value={
                                        rating
                                    }
                                >
                                    {rating}/5
                                </option>
                            ),
                        )}
                    </select>

                    <select
                        value={
                            form.follow_up ??
                            ""
                        }
                        onChange={(event) =>
                            setForm({
                                ...form,
                                follow_up:
                                    event
                                        .target
                                        .value,
                            })
                        }
                        className="rounded-lg border-slate-300 text-sm"
                    >
                        <option value="">
                            All follow-up
                            preferences
                        </option>
                        <option value="yes">
                            Follow-up consent
                        </option>
                        <option value="no">
                            No follow-up consent
                        </option>
                    </select>

                    <button className="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white">
                        Filter
                    </button>
                </form>

                <div className="space-y-4">
                    {feedback.data.map(
                        (item) => (
                            <article
                                key={
                                    item.uuid
                                }
                                className="rounded-xl border border-slate-200 bg-white p-6 shadow-sm"
                            >
                                <div className="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                    <div>
                                        <div className="font-semibold text-slate-900">
                                            {
                                                item.client
                                                    .name
                                            }
                                        </div>

                                        <div className="mt-1 text-xs text-slate-500">
                                            {
                                                item.appointment
                                                    .date
                                            }
                                            {" · "}
                                            {
                                                item.appointment
                                                    .counsellor
                                            }
                                        </div>
                                    </div>

                                    <div className="rounded-full bg-amber-50 px-3 py-1 text-sm font-semibold text-amber-700">
                                        {
                                            item.overall_rating
                                        }
                                        /5
                                    </div>
                                </div>

                                {item.comment && (
                                    <p className="mt-4 whitespace-pre-wrap text-sm leading-7 text-slate-700">
                                        {
                                            item.comment
                                        }
                                    </p>
                                )}

                                <div className="mt-4 text-xs text-slate-500">
                                    Follow-up
                                    consent:{" "}
                                    {item.consent_to_follow_up
                                        ? "Yes"
                                        : "No"}
                                </div>
                            </article>
                        ),
                    )}
                </div>

                <Pagination
                    links={
                        feedback.links
                    }
                />
            </div>
        </AdminLayout>
    );
}
