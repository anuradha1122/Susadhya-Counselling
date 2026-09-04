import InputError from "@/Components/InputError";
import Pagination from "@/Components/Pagination";
import PrimaryButton from "@/Components/PrimaryButton";
import ClientLayout from "@/Layouts/ClientLayout";
import {
    Head,
    Link,
    router,
    useForm,
} from "@inertiajs/react";
import {
    CircleDollarSign,
    Download,
    RotateCcw,
} from "lucide-react";
import { useState } from "react";

const money = (amount, currency = "LKR") =>
    `${currency} ${Number(amount ?? 0).toFixed(2)}`;

export default function Index({
    payments,
    appointments,
}) {
    const [refundPayment, setRefundPayment] =
        useState(null);

    const refundForm = useForm({
        requested_amount: "",
        reason: "",
    });

    const pay = (appointment) => {
        router.post(
            route(
                "client.payments.store",
                appointment.id,
            ),
            {},
            {
                preserveScroll: true,
            },
        );
    };

    const requestRefund = (event) => {
        event.preventDefault();

        refundForm.post(
            route(
                "client.payments.refunds.store",
                refundPayment.uuid,
            ),
            {
                preserveScroll: true,
                onSuccess: () => {
                    refundForm.reset();
                    setRefundPayment(null);
                },
            },
        );
    };

    return (
        <ClientLayout title="My Payments">
            <Head title="My Payments" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                    <div className="rounded-lg bg-white p-6 shadow-sm sm:rounded-lg">
                        <div className="flex items-start gap-4">
                            <div className="rounded-lg bg-indigo-50 p-3 text-indigo-600">
                                <CircleDollarSign className="h-6 w-6" />
                            </div>

                            <div>
                                <h1 className="text-xl font-semibold text-slate-900">
                                    Payments & receipts
                                </h1>

                                <p className="mt-1 text-sm text-slate-600">
                                    Pay for your appointments,
                                    download invoices and
                                    receipts, and request
                                    refunds.
                                </p>
                            </div>
                        </div>
                    </div>

                    {appointments.length > 0 && (
                        <div className="rounded-lg bg-white p-6 shadow-sm sm:rounded-lg">
                            <h2 className="font-semibold text-slate-900">
                                Appointments awaiting payment
                            </h2>

                            <div className="mt-4 space-y-3">
                                {appointments.map(
                                    (appointment) => {
                                        const service =
                                            appointment.counselling_service;

                                        const amount =
                                            appointment.fee_amount ??
                                            service?.price ??
                                            0;

                                        const currency =
                                            appointment.fee_currency ??
                                            service?.currency ??
                                            "LKR";

                                        return (
                                            <div
                                                key={
                                                    appointment.id
                                                }
                                                className="flex flex-col gap-4 rounded-lg border border-slate-200 p-4 sm:flex-row sm:items-center sm:justify-between"
                                            >
                                                <div>
                                                    <p className="font-medium text-slate-900">
                                                        {service?.name ??
                                                            "Counselling appointment"}
                                                    </p>

                                                    <p className="mt-1 text-sm text-slate-500">
                                                        {appointment.appointment_date}{" "}
                                                        ·{" "}
                                                        {money(
                                                            amount,
                                                            currency,
                                                        )}
                                                    </p>
                                                </div>

                                                <PrimaryButton
                                                    type="button"
                                                    onClick={() =>
                                                        pay(
                                                            appointment,
                                                        )
                                                    }
                                                >
                                                    Pay now
                                                </PrimaryButton>
                                            </div>
                                        );
                                    },
                                )}
                            </div>
                        </div>
                    )}

                    <div className="overflow-hidden rounded-lg bg-white shadow-sm sm:rounded-lg">
                        <div className="border-b border-slate-200 px-6 py-4">
                            <h2 className="font-semibold text-slate-900">
                                Payment history
                            </h2>
                        </div>

                        {payments.data.length ===
                        0 ? (
                            <div className="p-10 text-center text-sm text-slate-500">
                                No payments yet.
                            </div>
                        ) : (
                            <div className="divide-y divide-slate-100">
                                {payments.data.map(
                                    (payment) => (
                                        <div
                                            key={
                                                payment.uuid
                                            }
                                            className="p-6"
                                        >
                                            <div className="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                                                <div>
                                                    <p className="font-medium text-slate-900">
                                                        {payment
                                                            .appointment
                                                            ?.counselling_service
                                                            ?.name ??
                                                            "Counselling service"}
                                                    </p>

                                                    <div className="mt-1 flex flex-wrap gap-2 text-sm text-slate-500">
                                                        <span>
                                                            {money(
                                                                payment.amount,
                                                                payment.currency,
                                                            )}
                                                        </span>

                                                        <span>
                                                            ·
                                                        </span>

                                                        <span className="capitalize">
                                                            {payment.status.replaceAll(
                                                                "_",
                                                                " ",
                                                            )}
                                                        </span>

                                                        <span>
                                                            ·
                                                        </span>

                                                        <span>
                                                            {payment.provider}
                                                        </span>
                                                    </div>
                                                </div>

                                                <div className="flex flex-wrap gap-2">
                                                    {payment.invoice && (
                                                        <a
                                                            href={route(
                                                                "client.payments.invoice",
                                                                payment.uuid,
                                                            )}
                                                            className="inline-flex items-center gap-2 rounded-md border border-slate-300 px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
                                                        >
                                                            <Download className="h-4 w-4" />
                                                            Invoice
                                                        </a>
                                                    )}

                                                    {payment
                                                        .invoice
                                                        ?.receipt_number && (
                                                        <a
                                                            href={route(
                                                                "client.payments.receipt",
                                                                payment.uuid,
                                                            )}
                                                            className="inline-flex items-center gap-2 rounded-md border border-slate-300 px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
                                                        >
                                                            <Download className="h-4 w-4" />
                                                            Receipt
                                                        </a>
                                                    )}

                                                    {[
                                                        "paid",
                                                        "partially_refunded",
                                                    ].includes(
                                                        payment.status,
                                                    ) && (
                                                        <button
                                                            type="button"
                                                            onClick={() => {
                                                                setRefundPayment(
                                                                    payment,
                                                                );

                                                                refundForm.setData(
                                                                    "requested_amount",
                                                                    payment.amount,
                                                                );
                                                            }}
                                                            className="inline-flex items-center gap-2 rounded-md border border-amber-300 px-3 py-2 text-sm font-medium text-amber-700 hover:bg-amber-50"
                                                        >
                                                            <RotateCcw className="h-4 w-4" />
                                                            Request refund
                                                        </button>
                                                    )}
                                                </div>
                                            </div>

                                            {payment
                                                .refunds
                                                ?.length >
                                                0 && (
                                                <div className="mt-4 rounded-lg bg-slate-50 p-4">
                                                    <p className="text-xs font-semibold uppercase tracking-wide text-slate-500">
                                                        Refunds
                                                    </p>

                                                    <div className="mt-2 space-y-1 text-sm">
                                                        {payment.refunds.map(
                                                            (
                                                                refund,
                                                            ) => (
                                                                <div
                                                                    key={
                                                                        refund.uuid
                                                                    }
                                                                    className="flex justify-between gap-4"
                                                                >
                                                                    <span className="text-slate-600">
                                                                        {
                                                                            refund.refund_number
                                                                        }
                                                                    </span>

                                                                    <span className="capitalize text-slate-700">
                                                                        {refund.status.replaceAll(
                                                                            "_",
                                                                            " ",
                                                                        )}
                                                                    </span>
                                                                </div>
                                                            ),
                                                        )}
                                                    </div>
                                                </div>
                                            )}
                                        </div>
                                    ),
                                )}
                            </div>
                        )}

                        <div className="border-t border-slate-200 px-6 py-4">
                            <Pagination
                                links={
                                    payments.links
                                }
                            />
                        </div>
                    </div>

                    {refundPayment && (
                        <div className="rounded-lg bg-white p-6 shadow-sm sm:rounded-lg">
                            <h2 className="font-semibold text-slate-900">
                                Request refund
                            </h2>

                            <form
                                className="mt-4 space-y-4"
                                onSubmit={
                                    requestRefund
                                }
                            >
                                <div>
                                    <label className="text-sm font-medium text-slate-700">
                                        Amount
                                    </label>

                                    <input
                                        type="number"
                                        min="0.01"
                                        step="0.01"
                                        value={
                                            refundForm
                                                .data
                                                .requested_amount
                                        }
                                        onChange={(
                                            event,
                                        ) =>
                                            refundForm.setData(
                                                "requested_amount",
                                                event
                                                    .target
                                                    .value,
                                            )
                                        }
                                        className="mt-1 block w-full rounded-md border-slate-300"
                                    />

                                    <InputError
                                        className="mt-2"
                                        message={
                                            refundForm
                                                .errors
                                                .requested_amount
                                        }
                                    />
                                </div>

                                <div>
                                    <label className="text-sm font-medium text-slate-700">
                                        Reason
                                    </label>

                                    <textarea
                                        rows="4"
                                        value={
                                            refundForm
                                                .data
                                                .reason
                                        }
                                        onChange={(
                                            event,
                                        ) =>
                                            refundForm.setData(
                                                "reason",
                                                event
                                                    .target
                                                    .value,
                                            )
                                        }
                                        className="mt-1 block w-full rounded-md border-slate-300"
                                    />

                                    <InputError
                                        className="mt-2"
                                        message={
                                            refundForm
                                                .errors
                                                .reason
                                        }
                                    />
                                </div>

                                <div className="flex gap-3">
                                    <PrimaryButton
                                        disabled={
                                            refundForm.processing
                                        }
                                    >
                                        Submit request
                                    </PrimaryButton>

                                    <button
                                        type="button"
                                        onClick={() =>
                                            setRefundPayment(
                                                null,
                                            )
                                        }
                                        className="rounded-md border border-slate-300 px-4 py-2 text-sm"
                                    >
                                        Cancel
                                    </button>
                                </div>
                            </form>
                        </div>
                    )}
                </div>
            </div>
        </ClientLayout>
    );
}
