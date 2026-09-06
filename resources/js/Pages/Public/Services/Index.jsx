import Pagination from "@/Components/Pagination";
import {
    MarketingHero,
} from "@/Components/Public/FixedSections";
import PublicSeo from "@/Components/Public/PublicSeo";
import PublicLayout from "@/Layouts/PublicLayout";
import {
    Link,
    router,
} from "@inertiajs/react";
import {
    Clock,
    Laptop,
    Search,
    X,
} from "lucide-react";
import { useState } from "react";

function humanize(value) {
    return String(value ?? "")
        .replaceAll("_", " ")
        .replace(/\b\w/g, (char) =>
            char.toUpperCase(),
        );
}

export default function Index({
    services,
    filters,
    modes,
    seo,
    websiteContent,
}) {
    const [form, setForm] =
        useState({
            search:
                filters?.search ?? "",
            mode:
                filters?.mode ?? "",
        });

    const submit = (event) => {
        event.preventDefault();

        router.get(
            route(
                "public.services.index",
            ),
            {
                search:
                    form.search ||
                    undefined,

                mode:
                    form.mode ||
                    undefined,
            },
            {
                preserveState: true,
                preserveScroll: true,
                replace: true,
            },
        );
    };

    const clearFilters = () => {
        const cleared = {
            search: "",
            mode: "",
        };

        setForm(cleared);

        router.get(
            route(
                "public.services.index",
            ),
            {},
            {
                preserveState: true,
                preserveScroll: true,
                replace: true,
            },
        );
    };

    const hero =
        websiteContent
            ?.sections
            ?.hero;

    const hasFilters =
        Boolean(
            filters?.search ||
                filters?.mode,
        );

    const total =
        services?.total ??
        services?.data?.length ??
        0;

    return (
        <PublicLayout>
            <PublicSeo
                seo={
                    websiteContent
                        ?.page
                        ?.seo ??
                    seo
                }
            />

            {/* Fixed CMS-controlled hero */}
            {hero && (
                <MarketingHero
                    section={hero}
                />
            )}

            {/* Search & Filters */}
            <section className="bg-[#F7F9F4] px-4 py-10 sm:px-6 lg:px-8 lg:py-14">
                <div className="mx-auto max-w-7xl">
                    <div className="mx-auto max-w-3xl text-center">
                        <p className="text-xs font-semibold uppercase tracking-[0.2em] text-[#2A9D8F]">
                            Explore Support
                        </p>

                        <h2 className="susadhya-heading mt-3 text-3xl font-bold leading-tight text-[#1A365D] sm:text-4xl">
                            Find a counselling
                            service that fits
                            your needs.
                        </h2>

                        <p className="mx-auto mt-4 max-w-2xl text-sm leading-7 text-slate-600">
                            Search available
                            counselling services
                            or narrow the results
                            by how the service is
                            delivered.
                        </p>
                    </div>

                    <form
                        onSubmit={submit}
                        className="mx-auto mt-8 grid max-w-4xl gap-3 sm:grid-cols-[1fr_220px_auto]"
                    >
                        <div className="relative">
                            <Search className="pointer-events-none absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />

                            <input
                                value={
                                    form.search
                                }
                                onChange={(
                                    event,
                                ) =>
                                    setForm({
                                        ...form,
                                        search:
                                            event
                                                .target
                                                .value,
                                    })
                                }
                                placeholder="Search counselling services"
                                className="h-12 w-full rounded-md border border-slate-300 bg-white pl-11 pr-4 text-sm text-slate-700 shadow-sm placeholder:text-slate-400 focus:border-[#2A9D8F] focus:ring-[#2A9D8F]"
                            />
                        </div>

                        <select
                            value={
                                form.mode
                            }
                            onChange={(
                                event,
                            ) =>
                                setForm({
                                    ...form,
                                    mode:
                                        event
                                            .target
                                            .value,
                                })
                            }
                            className="h-12 rounded-md border border-slate-300 bg-white px-4 text-sm text-slate-700 shadow-sm focus:border-[#2A9D8F] focus:ring-[#2A9D8F]"
                        >
                            <option value="">
                                All delivery
                                modes
                            </option>

                            {modes.map(
                                (mode) => (
                                    <option
                                        key={
                                            mode
                                        }
                                        value={
                                            mode
                                        }
                                    >
                                        {humanize(
                                            mode,
                                        )}
                                    </option>
                                ),
                            )}
                        </select>

                        <button
                            type="submit"
                            className="h-12 rounded-md bg-[#2A9D8F] px-7 text-sm font-semibold text-white shadow-sm transition hover:bg-[#23877C]"
                        >
                            Search
                        </button>
                    </form>

                    {hasFilters && (
                        <div className="mt-4 text-center">
                            <button
                                type="button"
                                onClick={
                                    clearFilters
                                }
                                className="inline-flex items-center gap-2 text-sm font-medium text-slate-500 transition hover:text-[#2A9D8F]"
                            >
                                <X className="h-4 w-4" />
                                Clear filters
                            </button>
                        </div>
                    )}
                </div>
            </section>

            {/* Service Results */}
            <section className="bg-white px-4 py-16 sm:px-6 lg:px-8 lg:py-20">
                <div className="mx-auto max-w-7xl">
                    <div className="mb-10 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <p className="text-xs font-semibold uppercase tracking-[0.2em] text-[#2A9D8F]">
                                Professional
                                Counselling
                            </p>

                            <h2 className="susadhya-heading mt-3 text-3xl font-bold text-[#1A365D] sm:text-4xl">
                                Available
                                Services
                            </h2>
                        </div>

                        <p className="text-sm text-slate-500">
                            {total}{" "}
                            service
                            {total === 1
                                ? ""
                                : "s"}{" "}
                            found
                        </p>
                    </div>

                    {services?.data
                        ?.length > 0 ? (
                        <div className="grid gap-x-6 gap-y-8 md:grid-cols-2 lg:grid-cols-3">
                            {services.data.map(
                                (
                                    service,
                                ) => {
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
                                            className="group flex min-h-[340px] flex-col border-t border-slate-300 bg-[#F7F9F4] px-6 py-7 transition duration-200 hover:-translate-y-1 hover:border-[#2A9D8F] hover:bg-white hover:shadow-[0_14px_40px_rgba(26,54,93,0.09)]"
                                        >
                                            {/* Meta */}
                                            <div className="flex flex-wrap items-center gap-x-4 gap-y-2">
                                                {service.service_mode && (
                                                    <span className="inline-flex items-center gap-1.5 text-xs font-semibold uppercase tracking-[0.12em] text-[#2A9D8F]">
                                                        <Laptop className="h-3.5 w-3.5" />

                                                        {humanize(
                                                            service.service_mode,
                                                        )}
                                                    </span>
                                                )}

                                                {service.duration_minutes && (
                                                    <span className="inline-flex items-center gap-1.5 text-xs text-slate-500">
                                                        <Clock className="h-3.5 w-3.5" />

                                                        {
                                                            service.duration_minutes
                                                        }{" "}
                                                        min
                                                    </span>
                                                )}
                                            </div>

                                            {/* Title */}
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

                                            {/* Footer */}
                                            <div className="mt-auto pt-8">
                                                <div className="border-t border-slate-200 pt-5">
                                                    <div className="flex items-end justify-between gap-4">
                                                        <div>
                                                            <p className="text-[11px] font-medium uppercase tracking-[0.12em] text-slate-400">
                                                                Session
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

                                                    <div className="mt-5 text-sm font-semibold text-[#2A9D8F]">
                                                        Learn
                                                        more{" "}
                                                        <span className="inline-block transition-transform duration-200 group-hover:translate-x-1">
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
                    ) : (
                        <div className="border-y border-slate-200 py-16 text-center">
                            <Search className="mx-auto h-8 w-8 text-slate-300" />

                            <h3 className="susadhya-heading mt-4 text-xl font-bold text-[#1A365D]">
                                No services
                                found
                            </h3>

                            <p className="mx-auto mt-2 max-w-md text-sm leading-6 text-slate-500">
                                No counselling
                                services match the
                                current search or
                                delivery mode.
                            </p>

                            {hasFilters && (
                                <button
                                    type="button"
                                    onClick={
                                        clearFilters
                                    }
                                    className="mt-5 text-sm font-semibold text-[#2A9D8F]"
                                >
                                    Clear filters
                                    and view all
                                    services
                                </button>
                            )}
                        </div>
                    )}

                    {services?.links
                        ?.length > 3 && (
                        <div className="mt-12 border-t border-slate-200 pt-8">
                            <Pagination
                                links={
                                    services.links
                                }
                            />
                        </div>
                    )}
                </div>
            </section>
        </PublicLayout>
    );
}
