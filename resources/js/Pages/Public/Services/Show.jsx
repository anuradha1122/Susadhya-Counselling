import PublicSeo from "@/Components/Public/PublicSeo";
import PublicLayout from "@/Layouts/PublicLayout";
import { Link, usePage } from "@inertiajs/react";
import {
    Clock,
    HeartHandshake,
    Laptop,
    Users,
} from "lucide-react";

function humanize(value) {
    return String(value ?? "")
        .replaceAll("_", " ")
        .replace(/\b\w/g, (char) =>
            char.toUpperCase(),
        );
}

export default function Show({
    service,
    seo,
}) {
    const { auth } =
        usePage().props;

    return (
        <PublicLayout>
            <PublicSeo seo={seo} />

            <section className="bg-[#E6F2FA] px-4 py-16 sm:px-6 lg:px-8">
                <div className="mx-auto max-w-5xl">
                    <Link
                        href={route(
                            "public.services.index",
                        )}
                        className="text-sm font-semibold text-[#2A9D8F]"
                    >
                        ← All services
                    </Link>

                    <h1 className="susadhya-heading mt-6 text-4xl font-bold sm:text-5xl">
                        {service.name}
                    </h1>

                    {service.short_description && (
                        <p className="mt-5 max-w-3xl text-lg leading-8 text-slate-600">
                            {
                                service.short_description
                            }
                        </p>
                    )}
                </div>
            </section>

            <section className="px-4 py-14 sm:px-6 lg:px-8">
                <div className="mx-auto grid max-w-6xl gap-8 lg:grid-cols-[1fr_340px]">
                    <article className="rounded-2xl bg-white p-6 shadow-sm sm:p-8">
                        <h2 className="susadhya-heading text-2xl font-bold">
                            About this service
                        </h2>

                        <div className="mt-5 whitespace-pre-line text-base leading-8 text-slate-700">
                            {service.description ??
                                service.short_description ??
                                "More information will be available soon."}
                        </div>
                    </article>

                    <aside className="space-y-4">
                        <div className="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                            <h3 className="font-semibold text-[#1A365D]">
                                Service details
                            </h3>

                            <dl className="mt-5 space-y-4 text-sm">
                                {service.service_mode && (
                                    <div className="flex gap-3">
                                        <Laptop className="h-5 w-5 shrink-0 text-[#2A9D8F]" />

                                        <div>
                                            <dt className="text-slate-500">
                                                Mode
                                            </dt>

                                            <dd className="font-medium text-slate-900">
                                                {humanize(
                                                    service.service_mode,
                                                )}
                                            </dd>
                                        </div>
                                    </div>
                                )}

                                {service.duration_minutes && (
                                    <div className="flex gap-3">
                                        <Clock className="h-5 w-5 shrink-0 text-[#2A9D8F]" />

                                        <div>
                                            <dt className="text-slate-500">
                                                Duration
                                            </dt>

                                            <dd className="font-medium text-slate-900">
                                                {
                                                    service.duration_minutes
                                                }{" "}
                                                minutes
                                            </dd>
                                        </div>
                                    </div>
                                )}

                                {service.target_age_group && (
                                    <div className="flex gap-3">
                                        <Users className="h-5 w-5 shrink-0 text-[#2A9D8F]" />

                                        <div>
                                            <dt className="text-slate-500">
                                                Suitable for
                                            </dt>

                                            <dd className="font-medium text-slate-900">
                                                {humanize(
                                                    service.target_age_group,
                                                )}
                                            </dd>
                                        </div>
                                    </div>
                                )}

                                {service.price !==
                                    null &&
                                    service.price !==
                                        undefined && (
                                        <div className="flex gap-3">
                                            <HeartHandshake className="h-5 w-5 shrink-0 text-[#2A9D8F]" />

                                            <div>
                                                <dt className="text-slate-500">
                                                    Fee
                                                </dt>

                                                <dd className="font-medium text-slate-900">
                                                    {
                                                        service.currency
                                                    }{" "}
                                                    {
                                                        service.price
                                                    }
                                                </dd>
                                            </div>
                                        </div>
                                    )}
                            </dl>
                        </div>

                        <Link
                            href={
                                auth?.user
                                    ? route(
                                          "public.counsellors.index",
                                      )
                                    : route(
                                          "public.counsellors.index",
                                      )
                            }
                            className="block rounded-xl bg-[#2A9D8F] px-5 py-3 text-center text-sm font-semibold text-white hover:bg-[#23897D]"
                        >
                            Find a Counsellor
                        </Link>
                    </aside>
                </div>
            </section>
        </PublicLayout>
    );
}
