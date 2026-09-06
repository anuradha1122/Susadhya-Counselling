import InputError from "@/Components/InputError";
import ClientLayout from "@/Layouts/ClientLayout";
import {
    Head,
    Link,
    useForm,
} from "@inertiajs/react";
import { Star } from "lucide-react";

export default function Feedback({
    appointments,
}) {
    const {
        data,
        setData,
        post,
        processing,
        errors,
        reset,
    } = useForm({
        appointment_uuid:
            appointments[0]?.uuid ??
            "",
        overall_rating: 5,
        technical_rating: "",
        comment: "",
        would_recommend: true,
        consent_to_follow_up: false,
    });

    const submit = (event) => {
        event.preventDefault();

        if (!data.appointment_uuid) {
            return;
        }

        post(
            route(
                "client.support.feedback.store",
                data.appointment_uuid,
            ),
            {
                preserveScroll: true,

                onSuccess: () => {
                    reset(
                        "comment",
                    );
                },
            },
        );
    };

    return (
        <ClientLayout>
            <Head title="Session Feedback" />

            <div className="mx-auto max-w-3xl space-y-6">
                <div>
                    <Link
                        href={route(
                            "client.support.index",
                        )}
                        className="text-sm font-medium text-indigo-600"
                    >
                        ← Back to Support
                    </Link>

                    <div className="mt-3 flex items-center gap-2">
                        <Star className="h-5 w-5 text-amber-500" />

                        <h1 className="text-2xl font-bold text-slate-900">
                            Session Feedback
                        </h1>
                    </div>

                    <p className="mt-2 text-sm leading-6 text-slate-600">
                        Feedback is used for
                        service improvement. It
                        is not automatically
                        published as a public
                        testimonial.
                    </p>
                </div>

                {appointments.length ===
                0 ? (
                    <div className="rounded-xl border border-slate-200 bg-white p-8 text-center shadow-sm">
                        <p className="text-sm text-slate-600">
                            There are currently
                            no completed sessions
                            awaiting feedback.
                        </p>
                    </div>
                ) : (
                    <form
                        onSubmit={submit}
                        className="space-y-6 rounded-xl border border-slate-200 bg-white p-6 shadow-sm"
                    >
                        <div>
                            <label className="text-sm font-medium text-slate-700">
                                Completed
                                session
                            </label>

                            <select
                                value={
                                    data.appointment_uuid
                                }
                                onChange={(
                                    event,
                                ) =>
                                    setData(
                                        "appointment_uuid",
                                        event
                                            .target
                                            .value,
                                    )
                                }
                                className="mt-1 block w-full rounded-lg border-slate-300"
                            >
                                {appointments.map(
                                    (
                                        appointment,
                                    ) => (
                                        <option
                                            key={
                                                appointment.uuid
                                            }
                                            value={
                                                appointment.uuid
                                            }
                                        >
                                            {
                                                appointment.appointment_date
                                            }
                                            {" — "}
                                            {
                                                appointment.counsellor
                                            }
                                            {" — "}
                                            {
                                                appointment.service
                                            }
                                        </option>
                                    ),
                                )}
                            </select>
                        </div>

                        <div>
                            <label className="text-sm font-medium text-slate-700">
                                Overall rating
                            </label>

                            <select
                                value={
                                    data.overall_rating
                                }
                                onChange={(
                                    event,
                                ) =>
                                    setData(
                                        "overall_rating",
                                        Number(
                                            event
                                                .target
                                                .value,
                                        ),
                                    )
                                }
                                className="mt-1 block w-full rounded-lg border-slate-300"
                            >
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
                                            {
                                                rating
                                            }{" "}
                                            / 5
                                        </option>
                                    ),
                                )}
                            </select>

                            <InputError
                                className="mt-2"
                                message={
                                    errors.overall_rating
                                }
                            />
                        </div>

                        <div>
                            <label className="text-sm font-medium text-slate-700">
                                Technical /
                                platform
                                experience
                            </label>

                            <select
                                value={
                                    data.technical_rating
                                }
                                onChange={(
                                    event,
                                ) =>
                                    setData(
                                        "technical_rating",
                                        event
                                            .target
                                            .value ===
                                            ""
                                            ? ""
                                            : Number(
                                                  event
                                                      .target
                                                      .value,
                                              ),
                                    )
                                }
                                className="mt-1 block w-full rounded-lg border-slate-300"
                            >
                                <option value="">
                                    Not rated
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
                                            {
                                                rating
                                            }{" "}
                                            / 5
                                        </option>
                                    ),
                                )}
                            </select>
                        </div>

                        <div>
                            <label className="text-sm font-medium text-slate-700">
                                Comments
                            </label>

                            <textarea
                                rows="6"
                                value={
                                    data.comment
                                }
                                onChange={(
                                    event,
                                ) =>
                                    setData(
                                        "comment",
                                        event
                                            .target
                                            .value,
                                    )
                                }
                                className="mt-1 block w-full rounded-lg border-slate-300"
                            />

                            <InputError
                                className="mt-2"
                                message={
                                    errors.comment
                                }
                            />
                        </div>

                        <label className="flex items-center gap-3 text-sm text-slate-700">
                            <input
                                type="checkbox"
                                checked={
                                    data.would_recommend
                                }
                                onChange={(
                                    event,
                                ) =>
                                    setData(
                                        "would_recommend",
                                        event
                                            .target
                                            .checked,
                                    )
                                }
                                className="rounded border-slate-300 text-indigo-600"
                            />

                            I would recommend
                            Susadhya to someone
                            seeking professional
                            counselling support.
                        </label>

                        <label className="flex items-center gap-3 text-sm text-slate-700">
                            <input
                                type="checkbox"
                                checked={
                                    data.consent_to_follow_up
                                }
                                onChange={(
                                    event,
                                ) =>
                                    setData(
                                        "consent_to_follow_up",
                                        event
                                            .target
                                            .checked,
                                    )
                                }
                                className="rounded border-slate-300 text-indigo-600"
                            />

                            I consent to the
                            Susadhya team
                            contacting me about
                            this feedback.
                        </label>

                        <InputError
                            message={
                                errors.appointment
                            }
                        />

                        <button
                            disabled={
                                processing
                            }
                            className="rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white disabled:opacity-60"
                        >
                            {processing
                                ? "Submitting..."
                                : "Submit Feedback"}
                        </button>
                    </form>
                )}
            </div>
        </ClientLayout>
    );
}
