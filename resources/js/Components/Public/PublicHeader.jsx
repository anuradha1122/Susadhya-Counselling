import { Link, usePage } from "@inertiajs/react";
import {
    ArrowRight,
    Menu,
    X,
} from "lucide-react";
import { useState } from "react";

const navigation = [
    {
        label: "Home",
        href: "/",
    },
    {
        label: "About",
        href: "/about",
    },
    {
        label: "Services",
        href: "/services",
    },
    {
        label: "Counsellors",
        href: "/counsellors",
    },
    {
        label: "FAQ",
        href: "/faq",
    },
    {
        label: "Contact",
        href: "/contact",
    },
];

function isActive(currentUrl, href) {
    if (href === "/") {
        return currentUrl === "/";
    }

    if (
        href === "/services" ||
        href === "/counsellors"
    ) {
        return currentUrl.startsWith(
            href,
        );
    }

    return currentUrl === href;
}

export default function PublicHeader() {
    const {
        auth,
        publicSite,
    } = usePage().props;

    const currentUrl =
        usePage().url.split("?")[0];

    const [open, setOpen] =
        useState(false);

    const logo =
        publicSite?.logo_url ??
        publicSite?.logo_path ??
        "/images/brand/susadhya-logo.jpeg";

    const siteName =
        publicSite?.site_name ??
        "Susadhya Counselling";

    const bookingUrl =
        publicSite?.booking_cta_url ??
        "/counsellors";

    const bookingLabel =
        publicSite?.booking_cta_label ??
        "Find a Counsellor";

    return (
        <header className="sticky top-0 z-50 border-b border-slate-200/80 bg-white/95 backdrop-blur-xl">
            <div className="mx-auto flex h-[76px] max-w-[1320px] items-center justify-between gap-6 px-4 sm:px-6 lg:px-8">
                {/* Brand */}
                <Link
                    href="/"
                    className="flex min-w-0 shrink-0 items-center gap-3"
                >
                    <div className="h-[50px] w-[50px] shrink-0 overflow-hidden rounded-full border border-[#2A9D8F]/20 bg-white shadow-sm">
                        <img
                            src={logo}
                            alt={siteName}
                            className="h-full w-full object-cover"
                        />
                    </div>

                    <div className="hidden xl:block">
                        <p className="susadhya-heading text-[18px] font-bold leading-none text-[#1A365D]">
                            Susadhya
                        </p>

                        <p className="mt-1 text-[9px] font-semibold uppercase tracking-[0.17em] text-[#2A9D8F]">
                            Counselling
                        </p>
                    </div>
                </Link>

                {/* Desktop Navigation */}
                <nav className="hidden flex-1 items-center justify-center gap-7 lg:flex">
                    {navigation.map(
                        (item) => {
                            const active =
                                isActive(
                                    currentUrl,
                                    item.href,
                                );

                            return (
                                <Link
                                    key={
                                        item.href
                                    }
                                    href={
                                        item.href
                                    }
                                    className={`relative flex h-[76px] items-center whitespace-nowrap text-[13px] font-semibold transition ${
                                        active
                                            ? "text-[#2A9D8F]"
                                            : "text-[#1A365D] hover:text-[#2A9D8F]"
                                    }`}
                                >
                                    {
                                        item.label
                                    }

                                    {active && (
                                        <span className="absolute bottom-0 left-0 h-[3px] w-full rounded-t-full bg-[#2A9D8F]" />
                                    )}
                                </Link>
                            );
                        },
                    )}
                </nav>

                {/* Desktop Actions */}
                <div className="hidden shrink-0 items-center gap-3 lg:flex">
                    {auth?.user ? (
                        <Link
                            href="/dashboard"
                            className="px-3 py-2 text-sm font-semibold text-[#1A365D] transition hover:text-[#2A9D8F]"
                        >
                            Dashboard
                        </Link>
                    ) : (
                        <Link
                            href="/login"
                            className="px-3 py-2 text-sm font-semibold text-[#1A365D] transition hover:text-[#2A9D8F]"
                        >
                            Sign In
                        </Link>
                    )}

                    <Link
                        href={bookingUrl}
                        className="inline-flex items-center gap-2 rounded-full bg-[#168C9D] px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:-translate-y-0.5 hover:bg-[#147B8A] hover:shadow-md"
                    >
                        {bookingLabel}

                        <ArrowRight className="h-4 w-4" />
                    </Link>
                </div>

                {/* Mobile Menu Toggle */}
                <button
                    type="button"
                    aria-label="Toggle navigation"
                    onClick={() =>
                        setOpen(
                            (value) =>
                                !value,
                        )
                    }
                    className="flex h-11 w-11 items-center justify-center rounded-full border border-slate-200 bg-white text-[#1A365D] transition hover:border-[#2A9D8F]/30 lg:hidden"
                >
                    {open ? (
                        <X className="h-5 w-5" />
                    ) : (
                        <Menu className="h-5 w-5" />
                    )}
                </button>
            </div>

            {/* Mobile Navigation */}
            {open && (
                <div className="border-t border-slate-200 bg-white px-4 pb-6 pt-2 shadow-xl lg:hidden">
                    <div className="mx-auto max-w-7xl">
                        <nav className="divide-y divide-slate-100">
                            {navigation.map(
                                (item) => {
                                    const active =
                                        isActive(
                                            currentUrl,
                                            item.href,
                                        );

                                    return (
                                        <Link
                                            key={
                                                item.href
                                            }
                                            href={
                                                item.href
                                            }
                                            onClick={() =>
                                                setOpen(
                                                    false,
                                                )
                                            }
                                            className={`flex items-center justify-between py-4 text-sm font-semibold ${
                                                active
                                                    ? "text-[#2A9D8F]"
                                                    : "text-[#1A365D]"
                                            }`}
                                        >
                                            {
                                                item.label
                                            }

                                            <ArrowRight className="h-4 w-4 text-[#2A9D8F]" />
                                        </Link>
                                    );
                                },
                            )}
                        </nav>

                        <div className="mt-5 grid gap-3 sm:grid-cols-2">
                            <Link
                                href={
                                    auth?.user
                                        ? "/dashboard"
                                        : "/login"
                                }
                                onClick={() =>
                                    setOpen(
                                        false,
                                    )
                                }
                                className="rounded-full border border-[#1A365D]/20 bg-white px-5 py-3 text-center text-sm font-semibold text-[#1A365D]"
                            >
                                {auth?.user
                                    ? "Dashboard"
                                    : "Sign In"}
                            </Link>

                            <Link
                                href={
                                    bookingUrl
                                }
                                onClick={() =>
                                    setOpen(
                                        false,
                                    )
                                }
                                className="inline-flex items-center justify-center gap-2 rounded-full bg-[#168C9D] px-5 py-3 text-center text-sm font-semibold text-white"
                            >
                                {
                                    bookingLabel
                                }

                                <ArrowRight className="h-4 w-4" />
                            </Link>
                        </div>
                    </div>
                </div>
            )}
        </header>
    );
}
