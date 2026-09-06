import PublicVisual from "@/Components/Public/PublicVisual";
import { Link } from "@inertiajs/react";
import {
    BadgeCheck,
    Check,
    HeartHandshake,
    ShieldCheck,
    Star,
} from "lucide-react";
import { useState } from "react";

function SectionHeading({
    section,
    align = "center",
}) {
    if (
        !section.heading &&
        !section.subheading
    ) {
        return null;
    }

    const centered =
        align === "center";

    return (
        <div
            className={`mb-10 ${
                centered
                    ? "mx-auto max-w-3xl text-center"
                    : "max-w-2xl"
            }`}
        >
            {section.heading && (
                <h2 className="susadhya-heading text-3xl font-bold leading-tight sm:text-4xl">
                    {section.heading}
                </h2>
            )}

            {section.subheading && (
                <p className="mt-4 text-base leading-8 text-slate-600">
                    {section.subheading}
                </p>
            )}
        </div>
    );
}

function Hero({ section }) {
    const content =
        section.content ?? {};

    return (
        <section className="relative overflow-hidden bg-[#F7F9F4]">
            <div className="absolute left-0 top-0 h-80 w-80 -translate-x-1/2 -translate-y-1/2 rounded-full bg-[#2A9D8F]/10" />

            <div className="absolute bottom-0 right-0 h-96 w-96 translate-x-1/2 translate-y-1/2 rounded-full bg-[#7FB069]/10" />

            <div className="relative mx-auto grid max-w-7xl items-center gap-12 px-4 py-16 sm:px-6 lg:grid-cols-[1.05fr_.95fr] lg:px-8 lg:py-24">
                <div>
                    {content.eyebrow && (
                        <div className="inline-flex rounded-full bg-[#E6F2FA] px-4 py-2 text-xs font-semibold uppercase tracking-[0.16em] text-[#2A9D8F]">
                            {content.eyebrow}
                        </div>
                    )}

                    <h1 className="susadhya-heading mt-6 max-w-3xl text-4xl font-bold leading-[1.08] sm:text-5xl lg:text-6xl">
                        {section.heading}
                    </h1>

                    {section.subheading && (
                        <p className="mt-6 max-w-2xl text-lg leading-8 text-slate-600">
                            {section.subheading}
                        </p>
                    )}

                    <div className="mt-8 flex flex-wrap gap-3">
                        {content.primary_cta_url &&
                            content.primary_cta_label && (
                                <Link
                                    href={
                                        content.primary_cta_url
                                    }
                                    className="rounded-xl bg-[#2A9D8F] px-6 py-3.5 text-sm font-semibold text-white shadow-sm hover:-translate-y-0.5 hover:bg-[#23897D] hover:shadow-md"
                                >
                                    {
                                        content.primary_cta_label
                                    }
                                </Link>
                            )}

                        {content.secondary_cta_url &&
                            content.secondary_cta_label && (
                                <Link
                                    href={
                                        content.secondary_cta_url
                                    }
                                    className="rounded-xl border border-slate-300 bg-white px-6 py-3.5 text-sm font-semibold text-[#1A365D] shadow-sm hover:border-[#2A9D8F]"
                                >
                                    {
                                        content.secondary_cta_label
                                    }
                                </Link>
                            )}
                    </div>

                    {content.trust_items?.length >
                        0 && (
                        <div className="mt-8 flex flex-wrap gap-x-6 gap-y-3">
                            {content.trust_items.map(
                                (item) => (
                                    <span
                                        key={
                                            item
                                        }
                                        className="flex items-center gap-2 text-sm text-slate-600"
                                    >
                                        <Check className="h-4 w-4 text-[#7FB069]" />
                                        {item}
                                    </span>
                                ),
                            )}
                        </div>
                    )}
                </div>

                <PublicVisual
                    variant={
                        content.visual ??
                        "session"
                    }
                    imagePath={
                        content.image_path ??
                        null
                    }
                    imageAlt={
                        section.heading
                    }
                />
            </div>
        </section>
    );
}

function Stats({ section }) {
    const items =
        section.content?.items ?? [];

    if (items.length === 0) {
        return null;
    }

    return (
        <section className="bg-[#F7F9F4] px-4 py-6 sm:px-6 lg:px-8">
            <div className="mx-auto grid max-w-7xl overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm sm:grid-cols-2 lg:grid-cols-4">
                {items.map(
                    (item, index) => (
                        <div
                            key={`${item.value}-${item.label}`}
                            className={`px-6 py-8 text-center ${
                                index !==
                                items.length -
                                    1
                                    ? "border-b border-slate-200 lg:border-b-0 lg:border-r"
                                    : ""
                            }`}
                        >
                            <p className="susadhya-heading text-2xl font-bold text-[#1A365D]">
                                {item.value}
                            </p>

                            <p className="mx-auto mt-2 max-w-[220px] text-xs leading-5 text-slate-500">
                                {item.label}
                            </p>
                        </div>
                    ),
                )}
            </div>
        </section>
    );
}

function Steps({ section }) {
    const items =
        section.content?.items ?? [];

    return (
        <section className="bg-white px-4 py-20 sm:px-6 lg:px-8 lg:py-28">
            <div className="mx-auto max-w-7xl">
                <div className="grid gap-12 lg:grid-cols-[360px_1fr]">
                    <div>
                        <p className="text-xs font-semibold uppercase tracking-[0.2em] text-[#2A9D8F]">
                            How it works
                        </p>

                        <h2 className="susadhya-heading mt-4 text-4xl font-bold leading-tight">
                            {section.heading}
                        </h2>

                        {section.subheading && (
                            <p className="mt-5 text-sm leading-7 text-slate-600">
                                {
                                    section.subheading
                                }
                            </p>
                        )}
                    </div>

                    <div className="grid gap-x-8 gap-y-10 sm:grid-cols-2">
                        {items.map(
                            (item) => (
                                <div
                                    key={
                                        item.number
                                    }
                                    className="relative border-t border-slate-200 pt-6"
                                >
                                    <span className="text-xs font-semibold tracking-[0.16em] text-[#2A9D8F]">
                                        {
                                            item.number
                                        }
                                    </span>

                                    <h3 className="susadhya-heading mt-3 text-xl font-bold">
                                        {
                                            item.title
                                        }
                                    </h3>

                                    <p className="mt-3 text-sm leading-7 text-slate-600">
                                        {
                                            item.description
                                        }
                                    </p>
                                </div>
                            ),
                        )}
                    </div>
                </div>
            </div>
        </section>
    );
}

function FeatureGrid({ section }) {
    const icons = {
        shield: ShieldCheck,
        badge: BadgeCheck,
        heart: HeartHandshake,
    };

    const items =
        section.content?.items ?? [];

    return (
        <section className="px-4 py-16 sm:px-6 lg:px-8 lg:py-20">
            <div className="mx-auto max-w-7xl">
                <SectionHeading
                    section={section}
                />

                <div className="grid gap-6 md:grid-cols-3">
                    {items.map(
                        (item, index) => {
                            const Icon =
                                icons[
                                    item.icon
                                ] ??
                                HeartHandshake;

                            return (
                                <div
                                    key={`${item.title}-${index}`}
                                    className="rounded-2xl border border-slate-200 bg-white p-7 shadow-sm transition hover:-translate-y-1 hover:shadow-md"
                                >
                                    <div className="inline-flex rounded-xl bg-[#E6F2FA] p-3 text-[#2A9D8F]">
                                        <Icon className="h-6 w-6" />
                                    </div>

                                    <h3 className="susadhya-heading mt-5 text-2xl font-bold">
                                        {
                                            item.title
                                        }
                                    </h3>

                                    <p className="mt-3 text-sm leading-7 text-slate-600">
                                        {
                                            item.description
                                        }
                                    </p>
                                </div>
                            );
                        },
                    )}
                </div>
            </div>
        </section>
    );
}

function ImageText({ section }) {
    const content =
        section.content ?? {};

    const imageLeft =
        content.image_position ===
        "left";

    const image =
        content.image_path ?? null;

    const visual = image ? (
        <div className="overflow-hidden">
            <img
                src={image}
                alt={
                    content.image_alt ??
                    section.heading ??
                    ""
                }
                className="aspect-[4/3] h-full w-full object-cover"
            />
        </div>
    ) : (
        <div className="flex aspect-[4/3] items-center justify-center bg-[#E6F2FA]">
            <HeartHandshake className="h-20 w-20 text-[#2A9D8F]" />
        </div>
    );

    const copy = (
        <div className="flex items-center">
            <div className="max-w-xl px-6 py-12 sm:px-10 lg:px-16 lg:py-20">
                {content.eyebrow && (
                    <p className="text-xs font-semibold uppercase tracking-[0.2em] text-[#2A9D8F]">
                        {content.eyebrow}
                    </p>
                )}

                <h2 className="susadhya-heading mt-4 text-3xl font-bold leading-tight sm:text-4xl">
                    {section.heading}
                </h2>

                {section.subheading && (
                    <p className="mt-5 text-base leading-8 text-slate-600">
                        {
                            section.subheading
                        }
                    </p>
                )}

                {content.body && (
                    <p className="mt-5 text-sm leading-7 text-slate-600">
                        {content.body}
                    </p>
                )}

                {content.points?.length >
                    0 && (
                    <div className="mt-7 space-y-3">
                        {content.points.map(
                            (point) => (
                                <div
                                    key={
                                        point
                                    }
                                    className="flex gap-3"
                                >
                                    <Check className="mt-1 h-4 w-4 shrink-0 text-[#7FB069]" />

                                    <p className="text-sm leading-6 text-slate-600">
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
    );

    return (
        <section className="bg-[#F7F9F4] px-4 py-12 sm:px-6 lg:px-8 lg:py-20">
            <div className="mx-auto grid max-w-7xl overflow-hidden bg-white shadow-sm lg:grid-cols-2">
                {imageLeft ? (
                    <>
                        {visual}
                        {copy}
                    </>
                ) : (
                    <>
                        {copy}
                        {visual}
                    </>
                )}
            </div>
        </section>
    );
}

function Services({ section }) {
    const services =
        section.payload ?? [];

    if (services.length === 0) {
        return null;
    }

    return (
        <section className="bg-[#F7F9F4] px-4 py-20 sm:px-6 lg:px-8 lg:py-28">
            <div className="mx-auto max-w-7xl">
                {/* Section heading */}
                <div className="mb-12 flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                    <div className="max-w-3xl">
                        <p className="text-xs font-semibold uppercase tracking-[0.2em] text-[#2A9D8F]">
                            Counselling Services
                        </p>

                        {section.heading && (
                            <h2 className="susadhya-heading mt-4 text-4xl font-bold leading-tight text-[#1A365D] sm:text-5xl">
                                {section.heading}
                            </h2>
                        )}

                        {section.subheading && (
                            <p className="mt-5 max-w-2xl text-base leading-8 text-slate-600">
                                {section.subheading}
                            </p>
                        )}
                    </div>

                    <Link
                        href={route(
                            "public.services.index",
                        )}
                        className="inline-flex shrink-0 items-center text-sm font-semibold text-[#2A9D8F] transition hover:text-[#1A365D]"
                    >
                        View all services
                        <span className="ml-2">
                            →
                        </span>
                    </Link>
                </div>

                {/* Services */}
                <div className="grid gap-x-6 gap-y-8 md:grid-cols-2 lg:grid-cols-3">
                    {services.map(
                        (service) => {
                            const mode =
                                String(
                                    service.service_mode ??
                                        "Counselling",
                                ).replaceAll(
                                    "_",
                                    " ",
                                );

                            const description =
                                service.short_description ??
                                service.description ??
                                "";

                            const hasPrice =
                                service.price !==
                                    null &&
                                service.price !==
                                    undefined;

                            return (
                                <Link
                                    key={
                                        service.slug
                                    }
                                    href={route(
                                        "public.services.show",
                                        service.slug,
                                    )}
                                    className="group flex min-h-[320px] flex-col border-t border-slate-300 bg-white px-6 py-7 transition duration-200 hover:-translate-y-1 hover:border-[#2A9D8F] hover:shadow-[0_12px_35px_rgba(26,54,93,0.08)]"
                                >
                                    {/* Mode */}
                                    <div>
                                        <span className="text-xs font-semibold uppercase tracking-[0.16em] text-[#2A9D8F]">
                                            {mode}
                                        </span>
                                    </div>

                                    {/* Name */}
                                    <h3 className="susadhya-heading mt-5 text-2xl font-bold leading-snug text-[#1A365D] transition group-hover:text-[#2A9D8F]">
                                        {
                                            service.name
                                        }
                                    </h3>

                                    {/* Description */}
                                    {description && (
                                        <p className="mt-4 line-clamp-4 text-sm leading-7 text-slate-600">
                                            {
                                                description
                                            }
                                        </p>
                                    )}

                                    {/* Bottom details */}
                                    <div className="mt-auto pt-8">
                                        <div className="border-t border-slate-200 pt-5">
                                            <div className="flex items-end justify-between gap-4">
                                                <div>
                                                    <p className="text-[11px] font-medium uppercase tracking-[0.12em] text-slate-400">
                                                        Duration
                                                    </p>

                                                    <p className="mt-1 text-sm font-medium text-slate-700">
                                                        {service.duration_minutes
                                                            ? `${service.duration_minutes} minutes`
                                                            : "Professional counselling"}
                                                    </p>
                                                </div>

                                                {hasPrice && (
                                                    <div className="text-right">
                                                        <p className="text-[11px] font-medium uppercase tracking-[0.12em] text-slate-400">
                                                            Fee
                                                        </p>

                                                        <p className="mt-1 text-sm font-semibold text-[#1A365D]">
                                                            {
                                                                service.currency
                                                            }{" "}
                                                            {
                                                                service.price
                                                            }
                                                        </p>
                                                    </div>
                                                )}
                                            </div>

                                            <div className="mt-5 flex items-center text-sm font-semibold text-[#2A9D8F]">
                                                Learn more
                                                <span className="ml-2 transition-transform duration-200 group-hover:translate-x-1">
                                                    →
                                                </span>
                                            </div>
                                        </div>
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

function Counsellors({ section }) {
    const counsellors =
        section.payload ?? [];

    return (
        <section className="bg-[#E6F2FA] px-4 py-16 sm:px-6 lg:px-8 lg:py-20">
            <div className="mx-auto max-w-7xl">
                <SectionHeading
                    section={section}
                />

                <div className="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    {counsellors.map(
                        (counsellor) => (
                            <Link
                                key={
                                    counsellor.uuid
                                }
                                href={route(
                                    "public.counsellors.show",
                                    counsellor.uuid,
                                )}
                                className="overflow-hidden rounded-2xl bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-md"
                            >
                                <div className="aspect-[4/3] bg-[#F7F9F4]">
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
                                        <div className="flex h-full items-center justify-center text-5xl font-bold text-[#2A9D8F]">
                                            {counsellor.name
                                                .charAt(
                                                    0,
                                                )
                                                .toUpperCase()}
                                        </div>
                                    )}
                                </div>

                                <div className="p-5">
                                    <h3 className="susadhya-heading text-xl font-bold">
                                        {
                                            counsellor.name
                                        }
                                    </h3>

                                    {counsellor.headline && (
                                        <p className="mt-1 text-sm font-medium text-[#2A9D8F]">
                                            {
                                                counsellor.headline
                                            }
                                        </p>
                                    )}

                                    {counsellor.bio && (
                                        <p className="mt-3 line-clamp-3 text-sm leading-6 text-slate-600">
                                            {
                                                counsellor.bio
                                            }
                                        </p>
                                    )}
                                </div>
                            </Link>
                        ),
                    )}
                </div>

                <div className="mt-10 text-center">
                    <Link
                        href={route(
                            "public.counsellors.index",
                        )}
                        className="inline-flex rounded-xl bg-[#2A9D8F] px-5 py-2.5 text-sm font-semibold text-white"
                    >
                        Meet our counsellors
                    </Link>
                </div>
            </div>
        </section>
    );
}

function FAQ({ section }) {
    const [open, setOpen] =
        useState(null);

    const faqs =
        section.payload ?? [];

    return (
        <section className="px-4 py-16 sm:px-6 lg:px-8 lg:py-20">
            <div className="mx-auto max-w-4xl">
                <SectionHeading
                    section={section}
                />

                <div className="space-y-3">
                    {faqs.map((faq) => {
                        const active =
                            open === faq.uuid;

                        return (
                            <div
                                key={faq.uuid}
                                className="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm"
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
                                    className="flex w-full items-center justify-between gap-4 px-5 py-4 text-left font-semibold text-[#1A365D]"
                                >
                                    <span>
                                        {
                                            faq.question
                                        }
                                    </span>

                                    <span className="text-xl text-[#2A9D8F]">
                                        {active
                                            ? "−"
                                            : "+"}
                                    </span>
                                </button>

                                {active && (
                                    <p className="border-t border-slate-100 bg-[#F7F9F4]/50 px-5 py-5 text-sm leading-7 text-slate-600">
                                        {
                                            faq.answer
                                        }
                                    </p>
                                )}
                            </div>
                        );
                    })}
                </div>
            </div>
        </section>
    );
}

function Testimonials({ section }) {
    const testimonials =
        section.payload ?? [];

    if (
        testimonials.length === 0
    ) {
        return null;
    }

    return (
        <section className="bg-[#F7F9F4] px-4 py-16 sm:px-6 lg:px-8 lg:py-20">
            <div className="mx-auto max-w-7xl">
                <SectionHeading
                    section={section}
                />

                <div className="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                    {testimonials.map(
                        (testimonial) => (
                            <div
                                key={
                                    testimonial.uuid
                                }
                                className="rounded-2xl border border-slate-200 bg-white p-7 shadow-sm"
                            >
                                {testimonial.rating && (
                                    <div className="flex gap-1 text-[#7FB069]">
                                        {Array.from({
                                            length:
                                                testimonial.rating,
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

                                <p className="mt-5 text-sm leading-7 text-slate-700">
                                    “
                                    {
                                        testimonial.quote
                                    }
                                    ”
                                </p>

                                <p className="mt-6 font-semibold text-[#1A365D]">
                                    {
                                        testimonial.display_name
                                    }
                                </p>

                                {testimonial.role_label && (
                                    <p className="mt-1 text-xs text-slate-500">
                                        {
                                            testimonial.role_label
                                        }
                                    </p>
                                )}
                            </div>
                        ),
                    )}
                </div>
            </div>
        </section>
    );
}

function CTA({ section }) {
    const content =
        section.content ?? {};

    return (
        <section className="px-4 py-16 sm:px-6 lg:px-8">
            <div className="relative mx-auto max-w-6xl overflow-hidden rounded-[2rem] bg-[#1A365D] px-6 py-14 text-center text-white shadow-sm sm:px-12 lg:py-16">
                <div className="absolute -left-20 -top-20 h-56 w-56 rounded-full bg-[#2A9D8F]/20" />

                <div className="absolute -bottom-24 -right-20 h-64 w-64 rounded-full bg-[#7FB069]/15" />

                <div className="relative">
                    <h2 className="susadhya-heading mx-auto max-w-3xl text-3xl font-bold text-white sm:text-4xl">
                        {section.heading}
                    </h2>

                    {section.subheading && (
                        <p className="mx-auto mt-5 max-w-2xl text-sm leading-7 text-slate-200">
                            {
                                section.subheading
                            }
                        </p>
                    )}

                    {content.primary_cta_url &&
                        content.primary_cta_label && (
                            <Link
                                href={
                                    content.primary_cta_url
                                }
                                className="mt-8 inline-flex rounded-xl bg-[#2A9D8F] px-6 py-3 text-sm font-semibold text-white shadow-sm hover:bg-[#23897D]"
                            >
                                {
                                    content.primary_cta_label
                                }
                            </Link>
                        )}
                </div>
            </div>
        </section>
    );
}

function RichText({ section }) {
    const body =
        section.content?.body ?? "";

    return (
        <section className="px-4 py-14 sm:px-6 lg:px-8">
            <div className="mx-auto max-w-4xl rounded-2xl bg-white p-6 shadow-sm sm:p-8">
                <SectionHeading
                    section={section}
                />

                <div className="whitespace-pre-line text-base leading-8 text-slate-700">
                    {body}
                </div>
            </div>
        </section>
    );
}

export default function PublicSectionRenderer({
    sections = [],
}) {
    return sections.map(
        (section) => {
            switch (section.type) {
                case "hero":
                    return (
                        <Hero
                            key={
                                section.uuid
                            }
                            section={
                                section
                            }
                        />
                    );

                case "stats":
                    return (
                        <Stats
                            key={
                                section.uuid
                            }
                            section={
                                section
                            }
                        />
                    );

                case "steps":
                    return (
                        <Steps
                            key={
                                section.uuid
                            }
                            section={
                                section
                            }
                        />
                    );

                case "feature_grid":
                    return (
                        <FeatureGrid
                            key={
                                section.uuid
                            }
                            section={
                                section
                            }
                        />
                    );

                case "image_text":
                    return (
                        <ImageText
                            key={
                                section.uuid
                            }
                            section={
                                section
                            }
                        />
                    );

                case "services":
                    return (
                        <Services
                            key={
                                section.uuid
                            }
                            section={
                                section
                            }
                        />
                    );

                case "counsellors":
                    return (
                        <Counsellors
                            key={
                                section.uuid
                            }
                            section={
                                section
                            }
                        />
                    );

                case "faq":
                    return (
                        <FAQ
                            key={
                                section.uuid
                            }
                            section={
                                section
                            }
                        />
                    );

                case "testimonials":
                    return (
                        <Testimonials
                            key={
                                section.uuid
                            }
                            section={
                                section
                            }
                        />
                    );

                case "cta":
                    return (
                        <CTA
                            key={
                                section.uuid
                            }
                            section={
                                section
                            }
                        />
                    );

                case "rich_text":
                    return (
                        <RichText
                            key={
                                section.uuid
                            }
                            section={
                                section
                            }
                        />
                    );

                default:
                    return null;
            }
        },
    );
}
