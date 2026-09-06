import {
    CounsellorsPreview,
    FaqPreview,
    FinalCta,
    ImageTextSection,
    MarketingHero,
    ServicesPreview,
    StepsSection,
    TestimonialsPreview,
    TrustBand,
} from "@/Components/Public/FixedSections";
import PublicSeo from "@/Components/Public/PublicSeo";
import PublicLayout from "@/Layouts/PublicLayout";

export default function Home({
    page,
    sections,
    services,
    counsellors,
    testimonials,
    faqs,
}) {
    const ctaImage =
        sections?.why_counselling
            ?.content
            ?.image_path ??
        sections?.privacy
            ?.content
            ?.image_path ??
        sections?.hero
            ?.content
            ?.image_path ??
        null;

    return (
        <PublicLayout>
            <PublicSeo
                seo={page?.seo}
            />

            <MarketingHero
                section={
                    sections?.hero
                }
            />

            <ServicesPreview
                section={
                    sections?.services
                }
                services={
                    services
                }
            />

            <CounsellorsPreview
                section={
                    sections?.counsellors
                }
                counsellors={
                    counsellors
                }
            />

            <TrustBand />

            <ImageTextSection
                section={
                    sections?.why_counselling ??
                    sections?.intro
                }
                imageLeft
            />

            <TestimonialsPreview
                section={
                    sections?.testimonials
                }
                testimonials={
                    testimonials
                }
            />

            <StepsSection
                section={
                    sections
                        ?.how_it_works
                }
            />

            <ImageTextSection
                section={
                    sections?.privacy
                }
                imageLeft={
                    false
                }
            />

            <FaqPreview
                section={
                    sections?.faq
                }
                faqs={faqs}
            />

            <FinalCta
                section={
                    sections?.cta
                }
                imagePath={
                    ctaImage
                }
            />
        </PublicLayout>
    );
}
