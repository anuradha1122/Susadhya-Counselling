import { Link, usePage } from "@inertiajs/react";
import {
    ArrowUpRight,
    Clock,
    Mail,
    MapPin,
    Phone,
} from "lucide-react";

const navigation = [
    {
        label: "Home",
        url: "/",
    },
    {
        label: "About",
        url: "/about",
    },
    {
        label: "Services",
        url: "/services",
    },
    {
        label: "Counsellors",
        url: "/counsellors",
    },
    {
        label: "FAQ",
        url: "/faq",
    },
    {
        label: "Contact",
        url: "/contact",
    },
];

export default function PublicFooter() {
    const { publicSite } = usePage().props;

    const logo =
        publicSite?.logo_url ??
        publicSite?.logo_path ??
        "/images/brand/susadhya-logo.jpeg";

    const siteName =
        publicSite?.site_name ??
        "Susadhya Counselling";

    const tagline =
        publicSite?.tagline ??
        "Professional counselling support in a secure, compassionate and accessible environment.";

    const socialLinks = [
        {
            label: "Facebook",
            url: publicSite?.facebook_url,
        },
        {
            label: "Instagram",
            url: publicSite?.instagram_url,
        },
        {
            label: "LinkedIn",
            url: publicSite?.linkedin_url,
        },
        {
            label: "YouTube",
            url: publicSite?.youtube_url,
        },
    ].filter((item) => item.url);

    return (
        <footer className="bg-[#10334A] text-white">
            <div className="mx-auto max-w-[1320px] px-5 py-14 sm:px-6 lg:px-8 lg:py-16">
                <div className="grid gap-12 md:grid-cols-2 lg:grid-cols-[1.3fr_1fr_.8fr_.9fr]">
                    {/* Brand */}
                    <div>
                        <Link
                            href="/"
                            className="inline-flex items-center gap-4"
                        >
                            <div className="h-16 w-16 shrink-0 overflow-hidden rounded-full border border-white/20 bg-white shadow-sm">
                                <img
                                    src={logo}
                                    alt={siteName}
                                    className="h-full w-full object-cover"
                                />
                            </div>

                            <div>
                                <h2 className="susadhya-heading text-2xl font-bold text-white">
                                    Susadhya
                                </h2>

                                <p className="mt-1 text-[10px] font-semibold uppercase tracking-[0.18em] text-cyan-200">
                                    Counselling
                                </p>
                            </div>
                        </Link>

                        <p className="mt-6 max-w-sm text-sm leading-7 text-slate-300">
                            {tagline}
                        </p>

                        {socialLinks.length > 0 && (
                            <div className="mt-7 flex flex-wrap gap-2">
                                {socialLinks.map((item) => (
                                    <a
                                        key={item.label}
                                        href={item.url}
                                        target="_blank"
                                        rel="noreferrer"
                                        className="inline-flex items-center gap-1.5 rounded-full border border-white/15 px-3 py-1.5 text-xs font-medium text-slate-200 transition hover:border-cyan-300 hover:bg-white/5 hover:text-cyan-200"
                                    >
                                        {item.label}

                                        <ArrowUpRight className="h-3 w-3" />
                                    </a>
                                ))}
                            </div>
                        )}
                    </div>

                    {/* Contact */}
                    <div>
                        <h3 className="text-xs font-bold uppercase tracking-[0.17em] text-cyan-200">
                            Contact
                        </h3>

                        <div className="mt-6 space-y-4 text-sm text-slate-300">
                            {publicSite?.contact_phone && (
                                <a
                                    href={`tel:${publicSite.contact_phone}`}
                                    className="flex gap-3 transition hover:text-white"
                                >
                                    <Phone className="mt-0.5 h-4 w-4 shrink-0 text-cyan-300" />

                                    <span>
                                        {publicSite.contact_phone}
                                    </span>
                                </a>
                            )}

                            {publicSite?.contact_email && (
                                <a
                                    href={`mailto:${publicSite.contact_email}`}
                                    className="flex gap-3 transition hover:text-white"
                                >
                                    <Mail className="mt-0.5 h-4 w-4 shrink-0 text-cyan-300" />

                                    <span className="break-all">
                                        {publicSite.contact_email}
                                    </span>
                                </a>
                            )}

                            {publicSite?.address && (
                                <div className="flex gap-3">
                                    <MapPin className="mt-0.5 h-4 w-4 shrink-0 text-cyan-300" />

                                    <span className="whitespace-pre-line leading-6">
                                        {publicSite.address}
                                    </span>
                                </div>
                            )}

                            {publicSite?.office_hours && (
                                <div className="flex gap-3">
                                    <Clock className="mt-0.5 h-4 w-4 shrink-0 text-cyan-300" />

                                    <span className="whitespace-pre-line leading-6">
                                        {publicSite.office_hours}
                                    </span>
                                </div>
                            )}
                        </div>
                    </div>

                    {/* Fixed navigation */}
                    <div>
                        <h3 className="text-xs font-bold uppercase tracking-[0.17em] text-cyan-200">
                            Explore
                        </h3>

                        <nav className="mt-6 space-y-3">
                            {navigation.map((item) => (
                                <Link
                                    key={item.url}
                                    href={item.url}
                                    className="group flex w-fit items-center gap-2 text-sm text-slate-300 transition hover:translate-x-1 hover:text-white"
                                >
                                    {item.label}

                                    <ArrowUpRight className="h-3 w-3 opacity-0 transition group-hover:opacity-100" />
                                </Link>
                            ))}
                        </nav>
                    </div>

                    {/* CTA */}
                    <div>
                        <h3 className="text-xs font-bold uppercase tracking-[0.17em] text-cyan-200">
                            Get Support
                        </h3>

                        <p className="mt-6 text-sm leading-7 text-slate-300">
                            Explore available counselling services and find a
                            professional when you are ready to take the next
                            step.
                        </p>

                        <Link
                            href="/counsellors"
                            className="mt-6 inline-flex items-center gap-2 rounded-full bg-[#168C9D] px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:-translate-y-0.5 hover:bg-[#1491A2] hover:shadow-md"
                        >
                            Find a Counsellor

                            <ArrowUpRight className="h-4 w-4" />
                        </Link>
                    </div>
                </div>

                {/* Bottom */}
                <div className="mt-14 flex flex-col gap-4 border-t border-white/10 pt-6 text-xs text-slate-400 sm:flex-row sm:items-center sm:justify-between">
                    <p>
                        © {new Date().getFullYear()} {siteName}. All rights
                        reserved.
                    </p>

                    <div className="flex flex-wrap gap-5">
                        <Link
                            href="/pages/privacy-policy"
                            className="transition hover:text-white"
                        >
                            Privacy Policy
                        </Link>

                        <Link
                            href="/pages/terms-and-conditions"
                            className="transition hover:text-white"
                        >
                            Terms & Conditions
                        </Link>
                    </div>
                </div>
            </div>
        </footer>
    );
}
