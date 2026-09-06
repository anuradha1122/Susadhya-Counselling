import InputError from "@/Components/InputError";
import { useForm } from "@inertiajs/react";
import {
    LifeBuoy,
    Send,
    ShieldCheck,
} from "lucide-react";

const categories = [
    {
        value: "general",
        label: "General enquiry",
    },
    {
        value: "technical",
        label: "Technical support",
    },
    {
        value: "appointment",
        label: "Appointment support",
    },
    {
        value: "account",
        label: "Account support",
    },
    {
        value: "complaint",
        label: "Complaint",
    },
];

export default function SupportContactForm() {
    const {
        data,
        setData,
        post,
        processing,
        errors,
        reset,
        recentlySuccessful,
    } = useForm({
        name: "",
        email: "",
        category: "general",
        subject: "",
        message: "",
        privacy_acknowledged: false,

        // Honeypot.
        website: "",
    });

    const submit = (event) => {
        event.preventDefault();

        post(
            route(
                "public.contact.store",
            ),
            {
                preserveScroll: true,

                onSuccess: () => {
                    reset(
                        "subject",
                        "message",
                        "website",
                    );
                },
            },
        );
    };

    return (
        <section className="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8 lg:p-10">
            <div className="flex items-start gap-4">
                <div className="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-[#E6F2FA] text-[#2A9D8F]">
                    <LifeBuoy className="h-6 w-6" />
                </div>

                <div>
                    <p className="text-xs font-semibold uppercase tracking-[0.18em] text-[#2A9D8F]">
                        Contact Support
                    </p>

                    <h2 className="susadhya-heading mt-2 text-3xl font-bold text-[#1A365D]">
                        How can we help?
                    </h2>

                    <p className="mt-3 max-w-2xl text-sm leading-7 text-slate-600">
                        Send an operational
                        enquiry, technical
                        question, appointment
                        issue or complaint to
                        the Susadhya support
                        team.
                    </p>
                </div>
            </div>

            {recentlySuccessful && (
                <div className="mt-6 rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm leading-6 text-emerald-800">
                    Your message has been
                    received successfully.
                </div>
            )}

            <div className="mt-6 rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm leading-6 text-amber-900">
                <strong>
                    Important:
                </strong>{" "}
                this form is for support
                requests, not counselling
                emergencies. Please do not
                include counselling notes,
                detailed clinical information,
                passwords or payment
                credentials.
            </div>

            <form
                onSubmit={submit}
                className="mt-8 space-y-6"
            >
                {/* Honeypot */}
                <div
                    className="hidden"
                    aria-hidden="true"
                >
                    <label htmlFor="website">
                        Website
                    </label>

                    <input
                        id="website"
                        tabIndex="-1"
                        autoComplete="off"
                        value={data.website}
                        onChange={(event) =>
                            setData(
                                "website",
                                event.target.value,
                            )
                        }
                    />
                </div>

                <div className="grid gap-5 md:grid-cols-2">
                    <div>
                        <label
                            htmlFor="support_name"
                            className="text-sm font-semibold text-[#1A365D]"
                        >
                            Your name
                        </label>

                        <input
                            id="support_name"
                            value={data.name}
                            onChange={(event) =>
                                setData(
                                    "name",
                                    event.target.value,
                                )
                            }
                            className="mt-2 block w-full rounded-xl border-slate-300 text-sm focus:border-[#2A9D8F] focus:ring-[#2A9D8F]"
                            autoComplete="name"
                        />

                        <InputError
                            className="mt-2"
                            message={errors.name}
                        />
                    </div>

                    <div>
                        <label
                            htmlFor="support_email"
                            className="text-sm font-semibold text-[#1A365D]"
                        >
                            Email address
                        </label>

                        <input
                            id="support_email"
                            type="email"
                            value={data.email}
                            onChange={(event) =>
                                setData(
                                    "email",
                                    event.target.value,
                                )
                            }
                            className="mt-2 block w-full rounded-xl border-slate-300 text-sm focus:border-[#2A9D8F] focus:ring-[#2A9D8F]"
                            autoComplete="email"
                        />

                        <InputError
                            className="mt-2"
                            message={errors.email}
                        />
                    </div>
                </div>

                <div>
                    <label
                        htmlFor="support_category"
                        className="text-sm font-semibold text-[#1A365D]"
                    >
                        Type of request
                    </label>

                    <select
                        id="support_category"
                        value={data.category}
                        onChange={(event) =>
                            setData(
                                "category",
                                event.target.value,
                            )
                        }
                        className="mt-2 block w-full rounded-xl border-slate-300 text-sm focus:border-[#2A9D8F] focus:ring-[#2A9D8F]"
                    >
                        {categories.map(
                            (category) => (
                                <option
                                    key={
                                        category.value
                                    }
                                    value={
                                        category.value
                                    }
                                >
                                    {
                                        category.label
                                    }
                                </option>
                            ),
                        )}
                    </select>

                    <InputError
                        className="mt-2"
                        message={
                            errors.category
                        }
                    />
                </div>

                <div>
                    <label
                        htmlFor="support_subject"
                        className="text-sm font-semibold text-[#1A365D]"
                    >
                        Subject
                    </label>

                    <input
                        id="support_subject"
                        value={data.subject}
                        onChange={(event) =>
                            setData(
                                "subject",
                                event.target.value,
                            )
                        }
                        className="mt-2 block w-full rounded-xl border-slate-300 text-sm focus:border-[#2A9D8F] focus:ring-[#2A9D8F]"
                    />

                    <InputError
                        className="mt-2"
                        message={errors.subject}
                    />
                </div>

                <div>
                    <label
                        htmlFor="support_message"
                        className="text-sm font-semibold text-[#1A365D]"
                    >
                        Message
                    </label>

                    <textarea
                        id="support_message"
                        rows="7"
                        value={data.message}
                        onChange={(event) =>
                            setData(
                                "message",
                                event.target.value,
                            )
                        }
                        className="mt-2 block w-full rounded-xl border-slate-300 text-sm leading-6 focus:border-[#2A9D8F] focus:ring-[#2A9D8F]"
                    />

                    <InputError
                        className="mt-2"
                        message={errors.message}
                    />
                </div>

                <div className="rounded-2xl bg-[#F7F9F4] p-4">
                    <label className="flex items-start gap-3">
                        <input
                            type="checkbox"
                            checked={
                                data.privacy_acknowledged
                            }
                            onChange={(
                                event,
                            ) =>
                                setData(
                                    "privacy_acknowledged",
                                    event.target
                                        .checked,
                                )
                            }
                            className="mt-1 rounded border-slate-300 text-[#2A9D8F] focus:ring-[#2A9D8F]"
                        />

                        <span className="text-sm leading-6 text-slate-600">
                            I understand that
                            this is an
                            administrative
                            support channel and
                            should not be used
                            for emergencies or
                            detailed confidential
                            counselling records.
                        </span>
                    </label>

                    <InputError
                        className="mt-2"
                        message={
                            errors.privacy_acknowledged
                        }
                    />
                </div>

                <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div className="flex items-center gap-2 text-xs leading-5 text-slate-500">
                        <ShieldCheck className="h-4 w-4 text-[#2A9D8F]" />

                        Your request is
                        handled through the
                        secure Susadhya support
                        workflow.
                    </div>

                    <button
                        type="submit"
                        disabled={processing}
                        className="inline-flex items-center justify-center gap-2 rounded-full bg-[#2A9D8F] px-6 py-3 text-sm font-semibold text-white transition hover:bg-[#23897D] disabled:cursor-not-allowed disabled:opacity-60"
                    >
                        <Send className="h-4 w-4" />

                        {processing
                            ? "Sending..."
                            : "Send message"}
                    </button>
                </div>
            </form>
        </section>
    );
}
