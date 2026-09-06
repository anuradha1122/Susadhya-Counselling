import {
    FinalCta,
    ImageTextSection,
    MarketingHero,
    TrustBand,
} from "@/Components/Public/FixedSections";
import PublicSeo from "@/Components/Public/PublicSeo";
import PublicLayout from "@/Layouts/PublicLayout";

export default function About({
    page,
    sections,
}) {
    const ctaImage =
        sections?.mission
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

            <ImageTextSection
                section={
                    sections?.mission
                }
                imageLeft
            />

            <TrustBand />

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
