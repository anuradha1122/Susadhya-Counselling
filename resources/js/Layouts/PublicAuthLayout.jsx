import PublicHeader from "@/Components/Public/PublicHeader";
import {
    HeartHandshake,
    LockKeyhole,
    ShieldCheck,
} from "lucide-react";

export default function PublicAuthLayout({
    title,
    description,
    image = "/images/site-defaults/auth-login.jpg",
    imageAlt = "Professional counselling support",
    children,
}) {
    return (
        <div className="min-h-screen bg-[#F4FAFB]">
            <PublicHeader />

            <main className="relative overflow-hidden">
                <div className="absolute -left-32 top-40 h-80 w-80 rounded-full bg-[#2A9D8F]/10 blur-3xl" />

                <div className="absolute -bottom-32 -right-24 h-96 w-96 rounded-full bg-[#7FB069]/10 blur-3xl" />

                <div className="relative mx-auto grid min-h-[calc(100vh-76px)] max-w-[1600px] lg:grid-cols-[1.05fr_.95fr]">
                    {/* Left visual */}
                    <section className="relative hidden min-h-[760px] overflow-hidden lg:flex">
                        <img
                            src={image}
                            alt={imageAlt}
                            className="absolute inset-0 h-full w-full object-cover"
                        />

                        {/* Gradient overlay */}
                        <div className="absolute inset-0 bg-gradient-to-t from-[#102F47]/95 via-[#123B5B]/55 to-[#123B5B]/10" />

                        <div className="absolute inset-0 bg-gradient-to-r from-[#123B5B]/30 via-transparent to-transparent" />

                        {/* Decorative glow */}
                        <div className="absolute right-[-80px] top-[20%] h-72 w-72 rounded-full bg-[#2A9D8F]/20 blur-3xl" />

                        {/* Left content */}
                        <div className="relative z-10 flex w-full flex-col justify-end p-10 xl:p-14">
                            <div className="max-w-xl">
                                <p className="text-xs font-bold uppercase tracking-[0.22em] text-cyan-200">
                                    Susadhya Counselling
                                </p>

                                <h1 className="susadhya-heading mt-4 text-4xl font-bold leading-[1.08] text-white xl:text-5xl">
                                    Professional support,
                                    made easier to reach.
                                </h1>

                                <p className="mt-5 max-w-lg text-sm leading-7 text-slate-200">
                                    Connect with professional counselling support
                                    through a secure, private and thoughtfully
                                    designed platform.
                                </p>

                                <div className="mt-8 grid max-w-lg grid-cols-3 gap-4 border-t border-white/20 pt-7">
                                    <div>
                                        <div className="flex h-10 w-10 items-center justify-center rounded-full bg-white/10 backdrop-blur">
                                            <ShieldCheck className="h-5 w-5 text-cyan-200" />
                                        </div>

                                        <p className="mt-3 text-xs font-semibold text-white">
                                            Professional
                                        </p>

                                        <p className="mt-1 text-[10px] leading-4 text-slate-300">
                                            Trusted support
                                        </p>
                                    </div>

                                    <div>
                                        <div className="flex h-10 w-10 items-center justify-center rounded-full bg-white/10 backdrop-blur">
                                            <LockKeyhole className="h-5 w-5 text-cyan-200" />
                                        </div>

                                        <p className="mt-3 text-xs font-semibold text-white">
                                            Confidential
                                        </p>

                                        <p className="mt-1 text-[10px] leading-4 text-slate-300">
                                            Privacy focused
                                        </p>
                                    </div>

                                    <div>
                                        <div className="flex h-10 w-10 items-center justify-center rounded-full bg-white/10 backdrop-blur">
                                            <HeartHandshake className="h-5 w-5 text-cyan-200" />
                                        </div>

                                        <p className="mt-3 text-xs font-semibold text-white">
                                            Supportive
                                        </p>

                                        <p className="mt-1 text-[10px] leading-4 text-slate-300">
                                            Human centred
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    {/* Right form */}
                    <section className="relative flex items-center justify-center px-5 py-12 sm:px-8 lg:px-12 xl:px-16">
                        <div className="w-full max-w-[540px]">
                            <div className="mb-8">
                                <p className="text-xs font-bold uppercase tracking-[0.2em] text-[#2A9D8F]">
                                    Susadhya
                                </p>

                                <h2 className="susadhya-heading mt-3 text-4xl font-bold leading-tight text-[#123B5B]">
                                    {title}
                                </h2>

                                {description && (
                                    <p className="mt-3 max-w-lg text-sm leading-7 text-slate-600">
                                        {description}
                                    </p>
                                )}
                            </div>

                            <div className="rounded-[26px] border border-slate-200/80 bg-white p-6 shadow-[0_22px_70px_rgba(26,54,93,0.10)] sm:p-8">
                                {children}
                            </div>

                            <div className="mt-6 flex items-center justify-center gap-2 text-center text-xs text-slate-400">
                                <ShieldCheck className="h-4 w-4 text-[#7FB069]" />

                                <span>
                                    Secure and privacy-conscious access
                                </span>
                            </div>
                        </div>
                    </section>
                </div>
            </main>
        </div>
    );
}
