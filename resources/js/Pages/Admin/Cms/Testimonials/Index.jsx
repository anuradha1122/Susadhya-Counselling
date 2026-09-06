import InputError from "@/Components/InputError";
import Pagination from "@/Components/Pagination";
import AdminLayout from "@/Layouts/AdminLayout";
import CmsTabs from "@/Pages/Admin/Cms/Components/CmsTabs";
import { Head, router, useForm } from "@inertiajs/react";

function TestimonialForm({
    testimonial = null,
}) {
    const creating = testimonial === null;

    const form = useForm({
        display_name:
            testimonial?.display_name ?? "",
        role_label:
            testimonial?.role_label ?? "",
        quote:
            testimonial?.quote ?? "",
        rating:
            testimonial?.rating ?? 5,
        image_path:
            testimonial?.image_path ?? "",
        is_featured:
            Boolean(
                testimonial?.is_featured,
            ),
        is_active:
            Boolean(
                testimonial?.is_active,
            ),
        display_order:
            testimonial?.display_order ?? 10,
        consent_confirmed:
            Boolean(
                testimonial?.consent_confirmed,
            ),
    });

    const submit = (event) => {
        event.preventDefault();

        if (creating) {
            form.post(
                route(
                    "admin.cms.testimonials.store",
                ),
                {
                    preserveScroll: true,
                    onSuccess: () =>
                        form.reset(),
                },
            );

            return;
        }

        form.patch(
            route(
                "admin.cms.testimonials.update",
                testimonial.uuid,
            ),
            { preserveScroll: true },
        );
    };

    return (
        <form
            onSubmit={submit}
            className={`rounded-xl border p-5 shadow-sm ${
                creating
                    ? "border-indigo-200 bg-indigo-50"
                    : "border-slate-200 bg-white"
            }`}
        >
            <div className="grid gap-4 md:grid-cols-2">
                <input
                    value={form.data.display_name}
                    onChange={(event) =>
                        form.setData(
                            "display_name",
                            event.target.value,
                        )
                    }
                    placeholder="Public display name"
                    className="rounded-md border-slate-300"
                />

                <input
                    value={form.data.role_label}
                    onChange={(event) =>
                        form.setData(
                            "role_label",
                            event.target.value,
                        )
                    }
                    placeholder="Optional role label"
                    className="rounded-md border-slate-300"
                />
            </div>

            <textarea
                rows="5"
                value={form.data.quote}
                onChange={(event) =>
                    form.setData(
                        "quote",
                        event.target.value,
                    )
                }
                placeholder="Testimonial"
                className="mt-4 block w-full rounded-md border-slate-300"
            />

            <div className="mt-4 grid gap-4 md:grid-cols-2">
                <input
                    type="number"
                    min="1"
                    max="5"
                    value={form.data.rating}
                    onChange={(event) =>
                        form.setData(
                            "rating",
                            Number(event.target.value),
                        )
                    }
                    className="rounded-md border-slate-300"
                />

                <input
                    type="number"
                    min="0"
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

            <div className="mt-4 space-y-3">
                <label className="flex items-start gap-3">
                    <input
                        type="checkbox"
                        checked={
                            form.data
                                .consent_confirmed
                        }
                        onChange={(event) =>
                            form.setData(
                                "consent_confirmed",
                                event.target.checked,
                            )
                        }
                        className="mt-1 rounded border-slate-300 text-indigo-600"
                    />

                    <span className="text-sm text-slate-700">
                        I confirm that explicit permission
                        to publish this testimonial has
                        been obtained.
                    </span>
                </label>

                <InputError
                    message={
                        form.errors
                            .consent_confirmed
                    }
                />

                <label className="flex items-center gap-3">
                    <input
                        type="checkbox"
                        checked={
                            form.data.is_active
                        }
                        onChange={(event) =>
                            form.setData(
                                "is_active",
                                event.target.checked,
                            )
                        }
                        className="rounded border-slate-300 text-indigo-600"
                    />

                    <span className="text-sm">
                        Publish
                    </span>
                </label>

                <label className="flex items-center gap-3">
                    <input
                        type="checkbox"
                        checked={
                            form.data
                                .is_featured
                        }
                        onChange={(event) =>
                            form.setData(
                                "is_featured",
                                event.target.checked,
                            )
                        }
                        className="rounded border-slate-300 text-indigo-600"
                    />

                    <span className="text-sm">
                        Feature on homepage
                    </span>
                </label>
            </div>

            <div className="mt-4 flex gap-3">
                <button className="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white">
                    {creating
                        ? "Add testimonial"
                        : "Save testimonial"}
                </button>

                {!creating && (
                    <button
                        type="button"
                        onClick={() =>
                            router.post(
                                route(
                                    "admin.cms.testimonials.archive",
                                    testimonial.uuid,
                                ),
                            )
                        }
                        className="rounded-md border border-rose-300 px-4 py-2 text-sm text-rose-700"
                    >
                        Archive
                    </button>
                )}
            </div>
        </form>
    );
}

export default function Index({
    testimonials,
}) {
    return (
        <AdminLayout title="CMS Testimonials">
            <Head title="CMS Testimonials" />

            <div className="mx-auto max-w-7xl space-y-6">
                <CmsTabs />

                <div className="rounded-xl border border-amber-200 bg-amber-50 p-5 text-sm leading-6 text-amber-800">
                    Testimonials must never be generated automatically from
                    counselling notes, feedback or clinical records.
                    Publication requires explicit consent confirmation.
                </div>

                <TestimonialForm />

                <div className="space-y-4">
                    {testimonials.data.map(
                        (testimonial) => (
                            <TestimonialForm
                                key={
                                    testimonial.uuid
                                }
                                testimonial={
                                    testimonial
                                }
                            />
                        ),
                    )}
                </div>

                <Pagination
                    links={testimonials.links}
                />
            </div>
        </AdminLayout>
    );
}
