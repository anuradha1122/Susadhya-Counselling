import PrimaryButton from "@/Components/PrimaryButton";
import ClientLayout from "@/Layouts/ClientLayout";
import {
    Head,
    router,
} from "@inertiajs/react";
import {
    CircleCheck,
    CircleX,
    ShieldCheck,
} from "lucide-react";

export default function Checkout({
    payment,
}) {
    const submit = (result) => {
        router.post(
            route(
                "client.payments.sandbox.complete",
                payment.uuid,
            ),
            {
                result,
            },
        );
    };

    return (
        <ClientLayout title="Sandbox Payment">
            <Head title="Sandbox Payment" />

            <div className="py-12">
                <div className="mx-auto max-w-2xl space-y-6 sm:px-6 lg:px-8">
                    <div className="rounded-lg bg-white p-6 shadow-sm">
                        <div className="flex items-start gap-4">
                            <div className="rounded-lg bg-indigo-50 p-3 text-indigo-600">
                                <ShieldCheck className="h-6 w-6" />
                            </div>

                            <div>
                                <h1 className="text-xl font-semibold text-slate-900">
                                    Sandbox gateway
                                </h1>

                                <p className="mt-1 text-sm text-slate-600">
                                    Development-only payment
                                    simulator. No real money is
                                    transferred.
                                </p>
                            </div>
                        </div>

                        <dl className="mt-6 divide-y divide-slate-100 rounded-lg border border-slate-200">
                            <div className="flex justify-between p-4">
                                <dt className="text-sm text-slate-500">
                                    Service
                                </dt>
                                <dd className="text-sm font-medium text-slate-900">
                                    {payment
                                        .appointment
                                        ?.counselling_service
                                        ?.name ??
                                        "Counselling service"}
                                </dd>
                            </div>

                            <div className="flex justify-between p-4">
                                <dt className="text-sm text-slate-500">
                                    Amount
                                </dt>
                                <dd className="text-sm font-medium text-slate-900">
                                    {payment.currency}{" "}
                                    {Number(
                                        payment.amount,
                                    ).toFixed(2)}
                                </dd>
                            </div>
                        </dl>

                        <div className="mt-6 grid gap-3 sm:grid-cols-2">
                            <PrimaryButton
                                type="button"
                                onClick={() =>
                                    submit("success")
                                }
                                className="justify-center"
                            >
                                <CircleCheck className="mr-2 h-4 w-4" />
                                Simulate success
                            </PrimaryButton>

                            <button
                                type="button"
                                onClick={() =>
                                    submit("failed")
                                }
                                className="inline-flex items-center justify-center rounded-md border border-rose-300 px-4 py-2 text-sm font-medium text-rose-700 hover:bg-rose-50"
                            >
                                <CircleX className="mr-2 h-4 w-4" />
                                Simulate failure
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </ClientLayout>
    );
}
