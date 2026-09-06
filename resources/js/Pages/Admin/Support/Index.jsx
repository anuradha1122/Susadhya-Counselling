import Pagination from "@/Components/Pagination";
import AdminLayout from "@/Layouts/AdminLayout";
import {
    Head,
    Link,
    router,
} from "@inertiajs/react";
import {
    LifeBuoy,
    Star,
} from "lucide-react";
import { useState } from "react";

function humanize(value) {
    return String(value ?? "")
        .replaceAll("_", " ")
        .replace(/\b\w/g, (char) =>
            char.toUpperCase(),
        );
}

export default function Index({
    tickets,
    filters,
    statuses,
    categories,
    priorities,
}) {
    const [form, setForm] =
        useState(filters);

    const submit = (event) => {
        event.preventDefault();

        router.get(
            route(
                "admin.support.index",
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
            <Head title="Support" />

            <div className="mx-auto max-w-7xl space-y-6">
                <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <div className="flex items-center gap-2 text-sm font-semibold text-indigo-600">
                            <LifeBuoy className="h-4 w-4" />
                            M20 Support
                        </div>

                        <h1 className="mt-1 text-2xl font-bold text-slate-900">
                            Support Requests
                        </h1>
                    </div>

                    <Link
                        href={route(
                            "admin.support.feedback.index",
                        )}
                        className="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700"
                    >
                        <Star className="h-4 w-4" />
                        Session Feedback
                    </Link>
                </div>

                <form
                    onSubmit={submit}
                    className="grid gap-3 rounded-xl border border-slate-200 bg-white p-4 shadow-sm lg:grid-cols-6"
                >
                    <input
                        value={
                            form.search ?? ""
                        }
                        onChange={(event) =>
                            setForm({
                                ...form,
                                search:
                                    event
                                        .target
                                        .value,
                            })
                        }
                        placeholder="Search"
                        className="rounded-lg border-slate-300 text-sm lg:col-span-2"
                    />

                    <select
                        value={
                            form.status ?? ""
                        }
                        onChange={(event) =>
                            setForm({
                                ...form,
                                status:
                                    event
                                        .target
                                        .value,
                            })
                        }
                        className="rounded-lg border-slate-300 text-sm"
                    >
                        <option value="">
                            Status
                        </option>
                        {statuses.map(
                            (item) => (
                                <option
                                    key={item}
                                    value={item}
                                >
                                    {humanize(
                                        item,
                                    )}
                                </option>
                            ),
                        )}
                    </select>

                    <select
                        value={
                            form.category ??
                            ""
                        }
                        onChange={(event) =>
                            setForm({
                                ...form,
                                category:
                                    event
                                        .target
                                        .value,
                            })
                        }
                        className="rounded-lg border-slate-300 text-sm"
                    >
                        <option value="">
                            Category
                        </option>
                        {categories.map(
                            (item) => (
                                <option
                                    key={item}
                                    value={item}
                                >
                                    {humanize(
                                        item,
                                    )}
                                </option>
                            ),
                        )}
                    </select>

                    <select
                        value={
                            form.priority ??
                            ""
                        }
                        onChange={(event) =>
                            setForm({
                                ...form,
                                priority:
                                    event
                                        .target
                                        .value,
                            })
                        }
                        className="rounded-lg border-slate-300 text-sm"
                    >
                        <option value="">
                            Priority
                        </option>
                        {priorities.map(
                            (item) => (
                                <option
                                    key={item}
                                    value={item}
                                >
                                    {humanize(
                                        item,
                                    )}
                                </option>
                            ),
                        )}
                    </select>

                    <button className="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white">
                        Filter
                    </button>
                </form>

                <div className="overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm">
                    <table className="min-w-full divide-y divide-slate-200">
                        <thead className="bg-slate-50">
                            <tr>
                                {[
                                    "Request",
                                    "Requester",
                                    "Category",
                                    "Priority",
                                    "Status",
                                    "Owner",
                                ].map(
                                    (label) => (
                                        <th
                                            key={
                                                label
                                            }
                                            className="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500"
                                        >
                                            {
                                                label
                                            }
                                        </th>
                                    ),
                                )}
                            </tr>
                        </thead>

                        <tbody className="divide-y divide-slate-100">
                            {tickets.data.map(
                                (ticket) => (
                                    <tr
                                        key={
                                            ticket.uuid
                                        }
                                        className="hover:bg-slate-50"
                                    >
                                        <td className="px-5 py-4">
                                            <Link
                                                href={route(
                                                    "admin.support.show",
                                                    ticket.uuid,
                                                )}
                                                className="font-semibold text-indigo-600"
                                            >
                                                {
                                                    ticket.subject
                                                }
                                            </Link>
                                        </td>

                                        <td className="px-5 py-4 text-sm text-slate-600">
                                            {
                                                ticket.requester
                                                    ?.name
                                            }
                                        </td>

                                        <td className="px-5 py-4 text-sm text-slate-600">
                                            {humanize(
                                                ticket.category,
                                            )}
                                        </td>

                                        <td className="px-5 py-4 text-sm text-slate-600">
                                            {humanize(
                                                ticket.priority,
                                            )}
                                        </td>

                                        <td className="px-5 py-4 text-sm text-slate-600">
                                            {humanize(
                                                ticket.status,
                                            )}
                                        </td>

                                        <td className="px-5 py-4 text-sm text-slate-600">
                                            {ticket
                                                .owner
                                                ?.name ??
                                                "Unassigned"}
                                        </td>
                                    </tr>
                                ),
                            )}
                        </tbody>
                    </table>
                </div>

                <Pagination
                    links={
                        tickets.links
                    }
                />
            </div>
        </AdminLayout>
    );
}
