import AdminLayout from "@/Layouts/AdminLayout";
import CmsTabs from "@/Pages/Admin/Cms/Components/CmsTabs";
import {
    Head,
    Link,
} from "@inertiajs/react";
import {
    FileText,
    Pencil,
} from "lucide-react";

export default function Index({
    pages,
}) {
    return (
        <AdminLayout title="Website Content">
            <Head title="Website Content" />

            <div className="mx-auto max-w-7xl space-y-6">
                <CmsTabs />

                <div>
                    <h2 className="text-xl font-semibold text-slate-900">
                        Website Content
                    </h2>

                    <p className="mt-1 max-w-3xl text-sm leading-6 text-slate-500">
                        The public website design is fixed. Edit text,
                        images and SEO settings without changing the
                        website structure.
                    </p>
                </div>

                <div className="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
                    {pages.map(
                        (page) => (
                            <Link
                                key={
                                    page.slug
                                }
                                href={route(
                                    "admin.cms.content.edit",
                                    page.slug,
                                )}
                                className="group rounded-xl border border-slate-200 bg-white p-6 shadow-sm transition hover:border-indigo-300 hover:shadow-md"
                            >
                                <div className="flex items-start justify-between">
                                    <div className="rounded-xl bg-indigo-50 p-3 text-indigo-600">
                                        <FileText className="h-5 w-5" />
                                    </div>

                                    <Pencil className="h-4 w-4 text-slate-400 group-hover:text-indigo-600" />
                                </div>

                                <h3 className="mt-5 font-semibold text-slate-900">
                                    {
                                        page.label
                                    }
                                </h3>

                                <p className="mt-2 text-sm text-slate-500">
                                    {
                                        page.section_count
                                    }{" "}
                                    fixed content
                                    section(s)
                                </p>
                            </Link>
                        ),
                    )}
                </div>
            </div>
        </AdminLayout>
    );
}
