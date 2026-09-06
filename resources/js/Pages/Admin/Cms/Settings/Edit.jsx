import InputError from "@/Components/InputError";
import AdminLayout from "@/Layouts/AdminLayout";
import CmsTabs from "@/Pages/Admin/Cms/Components/CmsTabs";
import MediaPicker from "@/Pages/Admin/Cms/Components/MediaPicker";
import { Head, useForm } from "@inertiajs/react";

export default function Edit({
    settings,
    media,
}) {
    const form = useForm({
        site_name:
            settings.site_name ?? "Susadhya Counselling",
        tagline:
            settings.tagline ?? "",
        logo_path:
            settings.logo_path ?? "/images/brand/susadhya-logo.jpeg",
        favicon_path:
            settings.favicon_path ?? "",
        contact_email:
            settings.contact_email ?? "",
        contact_phone:
            settings.contact_phone ?? "",
        whatsapp_number:
            settings.whatsapp_number ?? "",
        address:
            settings.address ?? "",
        office_hours:
            settings.office_hours ?? "",
        social_links: {
            facebook:
                settings.social_links?.facebook ?? "",
            instagram:
                settings.social_links?.instagram ?? "",
            linkedin:
                settings.social_links?.linkedin ?? "",
            youtube:
                settings.social_links?.youtube ?? "",
        },
        default_meta_title:
            settings.default_meta_title ?? "",
        default_meta_description:
            settings.default_meta_description ?? "",
        default_og_image_path:
            settings.default_og_image_path ?? "",
        footer_text:
            settings.footer_text ?? "",
        emergency_notice:
            settings.emergency_notice ?? "",
        booking_cta_label:
            settings.booking_cta_label ?? "Find a Counsellor",
        booking_cta_url:
            settings.booking_cta_url ?? "/counsellors",
    });

    const submit = (event) => {
        event.preventDefault();

        form.patch(route("admin.cms.settings.update"));
    };

    return (
        <AdminLayout title="Website Settings">
            <Head title="Website Settings" />

            <div className="mx-auto max-w-5xl space-y-6">
                <CmsTabs />

                <form
                    onSubmit={submit}
                    className="space-y-6 rounded-xl border border-slate-200 bg-white p-6 shadow-sm"
                >
                    <div>
                        <h3 className="font-semibold text-slate-900">
                            Brand
                        </h3>

                        <div className="mt-4 grid gap-4 md:grid-cols-2">
                            <input
                                value={form.data.site_name}
                                onChange={(event) =>
                                    form.setData(
                                        "site_name",
                                        event.target.value,
                                    )
                                }
                                placeholder="Site name"
                                className="rounded-md border-slate-300"
                            />

                            <input
                                value={form.data.tagline}
                                onChange={(event) =>
                                    form.setData(
                                        "tagline",
                                        event.target.value,
                                    )
                                }
                                placeholder="Tagline"
                                className="rounded-md border-slate-300"
                            />

                            <MediaPicker
                                label="Website Logo"
                                value={form.data.logo_path}
                                defaultValue="/images/brand/susadhya-logo.jpeg"
                                media={media}
                                onChange={(value) =>
                                    form.setData(
                                        "logo_path",
                                        value,
                                    )
                                }
                            />

                            <input
                                value={form.data.favicon_path}
                                onChange={(event) =>
                                    form.setData(
                                        "favicon_path",
                                        event.target.value,
                                    )
                                }
                                placeholder="Favicon path"
                                className="rounded-md border-slate-300"
                            />
                        </div>
                    </div>

                    <div className="border-t border-slate-200 pt-6">
                        <h3 className="font-semibold text-slate-900">
                            Contact
                        </h3>

                        <div className="mt-4 grid gap-4 md:grid-cols-2">
                            <input
                                value={form.data.contact_email}
                                onChange={(event) =>
                                    form.setData(
                                        "contact_email",
                                        event.target.value,
                                    )
                                }
                                placeholder="Email"
                                className="rounded-md border-slate-300"
                            />

                            <input
                                value={form.data.contact_phone}
                                onChange={(event) =>
                                    form.setData(
                                        "contact_phone",
                                        event.target.value,
                                    )
                                }
                                placeholder="Phone"
                                className="rounded-md border-slate-300"
                            />

                            <input
                                value={form.data.whatsapp_number}
                                onChange={(event) =>
                                    form.setData(
                                        "whatsapp_number",
                                        event.target.value,
                                    )
                                }
                                placeholder="WhatsApp number"
                                className="rounded-md border-slate-300"
                            />
                        </div>

                        <textarea
                            rows="4"
                            value={form.data.address}
                            onChange={(event) =>
                                form.setData(
                                    "address",
                                    event.target.value,
                                )
                            }
                            placeholder="Address"
                            className="mt-4 block w-full rounded-md border-slate-300"
                        />

                        <textarea
                            rows="4"
                            value={form.data.office_hours}
                            onChange={(event) =>
                                form.setData(
                                    "office_hours",
                                    event.target.value,
                                )
                            }
                            placeholder="Office hours"
                            className="mt-4 block w-full rounded-md border-slate-300"
                        />
                    </div>

                    <div className="border-t border-slate-200 pt-6">
                        <h3 className="font-semibold text-slate-900">
                            Social links
                        </h3>

                        <div className="mt-4 grid gap-4 md:grid-cols-2">
                            {[
                                "facebook",
                                "instagram",
                                "linkedin",
                                "youtube",
                            ].map((platform) => (
                                <input
                                    key={platform}
                                    value={
                                        form.data.social_links[
                                            platform
                                        ]
                                    }
                                    onChange={(event) =>
                                        form.setData(
                                            "social_links",
                                            {
                                                ...form.data.social_links,
                                                [platform]:
                                                    event.target.value,
                                            },
                                        )
                                    }
                                    placeholder={`${platform} URL`}
                                    className="rounded-md border-slate-300"
                                />
                            ))}
                        </div>
                    </div>

                    <div className="border-t border-slate-200 pt-6">
                        <h3 className="font-semibold text-slate-900">
                            Default SEO
                        </h3>

                        <input
                            value={form.data.default_meta_title}
                            onChange={(event) =>
                                form.setData(
                                    "default_meta_title",
                                    event.target.value,
                                )
                            }
                            placeholder="Default meta title"
                            className="mt-4 block w-full rounded-md border-slate-300"
                        />

                        <textarea
                            rows="3"
                            value={form.data.default_meta_description}
                            onChange={(event) =>
                                form.setData(
                                    "default_meta_description",
                                    event.target.value,
                                )
                            }
                            placeholder="Default meta description"
                            className="mt-4 block w-full rounded-md border-slate-300"
                        />

                        <input
                            value={form.data.default_og_image_path}
                            onChange={(event) =>
                                form.setData(
                                    "default_og_image_path",
                                    event.target.value,
                                )
                            }
                            placeholder="Default OG image"
                            className="mt-4 block w-full rounded-md border-slate-300"
                        />
                    </div>

                    <div className="border-t border-slate-200 pt-6">
                        <textarea
                            rows="4"
                            value={form.data.footer_text}
                            onChange={(event) =>
                                form.setData(
                                    "footer_text",
                                    event.target.value,
                                )
                            }
                            placeholder="Footer text"
                            className="block w-full rounded-md border-slate-300"
                        />

                        <textarea
                            rows="4"
                            value={form.data.emergency_notice}
                            onChange={(event) =>
                                form.setData(
                                    "emergency_notice",
                                    event.target.value,
                                )
                            }
                            placeholder="Emergency notice"
                            className="mt-4 block w-full rounded-md border-slate-300"
                        />

                        <div className="mt-4 grid gap-4 md:grid-cols-2">
                            <input
                                value={form.data.booking_cta_label}
                                onChange={(event) =>
                                    form.setData(
                                        "booking_cta_label",
                                        event.target.value,
                                    )
                                }
                                placeholder="CTA label"
                                className="rounded-md border-slate-300"
                            />

                            <input
                                value={form.data.booking_cta_url}
                                onChange={(event) =>
                                    form.setData(
                                        "booking_cta_url",
                                        event.target.value,
                                    )
                                }
                                placeholder="CTA URL"
                                className="rounded-md border-slate-300"
                            />
                        </div>
                    </div>

                    <InputError
                        message={form.errors.site_name}
                    />

                    <button
                        disabled={form.processing}
                        className="rounded-md bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white"
                    >
                        Save website settings
                    </button>
                </form>
            </div>
        </AdminLayout>
    );
}
