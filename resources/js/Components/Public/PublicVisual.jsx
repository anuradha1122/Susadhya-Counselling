import {
    CalendarDays,
    CheckCircle2,
    HeartHandshake,
    LockKeyhole,
    MessageCircle,
    ShieldCheck,
    Sparkles,
    Video,
} from "lucide-react";

function IconBubble({
    children,
    className = "",
}) {
    return (
        <div
            className={`flex items-center justify-center rounded-2xl border border-white/70 bg-white/90 shadow-sm backdrop-blur ${className}`}
        >
            {children}
        </div>
    );
}

function SessionVisual() {
    return (
        <div className="relative mx-auto aspect-[5/4] w-full max-w-xl overflow-hidden rounded-[2rem] bg-gradient-to-br from-[#E6F2FA] via-white to-[#DFF1EC] p-7 shadow-sm ring-1 ring-[#2A9D8F]/10">
            <div className="absolute -right-16 -top-16 h-48 w-48 rounded-full bg-[#2A9D8F]/15" />
            <div className="absolute -bottom-20 -left-20 h-56 w-56 rounded-full bg-[#7FB069]/15" />

            <div className="relative flex h-full items-center justify-center">
                <div className="w-[75%] rounded-[1.75rem] border border-white bg-white/90 p-6 shadow-lg">
                    <div className="flex items-center gap-3">
                        <div className="flex h-12 w-12 items-center justify-center rounded-full bg-[#E6F2FA]">
                            <HeartHandshake className="h-6 w-6 text-[#2A9D8F]" />
                        </div>

                        <div>
                            <p className="font-semibold text-[#1A365D]">
                                Private online session
                            </p>

                            <p className="mt-1 text-xs text-slate-500">
                                Professional counselling support
                            </p>
                        </div>
                    </div>

                    <div className="mt-6 rounded-2xl bg-[#F7F9F4] p-5">
                        <div className="flex items-center justify-center py-8">
                            <Video className="h-16 w-16 text-[#2A9D8F]" />
                        </div>
                    </div>

                    <div className="mt-5 flex items-center justify-between">
                        <span className="flex items-center gap-2 text-xs font-medium text-slate-500">
                            <ShieldCheck className="h-4 w-4 text-[#7FB069]" />
                            Confidential
                        </span>

                        <span className="rounded-full bg-[#E6F2FA] px-3 py-1 text-xs font-medium text-[#1A365D]">
                            Online
                        </span>
                    </div>
                </div>

                <IconBubble className="absolute right-3 top-8 h-16 w-16">
                    <MessageCircle className="h-7 w-7 text-[#2A9D8F]" />
                </IconBubble>

                <IconBubble className="absolute bottom-6 left-2 h-16 w-16">
                    <Sparkles className="h-7 w-7 text-[#7FB069]" />
                </IconBubble>
            </div>
        </div>
    );
}

function PrivacyVisual() {
    return (
        <div className="relative mx-auto aspect-[5/4] w-full max-w-xl overflow-hidden rounded-[2rem] bg-[#1A365D] p-8 shadow-sm">
            <div className="absolute -right-20 -top-20 h-64 w-64 rounded-full bg-[#2A9D8F]/20" />
            <div className="absolute -bottom-24 -left-24 h-72 w-72 rounded-full bg-[#7FB069]/10" />

            <div className="relative flex h-full flex-col items-center justify-center">
                <div className="flex h-28 w-28 items-center justify-center rounded-[2rem] bg-white shadow-lg">
                    <ShieldCheck className="h-14 w-14 text-[#2A9D8F]" />
                </div>

                <h3 className="susadhya-heading mt-7 text-2xl font-bold text-white">
                    Privacy matters
                </h3>

                <p className="mt-3 max-w-xs text-center text-sm leading-6 text-slate-300">
                    Confidentiality and responsible access are built into
                    the Susadhya platform.
                </p>

                <div className="mt-7 grid w-full max-w-sm grid-cols-2 gap-3">
                    <div className="rounded-xl bg-white/10 p-4 text-center">
                        <LockKeyhole className="mx-auto h-5 w-5 text-[#7FB069]" />

                        <p className="mt-2 text-xs font-medium text-white">
                            Protected access
                        </p>
                    </div>

                    <div className="rounded-xl bg-white/10 p-4 text-center">
                        <CheckCircle2 className="mx-auto h-5 w-5 text-[#7FB069]" />

                        <p className="mt-2 text-xs font-medium text-white">
                            Controlled records
                        </p>
                    </div>
                </div>
            </div>
        </div>
    );
}

function JourneyVisual() {
    return (
        <div className="relative mx-auto aspect-[5/4] w-full max-w-xl overflow-hidden rounded-[2rem] bg-gradient-to-br from-[#F7F9F4] to-[#E6F2FA] p-8 shadow-sm ring-1 ring-slate-200">
            <div className="flex h-full flex-col justify-center gap-4">
                {[
                    {
                        icon: HeartHandshake,
                        number: "01",
                        title: "Explore support",
                    },
                    {
                        icon: CalendarDays,
                        number: "02",
                        title: "Choose a convenient time",
                    },
                    {
                        icon: Video,
                        number: "03",
                        title: "Meet your counsellor",
                    },
                ].map((item) => {
                    const Icon = item.icon;

                    return (
                        <div
                            key={item.number}
                            className="flex items-center gap-4 rounded-2xl border border-white bg-white/90 p-4 shadow-sm"
                        >
                            <div className="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-[#E6F2FA]">
                                <Icon className="h-6 w-6 text-[#2A9D8F]" />
                            </div>

                            <div className="flex-1">
                                <span className="text-xs font-semibold text-[#7FB069]">
                                    STEP {item.number}
                                </span>

                                <p className="mt-1 font-semibold text-[#1A365D]">
                                    {item.title}
                                </p>
                            </div>

                            <CheckCircle2 className="h-5 w-5 text-[#7FB069]" />
                        </div>
                    );
                })}
            </div>
        </div>
    );
}

export default function PublicVisual({
    variant = "session",
    imagePath = null,
    imageAlt = "Susadhya Counselling",
}) {
    if (imagePath) {
        return (
            <div className="mx-auto w-full max-w-xl overflow-hidden rounded-[2rem] bg-white p-3 shadow-sm ring-1 ring-slate-200">
                <img
                    src={imagePath}
                    alt={imageAlt}
                    className="aspect-[5/4] w-full rounded-[1.5rem] object-cover"
                />
            </div>
        );
    }

    switch (variant) {
        case "privacy":
            return <PrivacyVisual />;

        case "journey":
            return <JourneyVisual />;

        case "session":
        default:
            return <SessionVisual />;
    }
}
