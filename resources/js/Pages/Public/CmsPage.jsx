import PublicSectionRenderer from "@/Components/Public/PublicSectionRenderer";
import PublicSeo from "@/Components/Public/PublicSeo";
import PublicVisual from "@/Components/Public/PublicVisual";
import PublicLayout from "@/Layouts/PublicLayout";

export default function CmsPage({
    page,
}) {
    const legal =
        page.template === "legal";

    return (
        <PublicLayout>
            <PublicSeo
                seo={page.seo}
            />

            {legal ? (
                <section className="border-b border-slate-200 bg-[#E6F2FA] px-4 py-14 sm:px-6 lg:px-8">
                    <div className="mx-auto max-w-4xl">
                        <h1 className="susadhya-heading text-4xl font-bold sm:text-5xl">
                            {page.title}
                        </h1>

                        {page.excerpt && (
                            <p className="mt-5 max-w-2xl leading-7 text-slate-600">
                                {
                                    page.excerpt
                                }
                            </p>
                        )}
                    </div>
                </section>
            ) : (
                <section className="overflow-hidden bg-[#F7F9F4] px-4 py-16 sm:px-6 lg:px-8 lg:py-20">
                    <div className="mx-auto grid max-w-7xl items-center gap-12 lg:grid-cols-[1fr_.8fr]">
                        <div>
                            <p className="text-xs font-semibold uppercase tracking-[0.18em] text-[#2A9D8F]">
                                Susadhya Counselling
                            </p>

                            <h1 className="susadhya-heading mt-4 text-4xl font-bold leading-tight sm:text-5xl">
                                {page.title}
                            </h1>

                            {page.excerpt && (
                                <p className="mt-6 max-w-2xl text-lg leading-8 text-slate-600">
                                    {
                                        page.excerpt
                                    }
                                </p>
                            )}
                        </div>

                        <PublicVisual
                            variant={
                                page.slug ===
                                "about"
                                    ? "session"
                                    : "journey"
                            }
                        />
                    </div>
                </section>
            )}

            {page.body && (
                <section className="px-4 py-14 sm:px-6 lg:px-8">
                    <div className="mx-auto max-w-4xl">
                        <div className="whitespace-pre-line text-base leading-8 text-slate-700">
                            {
                                page.body
                            }
                        </div>
                    </div>
                </section>
            )}

            <PublicSectionRenderer
                sections={
                    page.sections
                }
            />
        </PublicLayout>
    );
}
