export default function StatCard({
    title,
    value,
    hint,
    icon: Icon,
    tone = 'teal',
}) {
    const tones = {
        teal: 'bg-teal-50 text-teal-700',
        blue: 'bg-blue-50 text-blue-700',
        amber: 'bg-amber-50 text-amber-700',
        violet: 'bg-violet-50 text-violet-700',
    };

    return (
        <article className="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div className="flex items-start justify-between gap-4">
                <div>
                    <p className="text-sm font-medium text-slate-500">
                        {title}
                    </p>

                    <p className="mt-2 text-3xl font-bold tracking-tight text-slate-900">
                        {value}
                    </p>

                    {hint && (
                        <p className="mt-2 text-xs text-slate-500">
                            {hint}
                        </p>
                    )}
                </div>

                <div
                    className={`rounded-xl p-3 ${
                        tones[tone] ?? tones.teal
                    }`}
                >
                    <Icon
                        className="h-5 w-5"
                        aria-hidden="true"
                    />
                </div>
            </div>
        </article>
    );
}
