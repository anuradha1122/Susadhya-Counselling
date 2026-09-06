import { Link } from "@inertiajs/react";
import {
    CircleHelp,
    Images,
    LayoutDashboard,
    MessageSquareQuote,
    Settings,
    Type,
} from "lucide-react";

const items = [
    {
        label: "Dashboard",
        routeName:
            "admin.cms.dashboard",
        match:
            "admin.cms.dashboard",
        icon:
            LayoutDashboard,
    },
    {
        label: "Website Content",
        routeName:
            "admin.cms.content.index",
        match:
            "admin.cms.content.*",
        icon:
            Type,
    },
    {
        label: "FAQs",
        routeName:
            "admin.cms.faqs.index",
        match:
            "admin.cms.faqs.*",
        icon:
            CircleHelp,
    },
    {
        label: "Testimonials",
        routeName:
            "admin.cms.testimonials.index",
        match:
            "admin.cms.testimonials.*",
        icon:
            MessageSquareQuote,
    },
    {
        label: "Media",
        routeName:
            "admin.cms.media.index",
        match:
            "admin.cms.media.*",
        icon:
            Images,
    },
    {
        label: "Settings",
        routeName:
            "admin.cms.settings.edit",
        match:
            "admin.cms.settings.*",
        icon:
            Settings,
    },
];

export default function CmsTabs() {
    return (
        <div className="overflow-x-auto rounded-xl border border-slate-200 bg-white p-2 shadow-sm">
            <div className="flex min-w-max gap-1">
                {items.map(
                    (item) => {
                        const Icon =
                            item.icon;

                        const active =
                            route().current(
                                item.match,
                            );

                        return (
                            <Link
                                key={
                                    item.routeName
                                }
                                href={route(
                                    item.routeName,
                                )}
                                className={`inline-flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium ${
                                    active
                                        ? "bg-indigo-50 text-indigo-700"
                                        : "text-slate-600 hover:bg-slate-50 hover:text-slate-900"
                                }`}
                            >
                                <Icon className="h-4 w-4" />

                                {
                                    item.label
                                }
                            </Link>
                        );
                    },
                )}
            </div>
        </div>
    );
}
