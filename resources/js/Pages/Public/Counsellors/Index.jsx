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
    BriefcaseBusiness,
    MapPin,
    Search,
} from "lucide-react";
import { useState } from "react";

export default function Index({
    counsellors,
    filters,
    seo,
    websiteContent,
}) {
    const [search, setSearch] =
        useState(
            filters?.search ?? "",
        );

    const submit = (event) => {
        event.preventDefault();

        router.get(
            route(
                "public.counsellors.index",
            ),
            {
                search:
                    search || undefined,
            },
            {
                preserveState: true,
                preserveScroll: true,
                replace: true,
            },
        );
    };

    const clearSearch = () => {
        setSearch("");

        router.get(
            route(
                "public.counsellors.index",
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

            {/* Fixed CMS Hero */}
            {hero && (
                <MarketingHero
                    section={hero}
                />
            )}

            {/* Search */}
            <section className="bg-[#F7F9F4] px-4 py-10 sm:px-6 lg:px-8 lg:py-14">
                <div className="mx-auto max-w-7xl">
                    <div className="mx-auto max-w-3xl text-center">
                        <p className="text-xs font-semibold uppercase tracking-[0.2em] text-[#2A9D8F]">
                            Browse Counsellors
                        </p>

                        <h2 className="susadhya-heading mt-3 text-3xl font-bold leading-tight text-[#1A365D] sm:text-4xl">
                            Find professional
                            support that feels
                            right for you.
                        </h2>

                        <p className="mx-auto mt-4 max-w-2xl text-sm leading-7 text-slate-600">
                            Search public
                            counsellor profiles
                            using a name,
                            speciality or area of
                            professional support.
                        </p>
                    </div>

                    <form
                        onSubmit={submit}
                        className="mx-auto mt-8 flex max-w-3xl flex-col gap-3 sm:flex-row"
                    >
                        <div className="relative flex-1">
                            <Search className="pointer-events-none absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />

                            <input
                                type="search"
                                value={search}
                                onChange={(
                                    event,
                                ) =>
                                    setSearch(
                                        event
                                            .target
                                            .value,
                                    )
                                }
                                placeholder="Search by counsellor, expertise or speciality"
                                className="h-12 w-full rounded-md border border-slate-300 bg-white pl-11 pr-4 text-sm text-slate-700 shadow-sm placeholder:text-slate-400 focus:border-[#2A9D8F] focus:ring-[#2A9D8F]"
                            />
                        </div>

                        <button
                            type="submit"
                            className="h-12 rounded-md bg-[#2A9D8F] px-7 text-sm font-semibold text-white shadow-sm transition hover:bg-[#23877C]"
                        >
                            Search
                        </button>

                        {filters?.search && (
                            <button
                                type="button"
                                onClick={
                                    clearSearch
                                }
                                className="h-12 rounded-md border border-slate-300 bg-white px-5 text-sm font-semibold text-slate-600 transition hover:border-[#2A9D8F] hover:text-[#2A9D8F]"
                            >
                                Clear
                            </button>
                        )}
                    </form>
                </div>
            </section>

            {/* Counsellor Results */}
            <section className="bg-white px-4 py-16 sm:px-6 lg:px-8 lg:py-20">
                <div className="mx-auto max-w-7xl">
                    <div className="mb-10 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <p className="text-xs font-semibold uppercase tracking-[0.2em] text-[#2A9D8F]">
                                Professional
                                Profiles
                            </p>

                            <h2 className="susadhya-heading mt-3 text-3xl font-bold text-[#1A365D] sm:text-4xl">
                                Available
                                Counsellors
                            </h2>
                        </div>

                        <p className="text-sm text-slate-500">
                            {counsellors?.total ??
                                counsellors
                                    ?.data
                                    ?.length ??
                                0}{" "}
                            counsellor
                            {(
                                counsellors?.total ??
                                counsellors
                                    ?.data
                                    ?.length ??
                                0
                            ) === 1
                                ? ""
                                : "s"}{" "}
                            found
                        </p>
                    </div>

                    {counsellors?.data
                        ?.length > 0 ? (
                        <div className="grid gap-x-6 gap-y-10 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                            {counsellors.data.map(
                                (
                                    counsellor,
                                ) => {
                                    const specialties =
                                        Array.isArray(
                                            counsellor.specialties,
                                        )
                                            ? counsellor.specialties
                                                  .map(
                                                      (
                                                          specialty,
                                                      ) => {
                                                          if (
                                                              typeof specialty ===
                                                              "string"
                                                          ) {
                                                              return {
                                                                  id:
                                                                      specialty,
                                                                  name:
                                                                      specialty,
                                                              };
                                                          }

                                                          if (
                                                              specialty &&
                                                              typeof specialty ===
                                                                  "object"
                                                          ) {
                                                              return {
                                                                  id:
                                                                      specialty.id ??
                                                                      specialty.name,
                                                                  name:
                                                                      specialty.name ??
                                                                      "",
                                                              };
                                                          }

                                                          return null;
                                                      },
                                                  )
                                                  .filter(
                                                      (
                                                          specialty,
                                                      ) =>
                                                          specialty?.name,
                                                  )
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
                                            className="group block"
                                        >
                                            {/* Profile Image */}
                                            <div className="aspect-[4/5] overflow-hidden rounded-t-2xl bg-[#E6F2FA]">
                                                {counsellor.profile_image_url ? (
                                                    <img
                                                        src={
                                                            counsellor.profile_image_url
                                                        }
                                                        alt={
                                                            counsellor.name
                                                        }
                                                        className="h-full w-full object-cover transition duration-300 group-hover:scale-[1.025]"
                                                    />
                                                ) : (
                                                    <div className="flex h-full items-center justify-center bg-gradient-to-br from-[#E6F2FA] to-[#F7F9F4]">
                                                        <span className="susadhya-heading text-7xl font-bold text-[#2A9D8F]">
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

                                            {/* Profile Info */}
                                            <div className="rounded-b-2xl border border-t-0 border-slate-200 bg-white px-5 pb-5 pt-5 transition group-hover:border-[#CFE8E4] group-hover:shadow-md">
                                                <h3 className="susadhya-heading text-xl font-bold leading-snug text-[#1A365D] transition group-hover:text-[#2A9D8F]">
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

                                                <div className="mt-4 space-y-2">
                                                    {counsellor.years_of_experience !==
                                                        null &&
                                                        counsellor.years_of_experience !==
                                                            undefined && (
                                                            <div className="flex items-center gap-2 text-xs text-slate-500">
                                                                <BriefcaseBusiness className="h-4 w-4 text-[#2A9D8F]" />

                                                                <span>
                                                                    {
                                                                        counsellor.years_of_experience
                                                                    }{" "}
                                                                    years
                                                                    experience
                                                                </span>
                                                            </div>
                                                        )}

                                                    {counsellor.city && (
                                                        <div className="flex items-center gap-2 text-xs text-slate-500">
                                                            <MapPin className="h-4 w-4 text-[#2A9D8F]" />

                                                            <span>
                                                                {
                                                                    counsellor.city
                                                                }
                                                            </span>
                                                        </div>
                                                    )}
                                                </div>

                                                {counsellor.bio && (
                                                    <p className="mt-4 line-clamp-3 text-sm leading-6 text-slate-600">
                                                        {
                                                            counsellor.bio
                                                        }
                                                    </p>
                                                )}

                                                {specialties.length >
                                                    0 && (
                                                    <div className="mt-4 flex flex-wrap gap-2">
                                                        {specialties
                                                            .slice(
                                                                0,
                                                                3,
                                                            )
                                                            .map(
                                                                (
                                                                    specialty,
                                                                ) => (
                                                                    <span
                                                                        key={
                                                                            specialty.id
                                                                        }
                                                                        className="rounded-full bg-[#E6F2FA] px-2.5 py-1 text-xs font-medium text-[#1A365D]"
                                                                    >
                                                                        {
                                                                            specialty.name
                                                                        }
                                                                    </span>
                                                                ),
                                                            )}

                                                        {specialties.length >
                                                            3 && (
                                                            <span className="rounded-full bg-[#F7F9F4] px-2.5 py-1 text-xs font-medium text-slate-500">
                                                                +
                                                                {specialties.length -
                                                                    3}{" "}
                                                                more
                                                            </span>
                                                        )}
                                                    </div>
                                                )}

                                                <div className="mt-5 border-t border-slate-100 pt-4 text-sm font-semibold text-[#2A9D8F]">
                                                    View profile{" "}
                                                    <span className="inline-block transition-transform group-hover:translate-x-1">
                                                        →
                                                    </span>
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
                                No counsellors
                                found
                            </h3>

                            <p className="mx-auto mt-2 max-w-md text-sm leading-6 text-slate-500">
                                No public
                                counsellor profiles
                                match your current
                                search.
                            </p>

                            {filters?.search && (
                                <button
                                    type="button"
                                    onClick={
                                        clearSearch
                                    }
                                    className="mt-5 text-sm font-semibold text-[#2A9D8F]"
                                >
                                    Clear search
                                    and view all
                                    counsellors
                                </button>
                            )}
                        </div>
                    )}

                    {counsellors?.links
                        ?.length > 3 && (
                        <div className="mt-12 border-t border-slate-200 pt-8">
                            <Pagination
                                links={
                                    counsellors.links
                                }
                            />
                        </div>
                    )}
                </div>
            </section>
        </PublicLayout>
    );
}
