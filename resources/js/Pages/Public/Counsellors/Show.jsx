import PublicSeo from "@/Components/Public/PublicSeo";
import PublicLayout from "@/Layouts/PublicLayout";
import { Link } from "@inertiajs/react";
import {
    ArrowLeft,
    BadgeCheck,
    BriefcaseBusiness,
    Languages,
    MapPin,
    ShieldCheck,
    Sparkles,
    UserRound,
} from "lucide-react";

function ProfileImage({ counsellor }) {
    if (counsellor.profile_image_url) {
        return (
            <img
                src={counsellor.profile_image_url}
                alt={`${counsellor.name} counsellor profile`}
                className="h-full w-full object-cover"
            />
        );
    }

    return (
        <div className="flex h-full w-full items-center justify-center bg-gradient-to-br from-[#E6F2FA] to-[#F7F9F4]">
            <div className="text-center">
                <UserRound className="mx-auto h-16 w-16 text-[#2A9D8F]" />

                <span className="susadhya-heading mt-3 block text-4xl font-bold text-[#1A365D]">
                    {counsellor.name
                        ?.charAt(0)
                        ?.toUpperCase() ?? "C"}
                </span>
            </div>
        </div>
    );
}

function DetailItem({
    icon: Icon,
    label,
    value,
}) {
    if (
        value === null ||
        value === undefined ||
        value === ""
    ) {
        return null;
    }

    return (
        <div className="flex items-start gap-3">
            <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[#E6F2FA] text-[#2A9D8F]">
                <Icon className="h-5 w-5" />
            </div>

            <div>
                <p className="text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">
                    {label}
                </p>

                <p className="mt-1 text-sm font-semibold text-[#1A365D]">
                    {value}
                </p>
            </div>
        </div>
    );
}

function Specializations({
    specialties = [],
}) {
    if (!specialties.length) {
        return null;
    }

    return (
        <section className="rounded-[1.75rem] border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
            <div className="flex items-center gap-3">
                <div className="flex h-11 w-11 items-center justify-center rounded-xl bg-[#E6F2FA] text-[#2A9D8F]">
                    <Sparkles className="h-5 w-5" />
                </div>

                <div>
                    <p className="text-xs font-semibold uppercase tracking-[0.18em] text-[#2A9D8F]">
                        Areas of Support
                    </p>

                    <h2 className="susadhya-heading mt-1 text-2xl font-bold text-[#1A365D]">
                        Professional Specializations
                    </h2>
                </div>
            </div>

            <div className="mt-6 flex flex-wrap gap-2.5">
                {specialties.map(
                    (specialty) => (
                        <span
                            key={
                                specialty.id ??
                                specialty.name
                            }
                            className="rounded-full border border-[#CFE8E4] bg-[#F0F8F6] px-4 py-2 text-sm font-medium text-[#1A365D]"
                        >
                            {specialty.name}
                        </span>
                    ),
                )}
            </div>
        </section>
    );
}

function LanguageSection({
    languages = [],
}) {
    if (!languages.length) {
        return null;
    }

    return (
        <section className="rounded-[1.75rem] border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
            <div className="flex items-center gap-3">
                <div className="flex h-11 w-11 items-center justify-center rounded-xl bg-[#E6F2FA] text-[#2A9D8F]">
                    <Languages className="h-5 w-5" />
                </div>

                <div>
                    <p className="text-xs font-semibold uppercase tracking-[0.18em] text-[#2A9D8F]">
                        Communication
                    </p>

                    <h2 className="susadhya-heading mt-1 text-2xl font-bold text-[#1A365D]">
                        Languages
                    </h2>
                </div>
            </div>

            <div className="mt-6 divide-y divide-slate-100">
                {languages.map(
                    (language) => (
                        <div
                            key={
                                language.id ??
                                language.name
                            }
                            className="flex items-center justify-between gap-4 py-4 first:pt-0 last:pb-0"
                        >
                            <div>
                                <p className="font-semibold text-[#1A365D]">
                                    {language.name}
                                </p>

                                {language.code && (
                                    <p className="mt-0.5 text-xs uppercase tracking-wider text-slate-400">
                                        {language.code}
                                    </p>
                                )}
                            </div>

                            {language.proficiency && (
                                <span className="rounded-full bg-[#F7F9F4] px-3 py-1.5 text-xs font-semibold capitalize text-slate-600">
                                    {language.proficiency}
                                </span>
                            )}
                        </div>
                    ),
                )}
            </div>
        </section>
    );
}

function QualificationsSection({
    qualifications = [],
}) {
    if (!qualifications.length) {
        return null;
    }

    return (
        <section className="rounded-[1.75rem] border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
            <div className="flex items-center gap-3">
                <div className="flex h-11 w-11 items-center justify-center rounded-xl bg-[#E6F2FA] text-[#2A9D8F]">
                    <BadgeCheck className="h-5 w-5" />
                </div>

                <div>
                    <p className="text-xs font-semibold uppercase tracking-[0.18em] text-[#2A9D8F]">
                        Professional Background
                    </p>

                    <h2 className="susadhya-heading mt-1 text-2xl font-bold text-[#1A365D]">
                        Qualifications
                    </h2>
                </div>
            </div>

            <div className="mt-7 space-y-4">
                {qualifications.map(
                    (
                        qualification,
                        index,
                    ) => (
                        <div
                            key={
                                qualification.id ??
                                `${qualification.qualification}-${index}`
                            }
                            className="rounded-2xl border border-slate-100 bg-[#F7F9F4] p-5"
                        >
                            <div className="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                                <div>
                                    <h3 className="font-semibold text-[#1A365D]">
                                        {
                                            qualification.qualification
                                        }
                                    </h3>

                                    {qualification.institution && (
                                        <p className="mt-1 text-sm text-slate-600">
                                            {
                                                qualification.institution
                                            }
                                        </p>
                                    )}

                                    {qualification.field_of_study && (
                                        <p className="mt-1 text-sm text-slate-500">
                                            {
                                                qualification.field_of_study
                                            }
                                        </p>
                                    )}
                                </div>

                                {qualification.year_completed && (
                                    <span className="shrink-0 rounded-full bg-white px-3 py-1 text-xs font-semibold text-slate-500">
                                        {
                                            qualification.year_completed
                                        }
                                    </span>
                                )}
                            </div>
                        </div>
                    ),
                )}
            </div>
        </section>
    );
}

function BookingCard({ counsellor }) {
    return (
        <div className="overflow-hidden rounded-[1.75rem] bg-[#1A365D] shadow-sm">
            <div className="p-6 sm:p-7">
                <div className="flex h-11 w-11 items-center justify-center rounded-xl bg-white/10 text-white">
                    <ShieldCheck className="h-5 w-5" />
                </div>

                <h3 className="susadhya-heading mt-5 text-2xl font-bold text-white">
                    Ready to speak with{" "}
                    {counsellor.name?.split(
                        " ",
                    )?.[0] ??
                        "this counsellor"}
                    ?
                </h3>

                <p className="mt-3 text-sm leading-7 text-slate-200">
                    Sign in to your secure
                    Susadhya account to view
                    appointment availability
                    and continue with the
                    booking process.
                </p>

                <Link
                    href={route("login")}
                    className="mt-6 inline-flex w-full items-center justify-center rounded-xl bg-[#2A9D8F] px-5 py-3 text-sm font-semibold text-white transition hover:bg-[#23877C]"
                >
                    Sign in to Book
                </Link>

                <Link
                    href={route(
                        "public.counsellors.index",
                    )}
                    className="mt-3 inline-flex w-full items-center justify-center rounded-xl border border-white/20 px-5 py-3 text-sm font-semibold text-white transition hover:bg-white/10"
                >
                    View Other Counsellors
                </Link>
            </div>
        </div>
    );
}

export default function Show({
    counsellor,
    seo,
}) {
    const specialties =
        Array.isArray(
            counsellor.specialties,
        )
            ? counsellor.specialties
            : [];

    const languages =
        Array.isArray(
            counsellor.languages,
        )
            ? counsellor.languages
            : [];

    const qualifications =
        Array.isArray(
            counsellor.qualifications,
        )
            ? counsellor.qualifications
            : [];

    return (
        <PublicLayout>
            <PublicSeo seo={seo} />

            {/* Profile Hero */}
            <section className="relative overflow-hidden bg-[#E6F2FA] px-4 py-10 sm:px-6 lg:px-8 lg:py-16">
                <div className="absolute -right-40 -top-40 h-96 w-96 rounded-full bg-white/40" />

                <div className="relative mx-auto max-w-7xl">
                    <Link
                        href={route(
                            "public.counsellors.index",
                        )}
                        className="inline-flex items-center gap-2 text-sm font-semibold text-[#2A9D8F] transition hover:text-[#23877C]"
                    >
                        <ArrowLeft className="h-4 w-4" />
                        All counsellors
                    </Link>

                    <div className="mt-8 grid gap-8 lg:grid-cols-[360px_1fr] lg:items-center lg:gap-14">
                        <div className="mx-auto w-full max-w-sm lg:mx-0">
                            <div className="aspect-[4/5] overflow-hidden rounded-[2rem] border-4 border-white bg-white shadow-xl shadow-slate-900/5">
                                <ProfileImage
                                    counsellor={
                                        counsellor
                                    }
                                />
                            </div>
                        </div>

                        <div className="max-w-3xl">
                            <p className="text-xs font-semibold uppercase tracking-[0.22em] text-[#2A9D8F]">
                                Professional
                                Counsellor
                            </p>

                            <h1 className="susadhya-heading mt-3 text-4xl font-bold leading-tight text-[#1A365D] sm:text-5xl lg:text-6xl">
                                {
                                    counsellor.name
                                }
                            </h1>

                            {counsellor.headline && (
                                <p className="mt-4 text-lg font-semibold text-[#2A9D8F] sm:text-xl">
                                    {
                                        counsellor.headline
                                    }
                                </p>
                            )}

                            {counsellor.bio && (
                                <p className="mt-6 max-w-2xl text-base leading-8 text-slate-600">
                                    {counsellor.bio
                                        .length >
                                    260
                                        ? `${counsellor.bio.slice(
                                              0,
                                              260,
                                          )}…`
                                        : counsellor.bio}
                                </p>
                            )}

                            <div className="mt-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                                {counsellor.years_of_experience !==
                                    null &&
                                    counsellor.years_of_experience !==
                                        undefined && (
                                        <DetailItem
                                            icon={
                                                BriefcaseBusiness
                                            }
                                            label="Experience"
                                            value={`${counsellor.years_of_experience} years`}
                                        />
                                    )}

                                <DetailItem
                                    icon={
                                        BadgeCheck
                                    }
                                    label="Registration"
                                    value={
                                        counsellor.registration_number
                                    }
                                />

                                <DetailItem
                                    icon={
                                        MapPin
                                    }
                                    label="Location"
                                    value={
                                        counsellor.city
                                    }
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            {/* Main Profile */}
            <section className="bg-[#F7F9F4] px-4 py-14 sm:px-6 lg:px-8 lg:py-20">
                <div className="mx-auto grid max-w-7xl gap-8 lg:grid-cols-[minmax(0,1fr)_360px] lg:items-start">
                    <main className="space-y-7">
                        {/* About */}
                        {counsellor.bio && (
                            <section className="rounded-[1.75rem] border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                                <p className="text-xs font-semibold uppercase tracking-[0.18em] text-[#2A9D8F]">
                                    Meet Your
                                    Counsellor
                                </p>

                                <h2 className="susadhya-heading mt-2 text-3xl font-bold text-[#1A365D]">
                                    About{" "}
                                    {
                                        counsellor.name
                                            ?.split(
                                                " ",
                                            )
                                            ?.[0]
                                    }
                                </h2>

                                <p className="mt-5 whitespace-pre-line text-base leading-8 text-slate-700">
                                    {
                                        counsellor.bio
                                    }
                                </p>
                            </section>
                        )}

                        <Specializations
                            specialties={
                                specialties
                            }
                        />

                        <QualificationsSection
                            qualifications={
                                qualifications
                            }
                        />

                        {!counsellor.bio &&
                            qualifications.length ===
                                0 && (
                                <section className="rounded-[1.75rem] border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                                    <h2 className="susadhya-heading text-2xl font-bold text-[#1A365D]">
                                        Professional
                                        Profile
                                    </h2>

                                    <p className="mt-4 leading-7 text-slate-600">
                                        Additional
                                        professional
                                        background
                                        information
                                        for this
                                        counsellor
                                        has not yet
                                        been added.
                                    </p>
                                </section>
                            )}
                    </main>

                    <aside className="space-y-6 lg:sticky lg:top-24">
                        <LanguageSection
                            languages={
                                languages
                            }
                        />

                        <BookingCard
                            counsellor={
                                counsellor
                            }
                        />

                        <div className="rounded-[1.75rem] border border-[#CFE8E4] bg-[#F0F8F6] p-6">
                            <div className="flex items-start gap-3">
                                <ShieldCheck className="mt-0.5 h-5 w-5 shrink-0 text-[#2A9D8F]" />

                                <div>
                                    <h3 className="font-semibold text-[#1A365D]">
                                        Confidential
                                        Support
                                    </h3>

                                    <p className="mt-2 text-sm leading-6 text-slate-600">
                                        Appointments,
                                        counselling
                                        records and
                                        personal
                                        information
                                        remain within
                                        Susadhya&apos;s
                                        secure client
                                        workflow.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </aside>
                </div>
            </section>
        </PublicLayout>
    );
}
