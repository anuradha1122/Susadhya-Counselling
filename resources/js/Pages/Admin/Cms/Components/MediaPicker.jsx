import {
    Check,
    Images,
    X,
} from "lucide-react";
import {
    useMemo,
    useState,
} from "react";

export default function MediaPicker({
    label,
    value,
    defaultValue = "",
    media = [],
    onChange,
}) {
    const [open, setOpen] =
        useState(false);

    const [search, setSearch] =
        useState("");

    const filtered =
        useMemo(() => {
            const term =
                search
                    .trim()
                    .toLowerCase();

            if (!term) {
                return media;
            }

            return media.filter(
                (item) =>
                    item.original_name
                        ?.toLowerCase()
                        .includes(term) ||
                    item.alt_text
                        ?.toLowerCase()
                        .includes(term),
            );
        }, [media, search]);

    const current =
        value || defaultValue;

    return (
        <>
            <div>
                <label className="text-sm font-medium text-slate-700">
                    {label}
                </label>

                <div className="mt-2 overflow-hidden rounded-xl border border-slate-200 bg-white">
                    {current ? (
                        <img
                            src={current}
                            alt=""
                            className="aspect-[16/7] w-full object-cover"
                        />
                    ) : (
                        <div className="flex aspect-[16/7] items-center justify-center bg-slate-100">
                            <Images className="h-8 w-8 text-slate-400" />
                        </div>
                    )}

                    <div className="flex flex-wrap items-center justify-between gap-3 p-3">
                        <p className="text-xs text-slate-500">
                            {value ===
                            defaultValue
                                ? "Using default website image"
                                : "Current website image"}
                        </p>

                        <div className="flex gap-2">
                            <button
                                type="button"
                                onClick={() =>
                                    setOpen(
                                        true,
                                    )
                                }
                                className="rounded-lg border border-indigo-200 px-3 py-2 text-xs font-medium text-indigo-700"
                            >
                                Choose Image
                            </button>

                            {defaultValue && (
                                <button
                                    type="button"
                                    onClick={() =>
                                        onChange(
                                            defaultValue,
                                        )
                                    }
                                    className="rounded-lg border border-slate-300 px-3 py-2 text-xs font-medium text-slate-600"
                                >
                                    Restore Default
                                </button>
                            )}
                        </div>
                    </div>
                </div>
            </div>

            {open && (
                <div className="fixed inset-0 z-[100] flex items-center justify-center bg-slate-950/60 p-4">
                    <div className="flex max-h-[90vh] w-full max-w-6xl flex-col overflow-hidden rounded-2xl bg-white shadow-2xl">
                        <div className="flex items-center justify-between border-b border-slate-200 px-5 py-4">
                            <div>
                                <h3 className="font-semibold text-slate-900">
                                    Choose Image
                                </h3>

                                <p className="mt-1 text-xs text-slate-500">
                                    Select an image from the CMS Media Library.
                                </p>
                            </div>

                            <button
                                type="button"
                                onClick={() =>
                                    setOpen(
                                        false,
                                    )
                                }
                                className="rounded-lg p-2 hover:bg-slate-100"
                            >
                                <X className="h-5 w-5" />
                            </button>
                        </div>

                        <div className="border-b border-slate-200 p-4">
                            <input
                                value={
                                    search
                                }
                                onChange={(
                                    event,
                                ) =>
                                    setSearch(
                                        event
                                            .target
                                            .value,
                                    )
                                }
                                placeholder="Search media..."
                                className="block w-full rounded-lg border-slate-300"
                            />
                        </div>

                        <div className="overflow-y-auto p-4">
                            {filtered.length >
                            0 ? (
                                <div className="grid gap-4 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4">
                                    {filtered.map(
                                        (
                                            item,
                                        ) => {
                                            const selected =
                                                value ===
                                                item.url;

                                            return (
                                                <button
                                                    key={
                                                        item.uuid
                                                    }
                                                    type="button"
                                                    onClick={() => {
                                                        onChange(
                                                            item.url,
                                                        );

                                                        setOpen(
                                                            false,
                                                        );
                                                    }}
                                                    className={`overflow-hidden rounded-xl border text-left ${
                                                        selected
                                                            ? "border-indigo-500 ring-2 ring-indigo-100"
                                                            : "border-slate-200 hover:border-indigo-300"
                                                    }`}
                                                >
                                                    <div className="relative aspect-[4/3] bg-slate-100">
                                                        <img
                                                            src={
                                                                item.url
                                                            }
                                                            alt={
                                                                item.alt_text ??
                                                                ""
                                                            }
                                                            className="h-full w-full object-cover"
                                                        />

                                                        {selected && (
                                                            <div className="absolute right-2 top-2 rounded-full bg-indigo-600 p-1.5 text-white">
                                                                <Check className="h-4 w-4" />
                                                            </div>
                                                        )}
                                                    </div>

                                                    <div className="p-3">
                                                        <p className="truncate text-xs font-medium text-slate-700">
                                                            {
                                                                item.original_name
                                                            }
                                                        </p>
                                                    </div>
                                                </button>
                                            );
                                        },
                                    )}
                                </div>
                            ) : (
                                <div className="py-16 text-center text-sm text-slate-500">
                                    No uploaded images found.
                                </div>
                            )}
                        </div>
                    </div>
                </div>
            )}
        </>
    );
}
