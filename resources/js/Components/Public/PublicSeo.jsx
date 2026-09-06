import { Head, usePage } from "@inertiajs/react";

export default function PublicSeo({
    seo = {},
}) {
    const { publicSite } =
        usePage().props;

    const siteName =
        publicSite?.site_name ??
        "Susadhya Counselling";

    const title =
        seo.title
            ? `${seo.title} | ${siteName}`
            : publicSite?.default_meta_title ??
              siteName;

    const description =
        seo.description ??
        publicSite?.default_meta_description ??
        "";

    const image =
        seo.og_image ??
        publicSite?.default_og_image_url ??
        publicSite?.logo_url;

    const robots =
        seo.robots ??
        "index,follow";

    return (
        <Head>
            <title>{title}</title>

            {description && (
                <meta
                    name="description"
                    content={description}
                />
            )}

            <meta
                name="robots"
                content={robots}
            />

            {seo.canonical && (
                <link
                    rel="canonical"
                    href={seo.canonical}
                />
            )}

            <meta
                property="og:type"
                content="website"
            />

            <meta
                property="og:site_name"
                content={siteName}
            />

            <meta
                property="og:title"
                content={
                    seo.og_title ??
                    seo.title ??
                    title
                }
            />

            {description && (
                <meta
                    property="og:description"
                    content={
                        seo.og_description ??
                        description
                    }
                />
            )}

            {image && (
                <meta
                    property="og:image"
                    content={image}
                />
            )}

            {seo.canonical && (
                <meta
                    property="og:url"
                    content={seo.canonical}
                />
            )}

            <meta
                name="twitter:card"
                content="summary_large_image"
            />

            {publicSite?.favicon_url && (
                <link
                    rel="icon"
                    href={
                        publicSite.favicon_url
                    }
                />
            )}
        </Head>
    );
}
