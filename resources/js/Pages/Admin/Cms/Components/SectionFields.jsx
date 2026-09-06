import MediaPicker from "@/Pages/Admin/Cms/Components/MediaPicker";
import {
    Plus,
    Trash2,
} from "lucide-react";

function TextField({
    label,
    value = "",
    onChange,
    placeholder = "",
}) {
    return (
        <div>
            <label className="text-sm font-medium text-slate-700">
                {label}
            </label>

            <input
                value={value ?? ""}
                onChange={(event) =>
                    onChange(
                        event.target.value,
                    )
                }
                placeholder={placeholder}
                className="mt-1 block w-full rounded-lg border-slate-300"
            />
        </div>
    );
}

function TextArea({
    label,
    value = "",
    onChange,
    rows = 5,
    placeholder = "",
}) {
    return (
        <div>
            <label className="text-sm font-medium text-slate-700">
                {label}
            </label>

            <textarea
                rows={rows}
                value={value ?? ""}
                onChange={(event) =>
                    onChange(
                        event.target.value,
                    )
                }
                placeholder={placeholder}
                className="mt-1 block w-full rounded-lg border-slate-300"
            />
        </div>
    );
}

function StringList({
    label,
    items = [],
    onChange,
    placeholder,
}) {
    const update = (
        index,
        value,
    ) => {
        const next = [...items];

        next[index] = value;

        onChange(next);
    };

    const remove = (index) => {
        onChange(
            items.filter(
                (_, itemIndex) =>
                    itemIndex !== index,
            ),
        );
    };

    return (
        <div>
            <label className="text-sm font-medium text-slate-700">
                {label}
            </label>

            <div className="mt-2 space-y-2">
                {items.map(
                    (item, index) => (
                        <div
                            key={index}
                            className="flex gap-2"
                        >
                            <input
                                value={item}
                                onChange={(
                                    event,
                                ) =>
                                    update(
                                        index,
                                        event
                                            .target
                                            .value,
                                    )
                                }
                                placeholder={
                                    placeholder
                                }
                                className="flex-1 rounded-lg border-slate-300"
                            />

                            <button
                                type="button"
                                onClick={() =>
                                    remove(
                                        index,
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

function StatsEditor({
    items = [],
    onChange,
}) {
    const add = () => {
        onChange([
            ...items,
            {
                value: "",
                label: "",
            },
        ]);
    };

    const update = (
        index,
        key,
        value,
    ) => {
        const next =
            items.map(
                (item, itemIndex) =>
                    itemIndex === index
                        ? {
                              ...item,
                              [key]:
                                  value,
                          }
                        : item,
            );

        onChange(next);
    };

    const remove = (index) => {
        onChange(
            items.filter(
                (_, itemIndex) =>
                    itemIndex !== index,
            ),
        );
    };

    return (
        <div>
            <h4 className="text-sm font-medium text-slate-700">
                Statistics / highlights
            </h4>

            <div className="mt-3 space-y-3">
                {items.map(
                    (item, index) => (
                        <div
                            key={index}
                            className="grid gap-3 rounded-xl border border-slate-200 p-4 md:grid-cols-[180px_1fr_auto]"
                        >
                            <input
                                value={
                                    item.value ??
                                    ""
                                }
                                onChange={(
                                    event,
                                ) =>
                                    update(
                                        index,
                                        "value",
                                        event
                                            .target
                                            .value,
                                    )
                                }
                                placeholder="Private"
                                className="rounded-lg border-slate-300"
                            />

                            <input
                                value={
                                    item.label ??
                                    ""
                                }
                                onChange={(
                                    event,
                                ) =>
                                    update(
                                        index,
                                        "label",
                                        event
                                            .target
                                            .value,
                                    )
                                }
                                placeholder="Confidential counselling environment"
                                className="rounded-lg border-slate-300"
                            />

                            <button
                                type="button"
                                onClick={() =>
                                    remove(
                                        index,
                                    )
                                }
                                className="rounded-lg border border-rose-200 p-2 text-rose-600"
                            >
                                <Trash2 className="h-4 w-4" />
                            </button>
                        </div>
                    ),
                )}
            </div>

            <button
                type="button"
                onClick={add}
                className="mt-3 inline-flex items-center gap-2 text-sm font-medium text-indigo-600"
            >
                <Plus className="h-4 w-4" />
                Add highlight
            </button>
        </div>
    );
}

function StepsEditor({
    items = [],
    onChange,
}) {
    const add = () => {
        onChange([
            ...items,
            {
                number: String(
                    items.length + 1,
                ).padStart(2, "0"),
                title: "",
                description: "",
            },
        ]);
    };

    const update = (
        index,
        key,
        value,
    ) => {
        onChange(
            items.map(
                (item, itemIndex) =>
                    itemIndex === index
                        ? {
                              ...item,
                              [key]:
                                  value,
                          }
                        : item,
            ),
        );
    };

    const remove = (index) => {
        onChange(
            items.filter(
                (_, itemIndex) =>
                    itemIndex !== index,
            ),
        );
    };

    return (
        <div>
            <h4 className="text-sm font-medium text-slate-700">
                Steps
            </h4>

            <div className="mt-3 space-y-4">
                {items.map(
                    (item, index) => (
                        <div
                            key={index}
                            className="rounded-xl border border-slate-200 p-4"
                        >
                            <div className="grid gap-3 md:grid-cols-[100px_1fr_auto]">
                                <input
                                    value={
                                        item.number ??
                                        ""
                                    }
                                    onChange={(
                                        event,
                                    ) =>
                                        update(
                                            index,
                                            "number",
                                            event
                                                .target
                                                .value,
                                        )
                                    }
                                    placeholder="01"
                                    className="rounded-lg border-slate-300"
                                />

                                <input
                                    value={
                                        item.title ??
                                        ""
                                    }
                                    onChange={(
                                        event,
                                    ) =>
                                        update(
                                            index,
                                            "title",
                                            event
                                                .target
                                                .value,
                                        )
                                    }
                                    placeholder="Step title"
                                    className="rounded-lg border-slate-300"
                                />

                                <button
                                    type="button"
                                    onClick={() =>
                                        remove(
                                            index,
                                        )
                                    }
                                    className="rounded-lg border border-rose-200 p-2 text-rose-600"
                                >
                                    <Trash2 className="h-4 w-4" />
                                </button>
                            </div>

                            <textarea
                                rows="3"
                                value={
                                    item.description ??
                                    ""
                                }
                                onChange={(
                                    event,
                                ) =>
                                    update(
                                        index,
                                        "description",
                                        event
                                            .target
                                            .value,
                                    )
                                }
                                placeholder="Explain this step..."
                                className="mt-3 block w-full rounded-lg border-slate-300"
                            />
                        </div>
                    ),
                )}
            </div>

            <button
                type="button"
                onClick={add}
                className="mt-3 inline-flex items-center gap-2 text-sm font-medium text-indigo-600"
            >
                <Plus className="h-4 w-4" />
                Add step
            </button>
        </div>
    );
}

function FeaturesEditor({
    items = [],
    onChange,
}) {
    const add = () => {
        onChange([
            ...items,
            {
                title: "",
                description: "",
                icon: "heart",
            },
        ]);
    };

    const update = (
        index,
        key,
        value,
    ) => {
        onChange(
            items.map(
                (item, itemIndex) =>
                    itemIndex === index
                        ? {
                              ...item,
                              [key]:
                                  value,
                          }
                        : item,
            ),
        );
    };

    const remove = (index) => {
        onChange(
            items.filter(
                (_, itemIndex) =>
                    itemIndex !== index,
            ),
        );
    };

    return (
        <div>
            <h4 className="text-sm font-medium text-slate-700">
                Feature cards
            </h4>

            <div className="mt-3 space-y-4">
                {items.map(
                    (item, index) => (
                        <div
                            key={index}
                            className="rounded-xl border border-slate-200 p-4"
                        >
                            <div className="grid gap-3 md:grid-cols-[1fr_180px_auto]">
                                <input
                                    value={
                                        item.title ??
                                        ""
                                    }
                                    onChange={(
                                        event,
                                    ) =>
                                        update(
                                            index,
                                            "title",
                                            event
                                                .target
                                                .value,
                                        )
                                    }
                                    placeholder="Feature title"
                                    className="rounded-lg border-slate-300"
                                />

                                <select
                                    value={
                                        item.icon ??
                                        "heart"
                                    }
                                    onChange={(
                                        event,
                                    ) =>
                                        update(
                                            index,
                                            "icon",
                                            event
                                                .target
                                                .value,
                                        )
                                    }
                                    className="rounded-lg border-slate-300"
                                >
                                    <option value="heart">
                                        Care
                                    </option>

                                    <option value="shield">
                                        Privacy
                                    </option>

                                    <option value="badge">
                                        Professional
                                    </option>
                                </select>

                                <button
                                    type="button"
                                    onClick={() =>
                                        remove(
                                            index,
                                        )
                                    }
                                    className="rounded-lg border border-rose-200 p-2 text-rose-600"
                                >
                                    <Trash2 className="h-4 w-4" />
                                </button>
                            </div>

                            <textarea
                                rows="3"
                                value={
                                    item.description ??
                                    ""
                                }
                                onChange={(
                                    event,
                                ) =>
                                    update(
                                        index,
                                        "description",
                                        event
                                            .target
                                            .value,
                                    )
                                }
                                placeholder="Feature description"
                                className="mt-3 block w-full rounded-lg border-slate-300"
                            />
                        </div>
                    ),
                )}
            </div>

            <button
                type="button"
                onClick={add}
                className="mt-3 inline-flex items-center gap-2 text-sm font-medium text-indigo-600"
            >
                <Plus className="h-4 w-4" />
                Add feature
            </button>
        </div>
    );
}

export function defaultContent(
    type,
) {
    switch (type) {
        case "hero":
            return {
                eyebrow: "",
                image_path: "",
                image_alt: "",
                visual: "session",
                primary_cta_label:
                    "Find a Counsellor",
                primary_cta_url:
                    "/counsellors",
                secondary_cta_label:
                    "Explore Services",
                secondary_cta_url:
                    "/services",
                trust_items: [],
            };

        case "stats":
            return {
                items: [],
            };

        case "steps":
            return {
                items: [],
            };

        case "image_text":
            return {
                eyebrow: "",
                body: "",
                image_path: "",
                image_alt: "",
                image_position: "right",
                visual: "session",
                points: [],
            };

        case "feature_grid":
            return {
                items: [],
            };

        case "services":
            return {
                limit: 6,
            };

        case "counsellors":
            return {
                limit: 4,
            };

        case "faq":
            return {
                limit: 6,
            };

        case "testimonials":
            return {
                limit: 6,
            };

        case "cta":
            return {
                primary_cta_label:
                    "Find a Counsellor",
                primary_cta_url:
                    "/counsellors",
            };

        case "rich_text":
            return {
                body: "",
            };

        default:
            return {};
    }
}

export default function SectionFields({
    type,
    content,
    setContent,
    media,
}) {
    const update = (
        key,
        value,
    ) => {
        setContent({
            ...content,
            [key]: value,
        });
    };

    if (type === "hero") {
        return (
            <div className="space-y-5">
                <TextField
                    label="Small heading"
                    value={
                        content.eyebrow
                    }
                    onChange={(value) =>
                        update(
                            "eyebrow",
                            value,
                        )
                    }
                    placeholder="Professional Online Counselling"
                />

                <MediaPicker
                    label="Hero image"
                    media={media}
                    value={
                        content.image_path ??
                        ""
                    }
                    altText={
                        content.image_alt ??
                        ""
                    }
                    onSelect={(item) =>
                        setContent({
                            ...content,
                            image_path:
                                item.url,
                            image_alt:
                                item.alt_text ??
                                "",
                        })
                    }
                    onClear={() =>
                        setContent({
                            ...content,
                            image_path: "",
                            image_alt: "",
                        })
                    }
                />

                {content.image_path && (
                    <TextField
                        label="Image description / alt text"
                        value={
                            content.image_alt
                        }
                        onChange={(value) =>
                            update(
                                "image_alt",
                                value,
                            )
                        }
                    />
                )}

                <div className="grid gap-4 md:grid-cols-2">
                    <TextField
                        label="Primary button text"
                        value={
                            content.primary_cta_label
                        }
                        onChange={(value) =>
                            update(
                                "primary_cta_label",
                                value,
                            )
                        }
                    />

                    <TextField
                        label="Primary button link"
                        value={
                            content.primary_cta_url
                        }
                        onChange={(value) =>
                            update(
                                "primary_cta_url",
                                value,
                            )
                        }
                    />

                    <TextField
                        label="Secondary button text"
                        value={
                            content.secondary_cta_label
                        }
                        onChange={(value) =>
                            update(
                                "secondary_cta_label",
                                value,
                            )
                        }
                    />

                    <TextField
                        label="Secondary button link"
                        value={
                            content.secondary_cta_url
                        }
                        onChange={(value) =>
                            update(
                                "secondary_cta_url",
                                value,
                            )
                        }
                    />
                </div>

                <StringList
                    label="Trust points"
                    items={
                        content.trust_items ??
                        []
                    }
                    onChange={(items) =>
                        update(
                            "trust_items",
                            items,
                        )
                    }
                    placeholder="Confidential support"
                />
            </div>
        );
    }

    if (type === "stats") {
        return (
            <StatsEditor
                items={
                    content.items ?? []
                }
                onChange={(items) =>
                    update(
                        "items",
                        items,
                    )
                }
            />
        );
    }

    if (type === "steps") {
        return (
            <StepsEditor
                items={
                    content.items ?? []
                }
                onChange={(items) =>
                    update(
                        "items",
                        items,
                    )
                }
            />
        );
    }

    if (
        type === "feature_grid"
    ) {
        return (
            <FeaturesEditor
                items={
                    content.items ?? []
                }
                onChange={(items) =>
                    update(
                        "items",
                        items,
                    )
                }
            />
        );
    }

    if (
        type === "image_text"
    ) {
        return (
            <div className="space-y-5">
                <TextField
                    label="Small heading"
                    value={
                        content.eyebrow
                    }
                    onChange={(value) =>
                        update(
                            "eyebrow",
                            value,
                        )
                    }
                />

                <TextArea
                    label="Main text"
                    value={
                        content.body
                    }
                    onChange={(value) =>
                        update(
                            "body",
                            value,
                        )
                    }
                    rows={7}
                />

                <MediaPicker
                    label="Section image"
                    media={media}
                    value={
                        content.image_path ??
                        ""
                    }
                    altText={
                        content.image_alt ??
                        ""
                    }
                    onSelect={(item) =>
                        setContent({
                            ...content,
                            image_path:
                                item.url,
                            image_alt:
                                item.alt_text ??
                                "",
                        })
                    }
                    onClear={() =>
                        setContent({
                            ...content,
                            image_path: "",
                            image_alt: "",
                        })
                    }
                />

                <div>
                    <label className="text-sm font-medium text-slate-700">
                        Image position
                    </label>

                    <select
                        value={
                            content.image_position ??
                            "right"
                        }
                        onChange={(
                            event,
                        ) =>
                            update(
                                "image_position",
                                event
                                    .target
                                    .value,
                            )
                        }
                        className="mt-1 block w-full rounded-lg border-slate-300"
                    >
                        <option value="left">
                            Left
                        </option>

                        <option value="right">
                            Right
                        </option>
                    </select>
                </div>

                <StringList
                    label="Bullet points"
                    items={
                        content.points ??
                        []
                    }
                    onChange={(items) =>
                        update(
                            "points",
                            items,
                        )
                    }
                    placeholder="Add a supporting point"
                />
            </div>
        );
    }

    if (
        [
            "services",
            "counsellors",
            "faq",
            "testimonials",
        ].includes(type)
    ) {
        return (
            <div className="max-w-xs">
                <label className="text-sm font-medium text-slate-700">
                    Number of items to show
                </label>

                <input
                    type="number"
                    min="1"
                    max="24"
                    value={
                        content.limit ?? 6
                    }
                    onChange={(event) =>
                        update(
                            "limit",
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

    if (type === "cta") {
        return (
            <div className="grid gap-4 md:grid-cols-2">
                <TextField
                    label="Button text"
                    value={
                        content.primary_cta_label
                    }
                    onChange={(value) =>
                        update(
                            "primary_cta_label",
                            value,
                        )
                    }
                />

                <TextField
                    label="Button link"
                    value={
                        content.primary_cta_url
                    }
                    onChange={(value) =>
                        update(
                            "primary_cta_url",
                            value,
                        )
                    }
                />
            </div>
        );
    }

    if (type === "rich_text") {
        return (
            <TextArea
                label="Content"
                value={
                    content.body
                }
                onChange={(value) =>
                    update(
                        "body",
                        value,
                    )
                }
                rows={12}
            />
        );
    }

    return (
        <div className="rounded-lg bg-slate-50 p-4 text-sm text-slate-500">
            This section has no additional fields.
        </div>
    );
}
