import Pagination from "@/Components/Pagination";
import FinanceLayout from "@/Layouts/FinanceLayout";
import { Head, router, useForm } from "@inertiajs/react";
import {
    Banknote,
    Download,
    ReceiptText,
    RotateCcw,
    TriangleAlert,
} from "lucide-react";

const label = (value) =>
    String(value ?? "")
        .replaceAll("_", " ")
        .replace(/\b\w/g, (letter) => letter.toUpperCase());

const money = (value, currency) =>
    `${currency} ${Number(value ?? 0).toLocaleString(
        undefined,
        {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        },
    )}`;

function cleanFilters(filters) {
    return Object.fromEntries(
        Object.entries(filters).filter(
            ([, value]) =>
                value !== "" &&
                value !== null &&
                value !== undefined,
        ),
    );
}

export default function Index({
    filters,
    summary,
    payments,
    options,
}) {
    const form = useForm({
        from: filters.from ?? "",
        to: filters.to ?? "",
        method: filters.method ?? "",
        currency: filters.currency ?? "",
    });

    const applyFilters = (event) => {
        event.preventDefault();

        router.get(
            route("finance.reports.index"),
            cleanFilters(form.data),
            {
                preserveState: true,
                replace: true,
            },
        );
    };

    const exportUrl = (routeName) =>
        route(
            routeName,
            cleanFilters(form.data),
        );

    return (
        <FinanceLayout title="Financial Reports">
            <Head title="Financial Reports" />

            <div className="mx-auto max-w-7xl space-y-6">
                <section className="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                    <p className="text-sm font-medium text-indigo-600">
                        M17 · Finance Reports
                    </p>

                    <h2 className="mt-1 text-2xl font-semibold text-slate-900">
                        Revenue & reconciliation
                    </h2>

                    <p className="mt-2 max-w-3xl text-sm leading-6 text-slate-600">
                        Revenue figures come directly from payment and
                        refund source records. No clinical information is
                        included in Finance reports.
                    </p>
                </section>

                <section className="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                    <form
                        onSubmit={applyFilters}
                        className="grid gap-4 md:grid-cols-4"
                    >
                        <div>
                            <label className="text-sm font-medium text-slate-700">
                                From
                            </label>

                            <input
                                type="date"
                                value={form.data.from}
                                onChange={(event) =>
                                    form.setData(
                                        "from",
                                        event.target.value,
                                    )
                                }
                                className="mt-1 block w-full rounded-lg border-slate-300"
                            />
                        </div>

                        <div>
                            <label className="text-sm font-medium text-slate-700">
                                To
                            </label>

                            <input
                                type="date"
                                value={form.data.to}
                                onChange={(event) =>
                                    form.setData(
                                        "to",
                                        event.target.value,
                                    )
                                }
                                className="mt-1 block w-full rounded-lg border-slate-300"
                            />
                        </div>

                        <div>
                            <label className="text-sm font-medium text-slate-700">
                                Method
                            </label>

                            <select
                                value={form.data.method}
                                onChange={(event) =>
                                    form.setData(
                                        "method",
                                        event.target.value,
                                    )
                                }
                                className="mt-1 block w-full rounded-lg border-slate-300"
                            >
                                <option value="">
                                    All methods
                                </option>

                                {options.methods.map(
                                    (method) => (
                                        <option
                                            key={method}
                                            value={method}
                                        >
                                            {label(method)}
                                        </option>
                                    ),
                                )}
                            </select>
                        </div>

                        <div>
                            <label className="text-sm font-medium text-slate-700">
                                Currency
                            </label>

                            <input
                                value={form.data.currency}
                                onChange={(event) =>
                                    form.setData(
                                        "currency",
                                        event.target.value.toUpperCase(),
                                    )
                                }
                                maxLength="3"
                                placeholder="LKR"
                                className="mt-1 block w-full rounded-lg border-slate-300"
                            />
                        </div>

                        <div className="md:col-span-4 flex flex-wrap justify-end gap-2">
                            <button
                                type="button"
                                onClick={() =>
                                    router.get(
                                        route(
                                            "finance.reports.index",
                                        ),
                                    )
                                }
                                className="inline-flex items-center gap-2 rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium"
                            >
                                <RotateCcw className="h-4 w-4" />
                                Reset
                            </button>

                            <button
                                type="submit"
                                className="rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white"
                            >
                                Apply filters
                            </button>

                            <a
                                href={exportUrl(
                                    "finance.reports.csv",
                                )}
                                className="inline-flex items-center gap-2 rounded-lg border border-indigo-300 px-4 py-2 text-sm font-medium text-indigo-700"
                            >
                                <Download className="h-4 w-4" />
                                CSV
                            </a>

                            <a
                                href={exportUrl(
                                    "finance.reports.pdf",
                                )}
                                className="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white"
                            >
                                <Download className="h-4 w-4" />
                                PDF
                            </a>
                        </div>
                    </form>
                </section>

                <section className="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    <div className="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                        <ReceiptText className="h-5 w-5 text-slate-500" />

                        <p className="mt-4 text-sm text-slate-500">
                            Collected payments
                        </p>

                        <p className="mt-1 text-2xl font-semibold">
                            {summary.payment_count}
                        </p>
                    </div>

                    <div className="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                        <Banknote className="h-5 w-5 text-slate-500" />

                        <p className="mt-4 text-sm text-slate-500">
                            Partial refunds
                        </p>

                        <p className="mt-1 text-2xl font-semibold">
                            {
                                summary.partially_refunded_count
                            }
                        </p>
                    </div>

                    <div className="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                        <RotateCcw className="h-5 w-5 text-slate-500" />

                        <p className="mt-4 text-sm text-slate-500">
                            Fully refunded
                        </p>

                        <p className="mt-1 text-2xl font-semibold">
                            {
                                summary.fully_refunded_count
                            }
                        </p>
                    </div>

                    <div className="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                        <TriangleAlert className="h-5 w-5 text-amber-600" />

                        <p className="mt-4 text-sm text-slate-500">
                            Reconciliation mismatch
                        </p>

                        <p className="mt-1 text-2xl font-semibold">
                            {
                                summary.reconciliation_mismatch_count
                            }
                        </p>
                    </div>
                </section>

                <section className="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h3 className="text-lg font-semibold text-slate-900">
                        Revenue by currency
                    </h3>

                    <div className="mt-5 grid gap-4 lg:grid-cols-3">
                        {summary.currency_totals.map(
                            (row) => (
                                <div
                                    key={row.currency}
                                    className="rounded-xl border border-slate-200 p-5"
                                >
                                    <p className="font-semibold text-slate-900">
                                        {row.currency}
                                    </p>

                                    <dl className="mt-4 space-y-2 text-sm">
                                        <div className="flex justify-between">
                                            <dt className="text-slate-500">
                                                Gross
                                            </dt>
                                            <dd>
                                                {money(
                                                    row.gross_amount,
                                                    row.currency,
                                                )}
                                            </dd>
                                        </div>

                                        <div className="flex justify-between">
                                            <dt className="text-slate-500">
                                                Refunded
                                            </dt>
                                            <dd>
                                                {money(
                                                    row.refunded_amount,
                                                    row.currency,
                                                )}
                                            </dd>
                                        </div>

                                        <div className="flex justify-between border-t border-slate-200 pt-2 font-semibold">
                                            <dt>Net</dt>
                                            <dd>
                                                {money(
                                                    row.net_amount,
                                                    row.currency,
                                                )}
                                            </dd>
                                        </div>
                                    </dl>
                                </div>
                            ),
                        )}
                    </div>
                </section>

                <section className="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h3 className="text-lg font-semibold">
                        Payment methods
                    </h3>

                    <div className="mt-5 overflow-x-auto">
                        <table className="min-w-full divide-y divide-slate-200 text-sm">
                            <thead>
                                <tr className="text-left text-xs uppercase text-slate-500">
                                    <th className="px-3 py-3">
                                        Method
                                    </th>
                                    <th className="px-3 py-3">
                                        Currency
                                    </th>
                                    <th className="px-3 py-3">
                                        Payments
                                    </th>
                                    <th className="px-3 py-3">
                                        Amount
                                    </th>
                                </tr>
                            </thead>

                            <tbody className="divide-y divide-slate-100">
                                {summary.method_breakdown.map(
                                    (row) => (
                                        <tr
                                            key={`${row.method}-${row.currency}`}
                                        >
                                            <td className="px-3 py-3">
                                                {label(
                                                    row.method,
                                                )}
                                            </td>

                                            <td className="px-3 py-3">
                                                {row.currency}
                                            </td>

                                            <td className="px-3 py-3">
                                                {
                                                    row.payment_count
                                                }
                                            </td>

                                            <td className="px-3 py-3">
                                                {money(
                                                    row.total_amount,
                                                    row.currency,
                                                )}
                                            </td>
                                        </tr>
                                    ),
                                )}
                            </tbody>
                        </table>
                    </div>
                </section>

                <section className="rounded-xl border border-slate-200 bg-white shadow-sm">
                    <div className="border-b border-slate-200 p-6">
                        <h3 className="text-lg font-semibold">
                            Revenue source records
                        </h3>
                    </div>

                    <div className="overflow-x-auto">
                        <table className="min-w-full divide-y divide-slate-200 text-sm">
                            <thead>
                                <tr className="text-left text-xs uppercase text-slate-500">
                                    <th className="px-4 py-3">
                                        Paid
                                    </th>
                                    <th className="px-4 py-3">
                                        Service
                                    </th>
                                    <th className="px-4 py-3">
                                        Method
                                    </th>
                                    <th className="px-4 py-3">
                                        Amount
                                    </th>
                                    <th className="px-4 py-3">
                                        Status
                                    </th>
                                    <th className="px-4 py-3">
                                        Reconciliation
                                    </th>
                                </tr>
                            </thead>

                            <tbody className="divide-y divide-slate-100">
                                {payments.data.map(
                                    (payment) => (
                                        <tr
                                            key={
                                                payment.uuid
                                            }
                                        >
                                            <td className="px-4 py-3">
                                                {
                                                    payment.paid_at
                                                }
                                            </td>

                                            <td className="px-4 py-3">
                                                {
                                                    payment.service_name
                                                }
                                            </td>

                                            <td className="px-4 py-3">
                                                {label(
                                                    payment.method,
                                                )}
                                            </td>

                                            <td className="px-4 py-3">
                                                {money(
                                                    payment.amount,
                                                    payment.currency,
                                                )}
                                            </td>

                                            <td className="px-4 py-3">
                                                {label(
                                                    payment.status,
                                                )}
                                            </td>

                                            <td className="px-4 py-3">
                                                {label(
                                                    payment.reconciliation_status,
                                                )}
                                            </td>
                                        </tr>
                                    ),
                                )}
                            </tbody>
                        </table>
                    </div>

                    <div className="p-6">
                        <Pagination links={payments.links} />
                    </div>
                </section>
            </div>
        </FinanceLayout>
    );
}
