import AdminLayout from "@/Layouts/AdminLayout";
import CmsTabs from "@/Pages/Admin/Cms/Components/CmsTabs";
import { Head, Link } from "@inertiajs/react";
import {
    CircleHelp,
    FileText,
    Images,
    MessageSquareQuote,
} from "lucide-react";

const fixedPageSlugs = [
    "home",
    "about",
    "services",
    "counsellors",
    "faq",
    "contact",
];

function StatCard({
    title,
    value,
    icon: Icon,
}) {
    return (
        <div className="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <div className="flex items-center justify-between gap-4">
                <div>
                    <p className="text-sm font-medium text-slate-500">
                        {title}
                    </p>

                    <p className="mt-2 text-3xl font-bold text-slate-900">
                        {value ?? 0}
                    </p>
                </div>

                <div className="rounded-xl bg-indigo-50 p-3 text-indigo-600">
                    <Icon className="h-5 w-5" />
                </div>
            </div>
        </div>
    );
}

export default function Dashboard({
    stats = {},
    recentPages = [],
}) {
    const fixedRecentPages =
        recentPages.filter((page) =>
            fixedPageSlugs.includes(
                page.slug,
            ),
        );

    return (
        <AdminLayout title="Website CMS">
            <Head title="Website CMS" />

            <div className="mx-auto max-w-7xl space-y-6">
                <CmsTabs />

                {/* Header */}
                <div className="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <h2 className="text-xl font-semibold text-slate-900">
                            Public Website CMS
                        </h2>

                        <p className="mt-1 max-w-3xl text-sm leading-6 text-slate-500">
                            Manage website text,
                            images, FAQs,
                            testimonials, media and
                            public-site settings.
                            The website structure
                            and section layout are
                            fixed by the application.
                        </p>
                    </div>

                    <Link
                        href={route(
                            "admin.cms.content.index",
                        )}
                        className="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-700"
                    >
                        Edit Website Content
                    </Link>
                </div>

                {/* Statistics */}
                <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    <StatCard
                        title="Website Pages"
                        value={
                            fixedPageSlugs.length
                        }
                        icon={FileText}
                    />

                    <StatCard
                        title="Published Pages"
                        value={
                            stats.publishedPages ??
                            0
                        }
                        icon={FileText}
                    />

                    <StatCard
                        title="Active FAQs"
                        value={
                            stats.faqs ?? 0
                        }
                        icon={CircleHelp}
                    />

                    <StatCard
                        title="Media Files"
                        value={
                            stats.media ?? 0
                        }
                        icon={Images}
                    />
                </div>

                {/* Main CMS Areas */}
                <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                    <Link
                        href={route(
                            "admin.cms.content.index",
                        )}
                        className="group rounded-xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-indigo-300 hover:shadow-md"
                    >
                        <div className="rounded-xl bg-indigo-50 p-3 text-indigo-600 w-fit">
                            <FileText className="h-5 w-5" />
                        </div>

                        <h3 className="mt-4 font-semibold text-slate-900 group-hover:text-indigo-700">
                            Website Content
                        </h3>

                        <p className="mt-2 text-sm leading-6 text-slate-500">
                            Edit headings,
                            descriptions, images
                            and SEO for the fixed
                            website pages.
                        </p>
                    </Link>

                    <Link
                        href={route(
                            "admin.cms.faqs.index",
                        )}
                        className="group rounded-xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-indigo-300 hover:shadow-md"
                    >
                        <div className="w-fit rounded-xl bg-amber-50 p-3 text-amber-600">
                            <CircleHelp className="h-5 w-5" />
                        </div>

                        <h3 className="mt-4 font-semibold text-slate-900 group-hover:text-indigo-700">
                            FAQs
                        </h3>

                        <p className="mt-2 text-sm leading-6 text-slate-500">
                            Manage public questions
                            and answers displayed on
                            the website.
                        </p>
                    </Link>

                    <Link
                        href={route(
                            "admin.cms.testimonials.index",
                        )}
                        className="group rounded-xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-indigo-300 hover:shadow-md"
                    >
                        <div className="w-fit rounded-xl bg-emerald-50 p-3 text-emerald-600">
                            <MessageSquareQuote className="h-5 w-5" />
                        </div>

                        <h3 className="mt-4 font-semibold text-slate-900 group-hover:text-indigo-700">
                            Testimonials
                        </h3>

                        <p className="mt-2 text-sm leading-6 text-slate-500">
                            Manage testimonials
                            that have appropriate
                            publication consent.
                        </p>
                    </Link>

                    <Link
                        href={route(
                            "admin.cms.media.index",
                        )}
                        className="group rounded-xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-indigo-300 hover:shadow-md"
                    >
                        <div className="w-fit rounded-xl bg-sky-50 p-3 text-sky-600">
                            <Images className="h-5 w-5" />
                        </div>

                        <h3 className="mt-4 font-semibold text-slate-900 group-hover:text-indigo-700">
                            Media Library
                        </h3>

                        <p className="mt-2 text-sm leading-6 text-slate-500">
                            Upload and manage
                            website images used by
                            public pages.
                        </p>
                    </Link>
                </div>

                {/* Recently Edited Fixed Pages */}
                <div className="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                    <div className="flex flex-wrap items-center justify-between gap-4 border-b border-slate-200 px-6 py-4">
                        <div>
                            <h3 className="font-semibold text-slate-900">
                                Recently Edited
                                Website Pages
                            </h3>

                            <p className="mt-1 text-xs text-slate-500">
                                Only fixed public
                                website pages are
                                shown here.
                            </p>
                        </div>

                        <Link
                            href={route(
                                "admin.cms.content.index",
                            )}
                            className="text-sm font-medium text-indigo-600 hover:text-indigo-800"
                        >
                            View Website Content
                        </Link>
                    </div>

                    {fixedRecentPages.length >
                    0 ? (
                        <div className="divide-y divide-slate-100">
                            {fixedRecentPages.map(
                                (page) => (
                                    <Link
                                        key={
                                            page.uuid ??
                                            page.slug
                                        }
                                        href={route(
                                            "admin.cms.content.edit",
                                            page.slug,
                                        )}
                                        className="flex items-center justify-between gap-4 px-6 py-4 transition hover:bg-slate-50"
                                    >
                                        <div className="min-w-0">
                                            <p className="truncate font-medium text-slate-900">
                                                {
                                                    page.title
                                                }
                                            </p>

                                            <p className="mt-1 text-xs text-slate-500">
                                                /
                                                {
                                                    page.slug
                                                }
                                            </p>
                                        </div>

                                        {page.status && (
                                            <span className="shrink-0 rounded-full bg-slate-100 px-3 py-1 text-xs font-medium capitalize text-slate-700">
                                                {
                                                    page.status
                                                }
                                            </span>
                                        )}
                                    </Link>
                                ),
                            )}
                        </div>
                    ) : (
                        <div className="px-6 py-10 text-center">
                            <FileText className="mx-auto h-7 w-7 text-slate-300" />

                            <p className="mt-3 text-sm font-medium text-slate-600">
                                No recently edited
                                fixed pages.
                            </p>

                            <Link
                                href={route(
                                    "admin.cms.content.index",
                                )}
                                className="mt-3 inline-block text-sm font-medium text-indigo-600"
                            >
                                Open Website Content
                            </Link>
                        </div>
                    )}
                </div>

                {/* Testimonials Notice */}
                {(stats.testimonials ??
                    0) > 0 && (
                    <div className="rounded-xl border border-emerald-200 bg-emerald-50 p-5 text-sm text-emerald-800">
                        <div className="flex items-start gap-3 font-medium">
                            <MessageSquareQuote className="mt-0.5 h-4 w-4 shrink-0" />

                            <span>
                                {
                                    stats.testimonials
                                }{" "}
                                consent-approved
                                testimonial
                                {stats.testimonials ===
                                1
                                    ? ""
                                    : "s"}{" "}
                                currently available
                                for publication.
                            </span>
                        </div>
                    </div>
                )}
            </div>
        </AdminLayout>
    );
}
