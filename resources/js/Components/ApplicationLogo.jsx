export default function ApplicationLogo({
    className = '',
    compact = false,
}) {
    return (
        <div
            className={`flex items-center gap-3 ${className}`}
        >
            <div className="grid h-10 w-10 shrink-0 place-items-center rounded-2xl bg-teal-600 text-lg font-bold text-white shadow-sm">
                S
            </div>

            {!compact && (
                <div className="leading-tight">
                    <p className="font-semibold text-slate-900">
                        Susadhya
                    </p>

                    <p className="text-xs text-slate-500">
                        Counselling
                    </p>
                </div>
            )}
        </div>
    );
}
