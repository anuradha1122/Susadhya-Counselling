import AdminLayout from "@/Layouts/AdminLayout";
import CmsTabs from "@/Pages/Admin/Cms/Components/CmsTabs";
import FixedSectionForm from "@/Pages/Admin/Cms/Components/FixedSectionForm";
import {
    Head,
    Link,
    useForm,
} from "@inertiajs/react";
import { Save } from "lucide-react";

export default function Edit({
    pageDefinition,
    page,
    pages,
    sections,
    media,
}) {
    const pageForm =
        useForm({
            title:
                page.title ?? "",

            excerpt:
                page.excerpt ?? "",

            body:
                page.body ?? "",

            meta_title:
                page.meta_title ??
                "",

            meta_description:
                page.meta_description ??
                "",

            og_image_path:
                page.og_image_path ??
                "",

            robots_index:
                Boolean(
                    page.robots_index,
                ),

            robots_follow:
                Boolean(
                    page.robots_follow,
                ),
        });

    const savePage = (
        event,
    ) => {
        event.preventDefault();

        pageForm.patch(
            route(
                "admin.cms.content.page.update",
                pageDefinition.slug,
            ),
            {
                preserveScroll: true,
            },
        );
    };

    return (
        <AdminLayout
            title={`${pageDefinition.label} Content`}
        >
            <Head
                title={`${pageDefinition.label} Content`}
            />

            <div className="mx-auto max-w-7xl space-y-6">
                <CmsTabs />

                <div className="flex flex-wrap gap-2">
                    {pages.map(
                        (item) => (
                            <Link
                                key={
                                    item.slug
                                }
                                href={route(
                                    "admin.cms.content.edit",
                                    item.slug,
                                )}
                                className={`rounded-lg px-4 py-2 text-sm font-medium ${
                                    item.slug ===
                                    pageDefinition.slug
                                        ? "bg-indigo-600 text-white"
                                        : "border border-slate-200 bg-white text-slate-600 hover:border-indigo-300"
                                }`}
                            >
                                {
                                    item.label
                                }
                            </Link>
                        ),
                    )}
                </div>

                <div>
                    <h1 className="text-2xl font-semibold text-slate-900">
                        {
                            pageDefinition.label
                        }{" "}
                        Page
                    </h1>

                    <p className="mt-1 text-sm text-slate-500">
                        Change text and
                        images. The
                        layout is fixed
                        and protected.
                    </p>
                </div>

                <form
                    onSubmit={
                        savePage
                    }
                    className="rounded-xl border border-slate-200 bg-white p-6 shadow-sm"
                >
                    <h2 className="font-semibold text-slate-900">
                        Page & SEO
                    </h2>

                    <div className="mt-5 grid gap-5">
                        <div>
                            <label className="text-sm font-medium text-slate-700">
                                Page title
                            </label>

                            <input
                                value={
                                    pageForm
                                        .data
                                        .title
                                }
                                onChange={(
                                    event,
                                ) =>
                                    pageForm.setData(
                                        "title",
                                        event
                                            .target
                                            .value,
                                    )
                                }
                                className="mt-1 block w-full rounded-lg border-slate-300"
                            />
                        </div>

                        <div>
                            <label className="text-sm font-medium text-slate-700">
                                Short description
                            </label>

                            <textarea
                                rows="3"
                                value={
                                    pageForm
                                        .data
                                        .excerpt
                                }
                                onChange={(
                                    event,
                                ) =>
                                    pageForm.setData(
                                        "excerpt",
                                        event
                                            .target
                                            .value,
                                    )
                                }
                                className="mt-1 block w-full rounded-lg border-slate-300"
                            />
                        </div>

                        <div className="grid gap-5 md:grid-cols-2">
                            <div>
                                <label className="text-sm font-medium text-slate-700">
                                    SEO title
                                </label>

                                <input
                                    value={
                                        pageForm
                                            .data
                                            .meta_title
                                    }
                                    onChange={(
                                        event,
                                    ) =>
                                        pageForm.setData(
                                            "meta_title",
                                            event
                                                .target
                                                .value,
                                        )
                                    }
                                    className="mt-1 block w-full rounded-lg border-slate-300"
                                />
                            </div>

                            <div>
                                <label className="text-sm font-medium text-slate-700">
                                    SEO description
                                </label>

                                <textarea
                                    rows="3"
                                    value={
                                        pageForm
                                            .data
                                            .meta_description
                                    }
                                    onChange={(
                                        event,
                                    ) =>
                                        pageForm.setData(
                                            "meta_description",
                                            event
                                                .target
                                                .value,
                                        )
                                    }
                                    className="mt-1 block w-full rounded-lg border-slate-300"
                                />
                            </div>
                        </div>
                    </div>

                    <button className="mt-5 inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white">
                        <Save className="h-4 w-4" />
                        Save Page Settings
                    </button>
                </form>

                <div className="space-y-6">
                    {sections.map(
                        (section) => (
                            <FixedSectionForm
                                key={
                                    section.key
                                }
                                page={
                                    pageDefinition.slug
                                }
                                section={
                                    section
                                }
                                media={
                                    media
                                }
                            />
                        ),
                    )}
                </div>
            </div>
        </AdminLayout>
    );
}
