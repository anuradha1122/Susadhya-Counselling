import Pagination from "@/Components/Pagination";
import FinanceLayout from "@/Layouts/FinanceLayout";
import {
    Head,
    router,
} from "@inertiajs/react";

export default function Index({
    refunds,
    filters,
}) {
    const decide = (
        refund,
        decision,
    ) => {
        const approvedAmount =
            decision === "approve"
                ? window.prompt(
                      "Approved refund amount:",
                      refund.requested_amount,
                  )
                : null;

        if (
            decision === "approve" &&
            !approvedAmount
        ) {
            return;
        }

        const notes =
            window.prompt(
                decision === "reject"
                    ? "Reason for rejection:"
                    : "Decision notes (optional):",
                "",
            );

        if (
            decision === "reject" &&
            !notes
        ) {
            return;
        }

        router.patch(
            route(
                "finance.refunds.decide",
                refund.uuid,
            ),
            {
                decision,
                approved_amount:
                    approvedAmount,
                decision_notes:
                    notes ?? "",
            },
            {
                preserveScroll: true,
            },
        );
    };

    const processRefund = (refund) => {
        let manualReference = "";

        if (
            refund.payment.provider ===
            "manual"
        ) {
            manualReference =
                window.prompt(
                    "Manual refund reference:",
                    "",
                ) ?? "";

            if (!manualReference) {
                return;
            }
        }

        router.patch(
            route(
                "finance.refunds.process",
                refund.uuid,
            ),
            {
                manual_reference:
                    manualReference,
            },
            {
                preserveScroll: true,
            },
        );
    };

    return (
        <FinanceLayout title="Refunds">
            <Head title="Refunds" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                    <div className="rounded-lg bg-white p-6 shadow-sm">
                        <h1 className="text-xl font-semibold text-slate-900">
                            Refund management
                        </h1>

                        <p className="mt-1 text-sm text-slate-600">
                            Review client requests,
                            approve or reject them,
                            and record provider refund
                            completion.
                        </p>
                    </div>

                    <div className="overflow-hidden rounded-lg bg-white shadow-sm">
                        <div className="divide-y divide-slate-100">
                            {refunds.data.map(
                                (refund) => (
                                    <div
                                        key={
                                            refund.uuid
                                        }
                                        className="p-6"
                                    >
                                        <div className="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                                            <div>
                                                <p className="font-semibold text-slate-900">
                                                    {
                                                        refund.refund_number
                                                    }
                                                </p>

                                                <p className="mt-1 text-sm text-slate-600">
                                                    {refund
                                                        .payment
                                                        ?.client_profile
                                                        ?.user
                                                        ?.name ??
                                                        "Client"}{" "}
                                                    ·{" "}
                                                    {
                                                        refund.currency
                                                    }{" "}
                                                    {Number(
                                                        refund.requested_amount,
                                                    ).toFixed(
                                                        2,
                                                    )}
                                                </p>

                                                <p className="mt-3 max-w-2xl text-sm text-slate-600">
                                                    {
                                                        refund.reason
                                                    }
                                                </p>

                                                <p className="mt-2 text-xs font-medium uppercase text-slate-500">
                                                    Status:{" "}
                                                    {refund.status.replaceAll(
                                                        "_",
                                                        " ",
                                                    )}
                                                </p>
                                            </div>

                                            <div className="flex flex-wrap gap-2">
                                                {refund.status ===
                                                    "requested" && (
                                                    <>
                                                        <button
                                                            type="button"
                                                            onClick={() =>
                                                                decide(
                                                                    refund,
                                                                    "approve",
                                                                )
                                                            }
                                                            className="rounded-md bg-emerald-600 px-3 py-2 text-sm font-medium text-white"
                                                        >
                                                            Approve
                                                        </button>

                                                        <button
                                                            type="button"
                                                            onClick={() =>
                                                                decide(
                                                                    refund,
                                                                    "reject",
                                                                )
                                                            }
                                                            className="rounded-md bg-rose-600 px-3 py-2 text-sm font-medium text-white"
                                                        >
                                                            Reject
                                                        </button>
                                                    </>
                                                )}

                                                {refund.status ===
                                                    "approved" && (
                                                    <button
                                                        type="button"
                                                        onClick={() =>
                                                            processRefund(
                                                                refund,
                                                            )
                                                        }
                                                        className="rounded-md bg-indigo-600 px-3 py-2 text-sm font-medium text-white"
                                                    >
                                                        Process refund
                                                    </button>
                                                )}
                                            </div>
                                        </div>
                                    </div>
                                ),
                            )}

                            {refunds.data.length ===
                                0 && (
                                <div className="p-10 text-center text-sm text-slate-500">
                                    No refund requests
                                    found.
                                </div>
                            )}
                        </div>

                        <div className="border-t border-slate-200 p-4">
                            <Pagination
                                links={
                                    refunds.links
                                }
                            />
                        </div>
                    </div>
                </div>
            </div>
        </FinanceLayout>
    );
}
