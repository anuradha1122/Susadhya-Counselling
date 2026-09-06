import InputError from "@/Components/InputError";
import Pagination from "@/Components/Pagination";
import AdminLayout from "@/Layouts/AdminLayout";
import CmsTabs from "@/Pages/Admin/Cms/Components/CmsTabs";
import { Head, router, useForm } from "@inertiajs/react";

function FaqCard({ faq }) {
    const form = useForm({
        category: faq.category ?? "",
        question: faq.question ?? "",
        answer: faq.answer ?? "",
        display_order: faq.display_order ?? 0,
        is_active: Boolean(faq.is_active),
    });

    const submit = (event) => {
        event.preventDefault();

        form.patch(
            route("admin.cms.faqs.update", faq.uuid),
            { preserveScroll: true },
        );
    };

    return (
        <form
            onSubmit={submit}
            className="rounded-xl border border-slate-200 bg-white p-5 shadow-sm"
        >
            <div className="grid gap-4 md:grid-cols-2">
                <input
                    value={form.data.category}
                    onChange={(event) =>
                        form.setData("category", event.target.value)
                    }
                    placeholder="Category"
                    className="rounded-md border-slate-300"
                />

                <input
                    type="number"
                    value={form.data.display_order}
                    onChange={(event) =>
                        form.setData(
                            "display_order",
                            Number(event.target.value),
                        )
                    }
                    className="rounded-md border-slate-300"
                />
            </div>

            <input
                value={form.data.question}
                onChange={(event) =>
                    form.setData("question", event.target.value)
                }
                className="mt-4 block w-full rounded-md border-slate-300"
            />

            <textarea
                rows="5"
                value={form.data.answer}
                onChange={(event) =>
                    form.setData("answer", event.target.value)
                }
                className="mt-4 block w-full rounded-md border-slate-300"
            />

            <InputError
                message={form.errors.answer}
                className="mt-2"
            />

            <div className="mt-4 flex flex-wrap gap-3">
                <button className="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white">
                    Save FAQ
                </button>

                <button
                    type="button"
                    onClick={() =>
                        router.post(
                            route(
                                "admin.cms.faqs.toggle",
                                faq.uuid,
                            ),
                            {},
                            { preserveScroll: true },
                        )
                    }
                    className="rounded-md border border-slate-300 px-4 py-2 text-sm"
                >
                    {faq.is_active ? "Hide" : "Publish"}
                </button>
            </div>
        </form>
    );
}

export default function Index({ faqs }) {
    const form = useForm({
        category: "General",
        question: "",
        answer: "",
        display_order: 10,
        is_active: true,
    });

    const create = (event) => {
        event.preventDefault();

        form.post(route("admin.cms.faqs.store"), {
            preserveScroll: true,
            onSuccess: () =>
                form.reset(
                    "question",
                    "answer",
                ),
        });
    };

    return (
        <AdminLayout title="CMS FAQs">
            <Head title="CMS FAQs" />

            <div className="mx-auto max-w-7xl space-y-6">
                <CmsTabs />

                <form
                    onSubmit={create}
                    className="rounded-xl border border-indigo-200 bg-indigo-50 p-5"
                >
                    <h3 className="font-semibold text-slate-900">
                        Add FAQ
                    </h3>

                    <div className="mt-4 grid gap-4 md:grid-cols-2">
                        <input
                            value={form.data.category}
                            onChange={(event) =>
                                form.setData(
                                    "category",
                                    event.target.value,
                                )
                            }
                            placeholder="Category"
                            className="rounded-md border-slate-300"
                        />

                        <input
                            type="number"
                            value={form.data.display_order}
                            onChange={(event) =>
                                form.setData(
                                    "display_order",
                                    Number(event.target.value),
                                )
                            }
                            className="rounded-md border-slate-300"
                        />
                    </div>

                    <input
                        value={form.data.question}
                        onChange={(event) =>
                            form.setData(
                                "question",
                                event.target.value,
                            )
                        }
                        placeholder="Question"
                        className="mt-4 block w-full rounded-md border-slate-300"
                    />

                    <textarea
                        rows="5"
                        value={form.data.answer}
                        onChange={(event) =>
                            form.setData(
                                "answer",
                                event.target.value,
                            )
                        }
                        placeholder="Answer"
                        className="mt-4 block w-full rounded-md border-slate-300"
                    />

                    <button className="mt-4 rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white">
                        Add FAQ
                    </button>
                </form>

                <div className="space-y-4">
                    {faqs.data.map((faq) => (
                        <FaqCard
                            key={faq.uuid}
                            faq={faq}
                        />
                    ))}
                </div>

                <Pagination links={faqs.links} />
            </div>
        </AdminLayout>
    );
}
