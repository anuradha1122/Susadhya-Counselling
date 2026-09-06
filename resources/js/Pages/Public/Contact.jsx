import {
    MarketingHero,
} from "@/Components/Public/FixedSections";
import PublicSeo from "@/Components/Public/PublicSeo";
import PublicLayout from "@/Layouts/PublicLayout";
import SupportContactForm from "@/Components/Public/SupportContactForm";
import {
    Clock,
    Mail,
    MapPin,
    MessageCircle,
    Phone,
} from "lucide-react";

function whatsappUrl(number) {
    if (!number) {
        return null;
    }

    const normalized =
        String(number).replace(
            /[^\d]/g,
            "",
        );

    if (!normalized) {
        return null;
    }

    return `https://wa.me/${normalized}`;
}

export default function Contact({
    page,
    contact,
    websiteContent,
}) {
    const hero =
        websiteContent
            ?.sections
            ?.hero;

    const whatsapp =
        whatsappUrl(
            contact?.whatsapp_number,
        );

    const seo =
        websiteContent
            ?.page
            ?.seo ??
        page?.seo;

    return (
        <PublicLayout>
            <PublicSeo seo={seo} />

            {/* Fixed CMS-controlled hero */}
            {hero && (
                <MarketingHero
                    section={hero}
                />
            )}

            {/* Contact introduction */}
            <section className="bg-[#F7F9F4] px-4 py-16 sm:px-6 lg:px-8 lg:py-20">
                <div className="mx-auto grid max-w-7xl gap-12 lg:grid-cols-[0.8fr_1.2fr] lg:gap-20">
                    <div>
                        <p className="text-xs font-semibold uppercase tracking-[0.2em] text-[#2A9D8F]">
                            Contact Susadhya
                        </p>

                        <h2 className="susadhya-heading mt-4 text-3xl font-bold leading-tight text-[#1A365D] sm:text-4xl">
                            General enquiries
                            and platform support
                        </h2>

                        <p className="mt-5 text-sm leading-7 text-slate-600">
                            Contact us for general
                            questions about
                            Susadhya, available
                            counselling services
                            or using the platform.
                        </p>

                        <p className="mt-4 text-sm leading-7 text-slate-500">
                            Please avoid sending
                            sensitive counselling
                            details, clinical
                            information or private
                            documents through
                            ordinary email,
                            telephone or WhatsApp.
                            Secure client and
                            counselling workflows
                            should be used for
                            sensitive information.
                        </p>

                        {page?.body && (
                            <p className="mt-6 whitespace-pre-line text-sm leading-7 text-slate-600">
                                {page.body}
                            </p>
                        )}
                    </div>

                    {/* Contact details */}
                    <div className="border-t border-slate-200">
                        {contact?.contact_email && (
                            <a
                                href={`mailto:${contact.contact_email}`}
                                className="group grid gap-4 border-b border-slate-200 py-7 sm:grid-cols-[48px_150px_1fr] sm:items-center"
                            >
                                <div className="flex h-11 w-11 items-center justify-center rounded-full bg-[#E6F2FA] text-[#2A9D8F]">
                                    <Mail className="h-5 w-5" />
                                </div>

                                <div>
                                    <p className="text-xs font-semibold uppercase tracking-[0.14em] text-slate-400">
                                        Email
                                    </p>
                                </div>

                                <p className="text-sm font-medium text-[#1A365D] transition group-hover:text-[#2A9D8F]">
                                    {
                                        contact.contact_email
                                    }
                                </p>
                            </a>
                        )}

                        {contact?.contact_phone && (
                            <a
                                href={`tel:${contact.contact_phone}`}
                                className="group grid gap-4 border-b border-slate-200 py-7 sm:grid-cols-[48px_150px_1fr] sm:items-center"
                            >
                                <div className="flex h-11 w-11 items-center justify-center rounded-full bg-[#E6F2FA] text-[#2A9D8F]">
                                    <Phone className="h-5 w-5" />
                                </div>

                                <div>
                                    <p className="text-xs font-semibold uppercase tracking-[0.14em] text-slate-400">
                                        Phone
                                    </p>
                                </div>

                                <p className="text-sm font-medium text-[#1A365D] transition group-hover:text-[#2A9D8F]">
                                    {
                                        contact.contact_phone
                                    }
                                </p>
                            </a>
                        )}

                        {contact?.whatsapp_number &&
                            (whatsapp ? (
                                <a
                                    href={
                                        whatsapp
                                    }
                                    target="_blank"
                                    rel="noreferrer"
                                    className="group grid gap-4 border-b border-slate-200 py-7 sm:grid-cols-[48px_150px_1fr] sm:items-center"
                                >
                                    <div className="flex h-11 w-11 items-center justify-center rounded-full bg-[#E6F2FA] text-[#2A9D8F]">
                                        <MessageCircle className="h-5 w-5" />
                                    </div>

                                    <div>
                                        <p className="text-xs font-semibold uppercase tracking-[0.14em] text-slate-400">
                                            WhatsApp
                                        </p>
                                    </div>

                                    <p className="text-sm font-medium text-[#1A365D] transition group-hover:text-[#2A9D8F]">
                                        {
                                            contact.whatsapp_number
                                        }
                                    </p>
                                </a>
                            ) : (
                                <div className="grid gap-4 border-b border-slate-200 py-7 sm:grid-cols-[48px_150px_1fr] sm:items-center">
                                    <div className="flex h-11 w-11 items-center justify-center rounded-full bg-[#E6F2FA] text-[#2A9D8F]">
                                        <MessageCircle className="h-5 w-5" />
                                    </div>

                                    <p className="text-xs font-semibold uppercase tracking-[0.14em] text-slate-400">
                                        WhatsApp
                                    </p>

                                    <p className="text-sm font-medium text-[#1A365D]">
                                        {
                                            contact.whatsapp_number
                                        }
                                    </p>
                                </div>
                            ))}

                        {contact?.address && (
                            <div className="grid gap-4 border-b border-slate-200 py-7 sm:grid-cols-[48px_150px_1fr]">
                                <div className="flex h-11 w-11 items-center justify-center rounded-full bg-[#E6F2FA] text-[#2A9D8F]">
                                    <MapPin className="h-5 w-5" />
                                </div>

                                <div className="pt-1">
                                    <p className="text-xs font-semibold uppercase tracking-[0.14em] text-slate-400">
                                        Address
                                    </p>
                                </div>

                                <p className="whitespace-pre-line text-sm leading-7 text-[#1A365D]">
                                    {
                                        contact.address
                                    }
                                </p>
                            </div>
                        )}

                        {contact?.office_hours && (
                            <div className="grid gap-4 border-b border-slate-200 py-7 sm:grid-cols-[48px_150px_1fr]">
                                <div className="flex h-11 w-11 items-center justify-center rounded-full bg-[#E6F2FA] text-[#2A9D8F]">
                                    <Clock className="h-5 w-5" />
                                </div>

                                <div className="pt-1">
                                    <p className="text-xs font-semibold uppercase tracking-[0.14em] text-slate-400">
                                        Office Hours
                                    </p>
                                </div>

                                <p className="whitespace-pre-line text-sm leading-7 text-[#1A365D]">
                                    {
                                        contact.office_hours
                                    }
                                </p>
                            </div>
                        )}
                    </div>
                </div>
            </section>

            <section className="bg-[#F7F9F4] px-4 py-12 sm:px-6 lg:px-8 lg:py-16">
                <div className="mx-auto max-w-5xl">
                    <SupportContactForm />
                </div>
            </section>

            {/* Emergency information */}
            {contact?.emergency_notice && (
                <section className="bg-white px-4 pb-20 sm:px-6 lg:px-8">
                    <div className="mx-auto max-w-7xl">
                        <div className="border-l-4 border-amber-400 bg-amber-50 px-6 py-6">
                            <p className="text-xs font-semibold uppercase tracking-[0.16em] text-amber-700">
                                Important
                            </p>

                            <h2 className="susadhya-heading mt-2 text-xl font-bold text-amber-950">
                                Emergency Support
                            </h2>

                            <p className="mt-3 whitespace-pre-line text-sm leading-7 text-amber-900">
                                {
                                    contact.emergency_notice
                                }
                            </p>
                        </div>
                    </div>
                </section>
            )}
        </PublicLayout>
    );
}
