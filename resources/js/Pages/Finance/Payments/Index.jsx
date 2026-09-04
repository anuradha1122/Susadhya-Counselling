import InputError from "@/Components/InputError";
import Pagination from "@/Components/Pagination";
import PrimaryButton from "@/Components/PrimaryButton";
import FinanceLayout from "@/Layouts/FinanceLayout";
import {
    Head,
    router,
    useForm,
} from "@inertiajs/react";
import {
    Download,
    RefreshCw,
} from "lucide-react";

const money = (amount, currency = "LKR") =>
    `${currency} ${Number(amount ?? 0).toFixed(2)}`;

const inputClass = (hasError) =>
    `mt-1 block w-full rounded-md shadow-sm focus:ring-indigo-500 ${
        hasError
            ? "border-rose-500 focus:border-rose-500"
            : "border-slate-300 focus:border-indigo-500"
    }`;

export default function Index({
    payments,
    unpaidAppointments,
    filters,
    statuses,
}) {
    const manual = useForm({
        appointment_id: "",
        reference: "",
        paid_at: "",
    });

    const applyFilters = (event) => {
        event.preventDefault();

        const form = new FormData(
            event.currentTarget,
        );

        router.get(
            route(
                "finance.payments.index",
            ),
            {
                search:
                    form.get("search") ?? "",
                status:
                    form.get("status") ?? "",
                reconciliation:
                    form.get(
                        "reconciliation",
                    ) ?? "",
            },
            {
                preserveState: true,
                preserveScroll: true,
                replace: true,
            },
        );
    };

    const recordManual = (event) => {
        event.preventDefault();

        manual.clearErrors();

        manual.post(
            route(
                "finance.payments.manual.store",
            ),
            {
                preserveScroll: true,

                onSuccess: () => {
                    manual.reset();
                },

                onError: (errors) => {
                    console.error(
                        "Manual payment validation errors:",
                        errors,
                    );
                },
            },
        );
    };

    const reconcile = (payment) => {
        router.patch(
            route(
                "finance.payments.reconcile",
                payment.uuid,
            ),
            {},
            {
                preserveScroll: true,
            },
        );
    };

    return (
        <FinanceLayout title="Payments">
            <Head title="Payments" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                    {/* Header */}
                    <div className="rounded-lg bg-white p-6 shadow-sm sm:rounded-lg">
                        <h1 className="text-xl font-semibold text-slate-900">
                            Payments & reconciliation
                        </h1>

                        <p className="mt-1 text-sm text-slate-600">
                            Manage gateway payments,
                            manual payments, invoices,
                            receipts and reconciliation.
                        </p>

                        <div className="mt-4 rounded-lg bg-indigo-50 p-4">
                            <p className="text-sm text-indigo-800">
                                This workspace contains
                                financial information only.
                                Clinical notes and confidential
                                case records are not exposed to
                                Finance Admin users.
                            </p>
                        </div>
                    </div>

                    {/* Manual Payment */}
                    <div className="rounded-lg bg-white p-6 shadow-sm sm:rounded-lg">
                        <div>
                            <h2 className="font-semibold text-slate-900">
                                Record manual payment
                            </h2>

                            <p className="mt-1 text-sm text-slate-500">
                                Record a verified cash,
                                bank-transfer or other manual
                                payment against an unpaid
                                appointment.
                            </p>
                        </div>

                        {/* Error Summary */}
                        {Object.keys(
                            manual.errors,
                        ).length > 0 && (
                            <div className="mt-6 rounded-lg border border-rose-300 bg-rose-50 p-4">
                                <p className="font-medium text-rose-800">
                                    The payment could not be
                                    recorded.
                                </p>

                                <p className="mt-1 text-sm text-rose-700">
                                    Laravel returned the
                                    following error
                                    {Object.keys(
                                        manual.errors,
                                    ).length > 1
                                        ? "s"
                                        : ""}
                                    :
                                </p>

                                <ul className="mt-3 list-disc space-y-1 pl-5 text-sm text-rose-700">
                                    {Object.entries(
                                        manual.errors,
                                    ).map(
                                        ([
                                            field,
                                            message,
                                        ]) => (
                                            <li
                                                key={
                                                    field
                                                }
                                            >
                                                <span className="font-medium">
                                                    {
                                                        field
                                                    }
                                                    :
                                                </span>{" "}
                                                {
                                                    message
                                                }
                                            </li>
                                        ),
                                    )}
                                </ul>
                            </div>
                        )}

                        <form
                            className="mt-6 space-y-5"
                            onSubmit={recordManual}
                        >
                            <div className="grid gap-5 lg:grid-cols-2">
                                {/* Appointment */}
                                <div>
                                    <label
                                        htmlFor="appointment_id"
                                        className="block text-sm font-medium text-slate-700"
                                    >
                                        Appointment
                                    </label>

                                    <select
                                        id="appointment_id"
                                        name="appointment_id"
                                        value={
                                            manual.data
                                                .appointment_id
                                        }
                                        onChange={(event) =>
                                            manual.setData(
                                                "appointment_id",
                                                event.target
                                                    .value,
                                            )
                                        }
                                        className={inputClass(
                                            Boolean(
                                                manual
                                                    .errors
                                                    .appointment_id,
                                            ),
                                        )}
                                    >
                                        <option value="">
                                            Select appointment
                                        </option>

                                        {unpaidAppointments.map(
                                            (
                                                appointment,
                                            ) => {
                                                const clientName =
                                                    appointment
                                                        .client_profile
                                                        ?.user
                                                        ?.name ??
                                                    "Client";

                                                const serviceName =
                                                    appointment
                                                        .counselling_service
                                                        ?.name ??
                                                    "Counselling service";

                                                const amount =
                                                    appointment.fee_amount ??
                                                    appointment
                                                        .counselling_service
                                                        ?.price ??
                                                    0;

                                                const currency =
                                                    appointment.fee_currency ??
                                                    appointment
                                                        .counselling_service
                                                        ?.currency ??
                                                    "LKR";

                                                return (
                                                    <option
                                                        key={
                                                            appointment.id
                                                        }
                                                        value={
                                                            appointment.id
                                                        }
                                                    >
                                                        {
                                                            clientName
                                                        }{" "}
                                                        ·{" "}
                                                        {
                                                            serviceName
                                                        }{" "}
                                                        ·{" "}
                                                        {money(
                                                            amount,
                                                            currency,
                                                        )}
                                                    </option>
                                                );
                                            },
                                        )}
                                    </select>

                                    <InputError
                                        className="mt-2"
                                        message={
                                            manual.errors
                                                .appointment_id
                                        }
                                    />

                                    {unpaidAppointments.length ===
                                        0 && (
                                        <p className="mt-2 text-sm text-amber-600">
                                            There are currently
                                            no unpaid
                                            appointments
                                            available.
                                        </p>
                                    )}
                                </div>

                                {/* Reference */}
                                <div>
                                    <label
                                        htmlFor="reference"
                                        className="block text-sm font-medium text-slate-700"
                                    >
                                        Payment reference
                                    </label>

                                    <input
                                        id="reference"
                                        name="reference"
                                        type="text"
                                        value={
                                            manual.data
                                                .reference
                                        }
                                        onChange={(event) =>
                                            manual.setData(
                                                "reference",
                                                event.target
                                                    .value,
                                            )
                                        }
                                        placeholder="Bank slip / cash receipt / transaction reference"
                                        className={inputClass(
                                            Boolean(
                                                manual
                                                    .errors
                                                    .reference,
                                            ),
                                        )}
                                    />

                                    <InputError
                                        className="mt-2"
                                        message={
                                            manual.errors
                                                .reference
                                        }
                                    />
                                </div>

                                {/* Paid At */}
                                <div>
                                    <label
                                        htmlFor="paid_at"
                                        className="block text-sm font-medium text-slate-700"
                                    >
                                        Paid date/time
                                    </label>

                                    <input
                                        id="paid_at"
                                        name="paid_at"
                                        type="datetime-local"
                                        value={
                                            manual.data
                                                .paid_at
                                        }
                                        onChange={(event) =>
                                            manual.setData(
                                                "paid_at",
                                                event.target
                                                    .value,
                                            )
                                        }
                                        className={inputClass(
                                            Boolean(
                                                manual
                                                    .errors
                                                    .paid_at,
                                            ),
                                        )}
                                    />

                                    <p className="mt-1 text-xs text-slate-500">
                                        Optional. Leave empty
                                        to record the payment at
                                        the current system time.
                                    </p>

                                    <InputError
                                        className="mt-2"
                                        message={
                                            manual.errors
                                                .paid_at
                                        }
                                    />
                                </div>
                            </div>

                            <div className="flex items-center gap-3">
                                <PrimaryButton
                                    type="submit"
                                    disabled={
                                        manual.processing ||
                                        unpaidAppointments.length ===
                                            0
                                    }
                                >
                                    {manual.processing
                                        ? "Recording..."
                                        : "Record payment"}
                                </PrimaryButton>
                            </div>
                        </form>
                    </div>

                    {/* Filters */}
                    <div className="rounded-lg bg-white p-6 shadow-sm sm:rounded-lg">
                        <form
                            onSubmit={applyFilters}
                            className="grid gap-4 md:grid-cols-2 lg:grid-cols-4"
                        >
                            <div>
                                <label className="block text-sm font-medium text-slate-700">
                                    Search
                                </label>

                                <input
                                    name="search"
                                    type="text"
                                    defaultValue={
                                        filters?.search ??
                                        ""
                                    }
                                    placeholder="Client, reference, invoice..."
                                    className="mt-1 block w-full rounded-md border-slate-300 shadow-sm"
                                />
                            </div>

                            <div>
                                <label className="block text-sm font-medium text-slate-700">
                                    Payment status
                                </label>

                                <select
                                    name="status"
                                    defaultValue={
                                        filters?.status ??
                                        ""
                                    }
                                    className="mt-1 block w-full rounded-md border-slate-300 shadow-sm"
                                >
                                    <option value="">
                                        All statuses
                                    </option>

                                    {statuses.map(
                                        (status) => (
                                            <option
                                                key={
                                                    status
                                                }
                                                value={
                                                    status
                                                }
                                            >
                                                {status.replaceAll(
                                                    "_",
                                                    " ",
                                                )}
                                            </option>
                                        ),
                                    )}
                                </select>
                            </div>

                            <div>
                                <label className="block text-sm font-medium text-slate-700">
                                    Reconciliation
                                </label>

                                <select
                                    name="reconciliation"
                                    defaultValue={
                                        filters?.reconciliation ??
                                        ""
                                    }
                                    className="mt-1 block w-full rounded-md border-slate-300 shadow-sm"
                                >
                                    <option value="">
                                        All
                                    </option>

                                    <option value="pending">
                                        Pending
                                    </option>

                                    <option value="matched">
                                        Matched
                                    </option>

                                    <option value="mismatch">
                                        Mismatch
                                    </option>

                                    <option value="unavailable">
                                        Unavailable
                                    </option>
                                </select>
                            </div>

                            <div className="flex items-end gap-2">
                                <PrimaryButton
                                    type="submit"
                                >
                                    Apply filters
                                </PrimaryButton>

                                <button
                                    type="button"
                                    onClick={() =>
                                        router.get(
                                            route(
                                                "finance.payments.index",
                                            ),
                                        )
                                    }
                                    className="rounded-md border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
                                >
                                    Reset
                                </button>
                            </div>
                        </form>
                    </div>

                    {/* Payment Records */}
                    <div className="overflow-hidden rounded-lg bg-white shadow-sm sm:rounded-lg">
                        <div className="border-b border-slate-200 px-6 py-4">
                            <h2 className="font-semibold text-slate-900">
                                Payment records
                            </h2>
                        </div>

                        {payments.data.length ===
                        0 ? (
                            <div className="p-10 text-center">
                                <p className="text-sm text-slate-500">
                                    No payment records
                                    found.
                                </p>
                            </div>
                        ) : (
                            <div className="overflow-x-auto">
                                <table className="min-w-full divide-y divide-slate-200 text-sm">
                                    <thead className="bg-slate-50">
                                        <tr>
                                            <th className="px-5 py-3 text-left font-medium text-slate-600">
                                                Client
                                            </th>

                                            <th className="px-5 py-3 text-left font-medium text-slate-600">
                                                Service
                                            </th>

                                            <th className="px-5 py-3 text-left font-medium text-slate-600">
                                                Amount
                                            </th>

                                            <th className="px-5 py-3 text-left font-medium text-slate-600">
                                                Method
                                            </th>

                                            <th className="px-5 py-3 text-left font-medium text-slate-600">
                                                Status
                                            </th>

                                            <th className="px-5 py-3 text-left font-medium text-slate-600">
                                                Reconciliation
                                            </th>

                                            <th className="px-5 py-3 text-right font-medium text-slate-600">
                                                Actions
                                            </th>
                                        </tr>
                                    </thead>

                                    <tbody className="divide-y divide-slate-100 bg-white">
                                        {payments.data.map(
                                            (
                                                payment,
                                            ) => (
                                                <tr
                                                    key={
                                                        payment.uuid
                                                    }
                                                >
                                                    <td className="px-5 py-4 text-slate-700">
                                                        {payment
                                                            .client_profile
                                                            ?.user
                                                            ?.name ??
                                                            "Not provided"}
                                                    </td>

                                                    <td className="px-5 py-4 text-slate-700">
                                                        {payment
                                                            .appointment
                                                            ?.counselling_service
                                                            ?.name ??
                                                            "Not provided"}
                                                    </td>

                                                    <td className="whitespace-nowrap px-5 py-4 font-medium text-slate-900">
                                                        {money(
                                                            payment.amount,
                                                            payment.currency,
                                                        )}
                                                    </td>

                                                    <td className="px-5 py-4 capitalize text-slate-600">
                                                        {payment.method?.replaceAll(
                                                            "_",
                                                            " ",
                                                        )}
                                                    </td>

                                                    <td className="px-5 py-4">
                                                        <span className="inline-flex rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium capitalize text-slate-700">
                                                            {payment.status.replaceAll(
                                                                "_",
                                                                " ",
                                                            )}
                                                        </span>
                                                    </td>

                                                    <td className="px-5 py-4">
                                                        <span className="inline-flex rounded-full bg-indigo-50 px-2.5 py-1 text-xs font-medium capitalize text-indigo-700">
                                                            {payment.reconciliation_status.replaceAll(
                                                                "_",
                                                                " ",
                                                            )}
                                                        </span>
                                                    </td>

                                                    <td className="px-5 py-4">
                                                        <div className="flex justify-end gap-2">
                                                            {payment.method ===
                                                                "gateway" && (
                                                                <button
                                                                    type="button"
                                                                    onClick={() =>
                                                                        reconcile(
                                                                            payment,
                                                                        )
                                                                    }
                                                                    className="rounded-md border border-slate-300 p-2 text-slate-600 hover:bg-slate-50"
                                                                    title="Reconcile"
                                                                >
                                                                    <RefreshCw className="h-4 w-4" />
                                                                </button>
                                                            )}

                                                            {payment.invoice && (
                                                                <a
                                                                    href={route(
                                                                        "finance.payments.invoice",
                                                                        payment.uuid,
                                                                    )}
                                                                    className="rounded-md border border-slate-300 p-2 text-slate-600 hover:bg-slate-50"
                                                                    title="Download invoice"
                                                                >
                                                                    <Download className="h-4 w-4" />
                                                                </a>
                                                            )}

                                                            {payment
                                                                .invoice
                                                                ?.receipt_number && (
                                                                <a
                                                                    href={route(
                                                                        "finance.payments.receipt",
                                                                        payment.uuid,
                                                                    )}
                                                                    className="rounded-md border border-slate-300 px-3 py-2 text-xs font-medium text-slate-600 hover:bg-slate-50"
                                                                >
                                                                    Receipt
                                                                </a>
                                                            )}
                                                        </div>
                                                    </td>
                                                </tr>
                                            ),
                                        )}
                                    </tbody>
                                </table>
                            </div>
                        )}

                        {payments.links && (
                            <div className="border-t border-slate-200 px-6 py-4">
                                <Pagination
                                    links={
                                        payments.links
                                    }
                                />
                            </div>
                        )}
                    </div>
                </div>
            </div>
        </FinanceLayout>
    );
}
