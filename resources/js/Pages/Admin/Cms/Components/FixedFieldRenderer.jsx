import MediaPicker from "@/Pages/Admin/Cms/Components/MediaPicker";
import {
    Plus,
    Trash2,
} from "lucide-react";

export default function FixedFieldRenderer({
    field,
    value,
    media,
    onChange,
}) {
    if (field.type === "text") {
        return (
            <div>
                <label className="text-sm font-medium text-slate-700">
                    {field.label}
                </label>

                <input
                    value={value ?? ""}
                    onChange={(event) =>
                        onChange(
                            event.target.value,
                        )
                    }
                    className="mt-1 block w-full rounded-lg border-slate-300"
                />
            </div>
        );
    }

    if (
        field.type === "textarea"
    ) {
        return (
            <div>
                <label className="text-sm font-medium text-slate-700">
                    {field.label}
                </label>

                <textarea
                    rows="6"
                    value={value ?? ""}
                    onChange={(event) =>
                        onChange(
                            event.target.value,
                        )
                    }
                    className="mt-1 block w-full rounded-lg border-slate-300"
                />
            </div>
        );
    }

    if (field.type === "number") {
        return (
            <div className="max-w-xs">
                <label className="text-sm font-medium text-slate-700">
                    {field.label}
                </label>

                <input
                    type="number"
                    min={field.min ?? 1}
                    max={field.max ?? 24}
                    value={value ?? ""}
                    onChange={(event) =>
                        onChange(
                            Number(
                                event
                                    .target
                                    .value,
                            ),
                        )
                    }
                    className="mt-1 block w-full rounded-lg border-slate-300"
                />
            </div>
        );
    }

    if (field.type === "image") {
        return (
            <MediaPicker
                label={field.label}
                value={value ?? ""}
                defaultValue={
                    field.default ?? ""
                }
                media={media}
                onChange={onChange}
            />
        );
    }

    if (field.type === "list") {
        const items =
            Array.isArray(value)
                ? value
                : [];

        const updateItem = (
            index,
            nextValue,
        ) => {
            const next =
                [...items];

            next[index] =
                nextValue;

            onChange(next);
        };

        return (
            <div>
                <label className="text-sm font-medium text-slate-700">
                    {field.label}
                </label>

                <div className="mt-2 space-y-2">
                    {items.map(
                        (item, index) => (
                            <div
                                key={
                                    index
                                }
                                className="flex gap-2"
                            >
                                <input
                                    value={
                                        item
                                    }
                                    onChange={(
                                        event,
                                    ) =>
                                        updateItem(
                                            index,
                                            event
                                                .target
                                                .value,
                                        )
                                    }
                                    className="flex-1 rounded-lg border-slate-300"
                                />

                                <button
                                    type="button"
                                    onClick={() =>
                                        onChange(
                                            items.filter(
                                                (
                                                    _,
                                                    itemIndex,
                                                ) =>
                                                    itemIndex !==
                                                    index,
                                            ),
                                        )
                                    }
                                    className="rounded-lg border border-rose-200 px-3 text-rose-600"
                                >
                                    <Trash2 className="h-4 w-4" />
                                </button>
                            </div>
                        ),
                    )}
                </div>

                <button
                    type="button"
                    onClick={() =>
                        onChange([
                            ...items,
                            "",
                        ])
                    }
                    className="mt-3 inline-flex items-center gap-2 text-sm font-medium text-indigo-600"
                >
                    <Plus className="h-4 w-4" />
                    Add item
                </button>
            </div>
        );
    }

    if (
        field.type === "repeater"
    ) {
        const items =
            Array.isArray(value)
                ? value
                : [];

        const update =
            (
                index,
                key,
                nextValue,
            ) => {
                onChange(
                    items.map(
                        (
                            item,
                            itemIndex,
                        ) =>
                            itemIndex ===
                            index
                                ? {
                                      ...item,
                                      [key]:
                                          nextValue,
                                  }
                                : item,
                    ),
                );
            };

        return (
            <div>
                <label className="text-sm font-medium text-slate-700">
                    {field.label}
                </label>

                <div className="mt-3 space-y-4">
                    {items.map(
                        (item, index) => (
                            <div
                                key={
                                    index
                                }
                                className="rounded-xl border border-slate-200 p-4"
                            >
                                <div className="mb-4 flex items-center justify-between">
                                    <span className="text-xs font-semibold uppercase tracking-wide text-slate-400">
                                        Item{" "}
                                        {index +
                                            1}
                                    </span>

                                    <button
                                        type="button"
                                        onClick={() =>
                                            onChange(
                                                items.filter(
                                                    (
                                                        _,
                                                        itemIndex,
                                                    ) =>
                                                        itemIndex !==
                                                        index,
                                                ),
                                            )
                                        }
                                        className="text-rose-600"
                                    >
                                        <Trash2 className="h-4 w-4" />
                                    </button>
                                </div>

                                <div className="grid gap-4">
                                    {field.fields.map(
                                        (
                                            nested,
                                        ) => (
                                            <div
                                                key={
                                                    nested.key
                                                }
                                            >
                                                <label className="text-sm font-medium text-slate-700">
                                                    {
                                                        nested.label
                                                    }
                                                </label>

                                                {nested.type ===
                                                "textarea" ? (
                                                    <textarea
                                                        rows="4"
                                                        value={
                                                            item[
                                                                nested
                                                                    .key
                                                            ] ??
                                                            ""
                                                        }
                                                        onChange={(
                                                            event,
                                                        ) =>
                                                            update(
                                                                index,
                                                                nested.key,
                                                                event
                                                                    .target
                                                                    .value,
                                                            )
                                                        }
                                                        className="mt-1 block w-full rounded-lg border-slate-300"
                                                    />
                                                ) : (
                                                    <input
                                                        value={
                                                            item[
                                                                nested
                                                                    .key
                                                            ] ??
                                                            ""
                                                        }
                                                        onChange={(
                                                            event,
                                                        ) =>
                                                            update(
                                                                index,
                                                                nested.key,
                                                                event
                                                                    .target
                                                                    .value,
                                                            )
                                                        }
                                                        className="mt-1 block w-full rounded-lg border-slate-300"
                                                    />
                                                )}
                                            </div>
                                        ),
                                    )}
                                </div>
                            </div>
                        ),
                    )}
                </div>
            </div>
        );
    }

    return null;
}
