import {
    Link,
} from "@inertiajs/react";
import {
    ArrowRight,
    Brain,
    CalendarDays,
    Check,
    Heart,
    HeartHandshake,
    Laptop,
    LockKeyhole,
    MessageCircle,
    ShieldCheck,
    Sparkles,
    Star,
    UserRound,
    UsersRound,
} from "lucide-react";
import {
    useState,
} from "react";

const serviceIcons = [
    UserRound,
    Heart,
    UsersRound,
    Brain,
    Laptop,
    HeartHandshake,
];

function ServiceIcon({
    index,
}) {
    const Icon =
        serviceIcons[
            index %
                serviceIcons.length
        ];

    return (
        <div className="flex h-16 w-16 items-center justify-center rounded-full bg-[#DFF2F6] text-[#168C9D]">
            <Icon className="h-7 w-7" />
        </div>
    );
}

export function MarketingHero({
    section,
}) {
    if (!section) {
        return null;
    }

    const content =
        section.content ?? {};

    const image =
        content.image_path;

    return (
        <section className="relative overflow-hidden bg-[#EAF6FA]">
            <div className="relative mx-auto min-h-[610px] max-w-[1600px] overflow-hidden lg:min-h-[650px]">
                {image && (
                    <img
                        src={image}
                        alt={
                            content.image_alt ??
                            section.heading ??
                            ""
                        }
                        className="absolute inset-0 h-full w-full object-cover object-center"
                    />
                )}

                <div className="absolute inset-0 bg-[linear-gradient(90deg,rgba(239,248,250,0.99)_0%,rgba(239,248,250,0.95)_28%,rgba(239,248,250,0.72)_48%,rgba(239,248,250,0.18)_70%,rgba(239,248,250,0.04)_100%)]" />

                <div className="relative z-10 mx-auto flex min-h-[610px] max-w-[1320px] items-center px-5 py-16 sm:px-6 lg:min-h-[650px] lg:px-8">
                    <div className="max-w-[650px]">
                        {content.eyebrow && (
                            <p className="text-xs font-bold uppercase tracking-[0.2em] text-[#168C9D]">
                                {
                                    content.eyebrow
                                }
                            </p>
                        )}

                        <h1 className="susadhya-heading mt-5 text-[42px] font-bold leading-[1.04] text-[#123B5B] sm:text-5xl lg:text-[64px]">
                            {section.heading}
                        </h1>

                        {section.subheading && (
                            <p className="mt-6 max-w-[590px] text-[15px] leading-7 text-slate-600 sm:text-base lg:text-[17px] lg:leading-8">
                                {
                                    section.subheading
                                }
                            </p>
                        )}

                        {(content.primary_cta_label ||
                            content.secondary_cta_label) && (
                            <div className="mt-8 flex flex-wrap gap-3">
                                {content.primary_cta_label &&
                                    content.primary_cta_url && (
                                        <Link
                                            href={
                                                content.primary_cta_url
                                            }
                                            className="inline-flex items-center gap-2 rounded-full bg-[#168C9D] px-7 py-3.5 text-sm font-semibold text-white shadow-md shadow-cyan-900/10 hover:-translate-y-0.5 hover:bg-[#147D8B]"
                                        >
                                            {
                                                content.primary_cta_label
                                            }

                                            <ArrowRight className="h-4 w-4" />
                                        </Link>
                                    )}

                                {content.secondary_cta_label &&
                                    content.secondary_cta_url && (
                                        <Link
                                            href={
                                                content.secondary_cta_url
                                            }
                                            className="inline-flex items-center gap-2 rounded-full border border-[#168C9D] bg-white/90 px-7 py-3.5 text-sm font-semibold text-[#126D7A] backdrop-blur hover:bg-white"
                                        >
                                            {
                                                content.secondary_cta_label
                                            }
                                        </Link>
                                    )}
                            </div>
                        )}

                        {content.trust_items
                            ?.length > 0 && (
                            <div className="mt-10 grid max-w-[620px] gap-4 sm:grid-cols-3">
                                {content.trust_items
                                    .slice(0, 3)
                                    .map(
                                        (
                                            item,
                                            index,
                                        ) => {
                                            const icons =
                                                [
                                                    ShieldCheck,
                                                    UsersRound,
                                                    Heart,
                                                ];

                                            const Icon =
                                                icons[
                                                    index
                                                ];

                                            return (
                                                <div
                                                    key={
                                                        item
                                                    }
                                                    className="flex items-center gap-3"
                                                >
                                                    <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-[#168C9D] text-white shadow-sm">
                                                        <Icon className="h-5 w-5" />
                                                    </div>

                                                    <p className="text-[11px] font-semibold leading-4 text-[#1A365D]">
                                                        {
                                                            item
                                                        }
                                                    </p>
                                                </div>
                                            );
                                        },
                                    )}
                            </div>
                        )}
                    </div>
                </div>
            </div>
        </section>
    );
}

export function ServicesPreview({
    section,
    services,
}) {
    if (!services?.length) {
        return null;
    }

    return (
        <section className="bg-[#EAF7FB] px-5 py-16 sm:px-6 lg:px-8">
            <div className="mx-auto max-w-[1320px]">
                <div className="text-center">
                    <p className="susadhya-section-label">
                        Our Services
                    </p>

                    <h2 className="susadhya-heading mt-2 text-3xl font-bold text-[#123B5B] sm:text-4xl">
                        {section?.heading ??
                            "Support for Every Stage of Life"}
                    </h2>

                    {section?.subheading && (
                        <p className="mx-auto mt-4 max-w-2xl text-sm leading-7 text-slate-600">
                            {
                                section.subheading
                            }
                        </p>
                    )}
                </div>

                <div className="mt-10 grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5">
                    {services.map(
                        (
                            service,
                            index,
                        ) => (
                            <Link
                                key={
                                    service.slug
                                }
                                href={route(
                                    "public.services.show",
                                    service.slug,
                                )}
                                className="group flex min-h-[285px] flex-col rounded-2xl border border-white/80 bg-white p-6 shadow-[0_8px_30px_rgba(26,54,93,0.06)] hover:-translate-y-1 hover:shadow-[0_16px_38px_rgba(26,54,93,0.11)]"
                            >
                                <ServiceIcon
                                    index={
                                        index
                                    }
                                />

                                <h3 className="susadhya-heading mt-5 text-[19px] font-bold leading-snug text-[#123B5B]">
                                    {
                                        service.name
                                    }
                                </h3>

                                <p className="mt-3 line-clamp-3 text-[13px] leading-6 text-slate-600">
                                    {service.short_description ??
                                        service.description}
                                </p>

                                <div className="mt-auto pt-5">
                                    <span className="inline-flex items-center gap-2 text-xs font-bold text-[#168C9D]">
                                        Learn More

                                        <ArrowRight className="h-3.5 w-3.5 transition-transform group-hover:translate-x-1" />
                                    </span>
                                </div>
                            </Link>
                        ),
                    )}
                </div>

                <div className="mt-8 text-center">
                    <Link
                        href="/services"
                        className="inline-flex items-center gap-2 text-sm font-semibold text-[#168C9D]"
                    >
                        View All Services

                        <ArrowRight className="h-4 w-4" />
                    </Link>
                </div>
            </div>
        </section>
    );
}

export function CounsellorsPreview({
    section,
    counsellors,
}) {
    if (!counsellors?.length) {
        return null;
    }

    return (
        <section className="bg-white px-5 py-16 sm:px-6 lg:px-8">
            <div className="mx-auto max-w-[1320px]">
                <div className="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p className="susadhya-section-label">
                            Meet Our
                            Counsellors
                        </p>

                        <h2 className="susadhya-heading mt-2 text-3xl font-bold text-[#123B5B] sm:text-4xl">
                            {section?.heading ??
                                "Trusted Professionals. Here for You."}
                        </h2>

                        {section?.subheading && (
                            <p className="mt-3 max-w-2xl text-sm leading-7 text-slate-600">
                                {
                                    section.subheading
                                }
                            </p>
                        )}
                    </div>

                    <Link
                        href="/counsellors"
                        className="inline-flex shrink-0 items-center gap-2 text-sm font-semibold text-[#168C9D]"
                    >
                        View All Counsellors

                        <ArrowRight className="h-4 w-4" />
                    </Link>
                </div>

                <div className="mt-8 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    {counsellors.map(
                        (
                            counsellor,
                        ) => {
                            const specialties =
                                Array.isArray(
                                    counsellor.specialties,
                                )
                                    ? counsellor.specialties
                                    : [];

                            return (
                                <Link
                                    key={
                                        counsellor.uuid
                                    }
                                    href={route(
                                        "public.counsellors.show",
                                        counsellor.uuid,
                                    )}
                                    className="group flex min-h-[190px] gap-4 rounded-2xl border border-slate-200 bg-white p-4 shadow-[0_6px_24px_rgba(26,54,93,0.05)] hover:-translate-y-1 hover:border-[#168C9D]/30 hover:shadow-lg"
                                >
                                    <div className="h-[120px] w-[95px] shrink-0 overflow-hidden rounded-xl bg-[#EAF7FB]">
                                        {counsellor.profile_image_url ? (
                                            <img
                                                src={
                                                    counsellor.profile_image_url
                                                }
                                                alt={
                                                    counsellor.name
                                                }
                                                className="h-full w-full object-cover"
                                            />
                                        ) : (
                                            <div className="flex h-full items-center justify-center">
                                                <span className="susadhya-heading text-4xl font-bold text-[#168C9D]">
                                                    {counsellor.name
                                                        ?.charAt(
                                                            0,
                                                        )
                                                        ?.toUpperCase() ??
                                                        "C"}
                                                </span>
                                            </div>
                                        )}
                                    </div>

                                    <div className="min-w-0 flex-1">
                                        <h3 className="susadhya-heading text-[17px] font-bold leading-snug text-[#123B5B] group-hover:text-[#168C9D]">
                                            {
                                                counsellor.name
                                            }
                                        </h3>

                                        {counsellor.headline && (
                                            <p className="mt-1 line-clamp-2 text-[11px] font-semibold text-[#168C9D]">
                                                {
                                                    counsellor.headline
                                                }
                                            </p>
                                        )}

                                        {specialties.length >
                                            0 && (
                                            <p className="mt-3 line-clamp-2 text-[11px] leading-5 text-slate-500">
                                                {specialties
                                                    .slice(
                                                        0,
                                                        3,
                                                    )
                                                    .join(
                                                        ", ",
                                                    )}
                                            </p>
                                        )}

                                        <span className="mt-4 inline-flex items-center gap-1.5 rounded-full border border-[#168C9D]/40 px-3 py-1.5 text-[11px] font-semibold text-[#168C9D]">
                                            View Profile

                                            <ArrowRight className="h-3 w-3" />
                                        </span>
                                    </div>
                                </Link>
                            );
                        },
                    )}
                </div>
            </div>
        </section>
    );
}

export function TrustBand() {
    const items = [
        {
            icon: UsersRound,
            title:
                "Professional Counsellors",
            description:
                "Qualified support",
        },
        {
            icon: LockKeyhole,
            title:
                "Confidential Support",
            description:
                "Privacy matters",
        },
        {
            icon: ShieldCheck,
            title:
                "Secure Platform",
            description:
                "Controlled access",
        },
        {
            icon: Heart,
            title:
                "Compassionate Care",
            description:
                "Support with respect",
        },
    ];

    return (
        <section className="border-y border-[#CBE8EF] bg-[#DFF3F8] px-5 py-8 sm:px-6 lg:px-8">
            <div className="mx-auto grid max-w-[1200px] gap-6 sm:grid-cols-2 lg:grid-cols-4">
                {items.map(
                    ({
                        icon: Icon,
                        title,
                        description,
                    }) => (
                        <div
                            key={
                                title
                            }
                            className="flex items-center justify-center gap-4 lg:justify-start"
                        >
                            <div className="flex h-12 w-12 shrink-0 items-center justify-center text-[#168C9D]">
                                <Icon className="h-9 w-9" />
                            </div>

                            <div>
                                <p className="susadhya-heading text-[18px] font-bold text-[#123B5B]">
                                    {
                                        title
                                    }
                                </p>

                                <p className="mt-0.5 text-[11px] text-slate-500">
                                    {
                                        description
                                    }
                                </p>
                            </div>
                        </div>
                    ),
                )}
            </div>
        </section>
    );
}

export function ImageTextSection({
    section,
    imageLeft = true,
}) {
    if (!section) {
        return null;
    }

    const content =
        section.content ?? {};

    return (
        <section className="bg-[#F9FBFA] px-5 py-16 sm:px-6 lg:px-8">
            <div className="mx-auto grid max-w-[1200px] overflow-hidden rounded-[28px] bg-white shadow-[0_18px_55px_rgba(26,54,93,0.08)] lg:grid-cols-2">
                <div
                    className={`min-h-[380px] ${
                        imageLeft
                            ? "lg:order-1"
                            : "lg:order-2"
                    }`}
                >
                    {content.image_path ? (
                        <img
                            src={
                                content.image_path
                            }
                            alt={
                                content.image_alt ??
                                section.heading
                            }
                            className="h-full min-h-[380px] w-full object-cover"
                        />
                    ) : (
                        <div className="flex h-full min-h-[380px] items-center justify-center bg-[#EAF7FB]">
                            <Sparkles className="h-16 w-16 text-[#168C9D]" />
                        </div>
                    )}
                </div>

                <div
                    className={`flex items-center ${
                        imageLeft
                            ? "lg:order-2"
                            : "lg:order-1"
                    }`}
                >
                    <div className="p-8 sm:p-10 lg:p-14">
                        <p className="susadhya-section-label">
                            Support That
                            Matters
                        </p>

                        <h2 className="susadhya-heading mt-3 text-3xl font-bold leading-tight text-[#123B5B] sm:text-4xl">
                            {
                                section.heading
                            }
                        </h2>

                        {section.subheading && (
                            <p className="mt-5 text-sm leading-7 text-slate-600">
                                {
                                    section.subheading
                                }
                            </p>
                        )}

                        {content.body && (
                            <p className="mt-4 text-sm leading-7 text-slate-500">
                                {
                                    content.body
                                }
                            </p>
                        )}

                        {content.points
                            ?.length > 0 && (
                            <div className="mt-6 grid gap-3">
                                {content.points.map(
                                    (
                                        point,
                                    ) => (
                                        <div
                                            key={
                                                point
                                            }
                                            className="flex gap-3"
                                        >
                                            <div className="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-[#E0F2E2]">
                                                <Check className="h-3 w-3 text-[#5D8E55]" />
                                            </div>

                                            <p className="text-[13px] leading-6 text-slate-600">
                                                {
                                                    point
                                                }
                                            </p>
                                        </div>
                                    ),
                                )}
                            </div>
                        )}
                    </div>
                </div>
            </div>
        </section>
    );
}

export function TestimonialsPreview({
    section,
    testimonials,
}) {
    if (!testimonials?.length) {
        return null;
    }

    return (
        <section className="bg-white px-5 py-16 sm:px-6 lg:px-8">
            <div className="mx-auto max-w-[1320px]">
                <div className="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p className="susadhya-section-label">
                            What Our Clients Say
                        </p>

                        <h2 className="susadhya-heading mt-2 text-3xl font-bold text-[#123B5B] sm:text-4xl">
                            {section?.heading ??
                                "Real Stories. Meaningful Support."}
                        </h2>
                    </div>
                </div>

                <div className="mt-8 grid gap-5 md:grid-cols-3">
                    {testimonials.map(
                        (
                            testimonial,
                        ) => (
                            <article
                                key={
                                    testimonial.uuid
                                }
                                className="rounded-2xl border border-slate-200 bg-white p-6 shadow-[0_8px_30px_rgba(26,54,93,0.06)]"
                            >
                                <MessageCircle className="h-7 w-7 text-[#168C9D]" />

                                <p className="mt-5 text-sm leading-7 text-slate-600">
                                    “
                                    {
                                        testimonial.quote
                                    }
                                    ”
                                </p>

                                {testimonial.rating && (
                                    <div className="mt-5 flex gap-1 text-amber-400">
                                        {Array.from({
                                            length:
                                                Math.min(
                                                    Number(
                                                        testimonial.rating,
                                                    ),
                                                    5,
                                                ),
                                        }).map(
                                            (
                                                _,
                                                index,
                                            ) => (
                                                <Star
                                                    key={
                                                        index
                                                    }
                                                    className="h-4 w-4 fill-current"
                                                />
                                            ),
                                        )}
                                    </div>
                                )}

                                <p className="mt-3 text-sm font-bold text-[#123B5B]">
                                    {
                                        testimonial.display_name
                                    }
                                </p>
                            </article>
                        ),
                    )}
                </div>
            </div>
        </section>
    );
}

export function StepsSection({
    section,
}) {
    if (!section) {
        return null;
    }

    const items =
        section.content?.items ??
        [];

    return (
        <section className="bg-[#F9FBFA] px-5 py-16 sm:px-6 lg:px-8">
            <div className="mx-auto max-w-[1200px]">
                <div className="text-center">
                    <p className="susadhya-section-label">
                        How It Works
                    </p>

                    <h2 className="susadhya-heading mt-2 text-3xl font-bold text-[#123B5B] sm:text-4xl">
                        {section.heading}
                    </h2>

                    {section.subheading && (
                        <p className="mx-auto mt-4 max-w-2xl text-sm leading-7 text-slate-600">
                            {
                                section.subheading
                            }
                        </p>
                    )}
                </div>

                <div className="relative mt-12 grid gap-8 md:grid-cols-2 lg:grid-cols-4">
                    {items.map(
                        (
                            item,
                            index,
                        ) => {
                            const icons =
                                [
                                    SearchIcon,
                                    UsersRound,
                                    CalendarDays,
                                    HeartHandshake,
                                ];

                            const Icon =
                                icons[
                                    index %
                                        icons.length
                                ];

                            return (
                                <div
                                    key={`${item.number}-${item.title}`}
                                    className="relative text-center"
                                >
                                    <div className="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-[#DDF2F7] text-[#168C9D]">
                                        <Icon className="h-7 w-7" />
                                    </div>

                                    <div className="mx-auto mt-3 flex h-8 w-8 items-center justify-center rounded-full bg-[#168C9D] text-xs font-bold text-white">
                                        {item.number ??
                                            index +
                                                1}
                                    </div>

                                    <h3 className="susadhya-heading mt-4 text-xl font-bold text-[#123B5B]">
                                        {
                                            item.title
                                        }
                                    </h3>

                                    <p className="mx-auto mt-2 max-w-[230px] text-[12px] leading-6 text-slate-500">
                                        {
                                            item.description
                                        }
                                    </p>

                                    {index <
                                        items.length -
                                            1 && (
                                        <ArrowRight className="absolute right-[-20px] top-8 hidden h-5 w-5 text-[#168C9D]/60 lg:block" />
                                    )}
                                </div>
                            );
                        },
                    )}
                </div>
            </div>
        </section>
    );
}

function SearchIcon(
    props,
) {
    return (
        <Sparkles
            {...props}
        />
    );
}

export function FaqPreview({
    section,
    faqs,
}) {
    const [open, setOpen] =
        useState(null);

    if (!faqs?.length) {
        return null;
    }

    return (
        <section className="bg-white px-5 py-16 sm:px-6 lg:px-8">
            <div className="mx-auto grid max-w-[1150px] gap-12 lg:grid-cols-[.7fr_1.3fr]">
                <div>
                    <p className="susadhya-section-label">
                        Questions &
                        Answers
                    </p>

                    <h2 className="susadhya-heading mt-3 text-3xl font-bold leading-tight text-[#123B5B] sm:text-4xl">
                        {section?.heading ??
                            "Questions Before You Begin?"}
                    </h2>

                    {section?.subheading && (
                        <p className="mt-5 text-sm leading-7 text-slate-600">
                            {
                                section.subheading
                            }
                        </p>
                    )}

                    <Link
                        href="/faq"
                        className="mt-6 inline-flex items-center gap-2 text-sm font-bold text-[#168C9D]"
                    >
                        View All Questions

                        <ArrowRight className="h-4 w-4" />
                    </Link>
                </div>

                <div className="divide-y divide-slate-200 border-y border-slate-200">
                    {faqs
                        .slice(0, 5)
                        .map(
                            (faq) => {
                                const active =
                                    open ===
                                    faq.uuid;

                                return (
                                    <div
                                        key={
                                            faq.uuid
                                        }
                                    >
                                        <button
                                            type="button"
                                            onClick={() =>
                                                setOpen(
                                                    active
                                                        ? null
                                                        : faq.uuid,
                                                )
                                            }
                                            className="flex w-full items-center justify-between gap-5 py-5 text-left"
                                        >
                                            <span className="susadhya-heading text-[17px] font-bold text-[#123B5B]">
                                                {
                                                    faq.question
                                                }
                                            </span>

                                            <span className="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-[#EAF7FB] text-lg font-semibold text-[#168C9D]">
                                                {active
                                                    ? "−"
                                                    : "+"}
                                            </span>
                                        </button>

                                        {active && (
                                            <p className="max-w-3xl pb-5 pr-12 text-sm leading-7 text-slate-600">
                                                {
                                                    faq.answer
                                                }
                                            </p>
                                        )}
                                    </div>
                                );
                            },
                        )}
                </div>
            </div>
        </section>
    );
}

export function FinalCta({
    section,
    imagePath = null,
}) {
    if (!section) {
        return null;
    }

    const content =
        section.content ?? {};

    const image =
        imagePath ??
        content.image_path;

    return (
        <section className="relative min-h-[280px] overflow-hidden bg-[#DFF2F7]">
            {image && (
                <img
                    src={image}
                    alt=""
                    className="absolute inset-0 h-full w-full object-cover"
                />
            )}

            <div className="absolute inset-0 bg-[linear-gradient(90deg,rgba(226,244,248,0.45)_0%,rgba(226,244,248,0.6)_36%,rgba(240,249,250,0.95)_65%,rgba(240,249,250,0.99)_100%)]" />

            <div className="relative mx-auto flex min-h-[280px] max-w-[1320px] items-center justify-end px-5 py-12 sm:px-6 lg:px-8">
                <div className="max-w-xl text-center lg:text-left">
                    <h2 className="susadhya-heading text-3xl font-bold leading-tight text-[#123B5B] sm:text-4xl">
                        {
                            section.heading
                        }
                    </h2>

                    {section.subheading && (
                        <p className="mt-4 text-sm leading-7 text-slate-600">
                            {
                                section.subheading
                            }
                        </p>
                    )}

                    {content.primary_cta_label &&
                        content.primary_cta_url && (
                            <Link
                                href={
                                    content.primary_cta_url
                                }
                                className="mt-6 inline-flex items-center gap-2 rounded-full bg-[#168C9D] px-7 py-3.5 text-sm font-semibold text-white shadow-md hover:-translate-y-0.5 hover:bg-[#147D8B]"
                            >
                                {
                                    content.primary_cta_label
                                }

                                <ArrowRight className="h-4 w-4" />
                            </Link>
                        )}
                </div>
            </div>
        </section>
    );
}
