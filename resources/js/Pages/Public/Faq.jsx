import {
    MarketingHero,
} from "@/Components/Public/FixedSections";
import PublicSeo from "@/Components/Public/PublicSeo";
import PublicLayout from "@/Layouts/PublicLayout";
import { useState } from "react";

export default function Faq({
    faqGroups,
    seo,
    websiteContent,
}) {
    const [open, setOpen] =
        useState(null);

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

            {/* Fixed CMS-controlled hero */}
            {hero && (
                <MarketingHero
                    section={hero}
                />
            )}

            {/* FAQ content */}
            <section className="bg-[#F7F9F4] px-4 py-16 sm:px-6 lg:px-8 lg:py-20">
                <div className="mx-auto max-w-5xl">
                    <div className="mx-auto max-w-3xl text-center">
                        <p className="text-xs font-semibold uppercase tracking-[0.2em] text-[#2A9D8F]">
                            Helpful Information
                        </p>

                        <h2 className="susadhya-heading mt-3 text-3xl font-bold leading-tight text-[#1A365D] sm:text-4xl">
                            Common questions about
                            counselling and Susadhya
                        </h2>

                        <p className="mx-auto mt-4 max-w-2xl text-sm leading-7 text-slate-600">
                            Browse answers about
                            counselling services,
                            appointments, privacy,
                            using the platform and
                            what to expect when
                            getting started.
                        </p>
                    </div>

                    <div className="mt-12 space-y-14">
                        {Object.entries(
                            faqGroups ?? {},
                        ).map(
                            ([
                                category,
                                items,
                            ]) => (
                                <div
                                    key={
                                        category
                                    }
                                >
                                    <div className="border-b border-slate-200 pb-4">
                                        <p className="text-xs font-semibold uppercase tracking-[0.16em] text-[#2A9D8F]">
                                            FAQ Category
                                        </p>

                                        <h3 className="susadhya-heading mt-2 text-2xl font-bold text-[#1A365D]">
                                            {
                                                category
                                            }
                                        </h3>
                                    </div>

                                    <div className="divide-y divide-slate-200 border-b border-slate-200">
                                        {items.map(
                                            (
                                                faq,
                                            ) => {
                                                const active =
                                                    open ===
                                                    faq.uuid;

                                                return (
                                                    <div
                                                        key={
                                                            faq.uuid
                                                        }
                                                        className="bg-white"
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
                                                            className="flex w-full items-start justify-between gap-6 py-6 text-left"
                                                        >
                                                            <span className="susadhya-heading pr-4 text-lg font-bold leading-7 text-[#1A365D] sm:text-xl">
                                                                {
                                                                    faq.question
                                                                }
                                                            </span>

                                                            <span
                                                                className={`mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-full border text-lg font-medium transition ${
                                                                    active
                                                                        ? "border-[#2A9D8F] bg-[#2A9D8F] text-white"
                                                                        : "border-slate-300 bg-white text-[#2A9D8F]"
                                                                }`}
                                                            >
                                                                {active
                                                                    ? "−"
                                                                    : "+"}
                                                            </span>
                                                        </button>

                                                        {active && (
                                                            <div className="pb-7 pr-12">
                                                                <p className="max-w-3xl whitespace-pre-line text-sm leading-7 text-slate-600">
                                                                    {
                                                                        faq.answer
                                                                    }
                                                                </p>
                                                            </div>
                                                        )}
                                                    </div>
                                                );
                                            },
                                        )}
                                    </div>
                                </div>
                            ),
                        )}
                    </div>

                    {Object.keys(
                        faqGroups ?? {},
                    ).length === 0 && (
                        <div className="mt-12 border-y border-slate-200 bg-white py-16 text-center">
                            <h3 className="susadhya-heading text-xl font-bold text-[#1A365D]">
                                No FAQs available
                            </h3>

                            <p className="mx-auto mt-3 max-w-md text-sm leading-7 text-slate-500">
                                Frequently asked
                                questions will appear
                                here once they are
                                published.
                            </p>
                        </div>
                    )}
                </div>
            </section>
        </PublicLayout>
    );
}
