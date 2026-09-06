import FixedFieldRenderer from "@/Pages/Admin/Cms/Components/FixedFieldRenderer";
import { useForm } from "@inertiajs/react";
import {
    CheckCircle2,
    Save,
} from "lucide-react";

export default function FixedSectionForm({
    page,
    section,
    media,
}) {
    const form = useForm({
        heading:
            section.heading ?? "",

        subheading:
            section.subheading ?? "",

        content:
            section.content ?? {},
    });

    const updateContent = (
        key,
        value,
    ) => {
        form.setData(
            "content",
            {
                ...form.data.content,
                [key]: value,
            },
        );
    };

    const submit = (event) => {
        event.preventDefault();

        form.patch(
            route(
                "admin.cms.content.section.update",
                [
                    page,
                    section.key,
                ],
            ),
            {
                preserveScroll: true,
            },
        );
    };

    return (
        <form
            onSubmit={submit}
            className="rounded-xl border border-slate-200 bg-white p-6 shadow-sm"
        >
            <div className="flex items-start justify-between gap-4 border-b border-slate-200 pb-5">
                <div>
                    <h3 className="text-lg font-semibold text-slate-900">
                        {section.label}
                    </h3>

                    <p className="mt-1 text-sm text-slate-500">
                        Edit the content shown in this fixed website section.
                    </p>
                </div>

                <CheckCircle2 className="h-5 w-5 text-emerald-500" />
            </div>

            <div className="mt-6 grid gap-5">
                <div>
                    <label className="text-sm font-medium text-slate-700">
                        Main heading
                    </label>

                    <input
                        value={
                            form.data
                                .heading
                        }
                        onChange={(
                            event,
                        ) =>
                            form.setData(
                                "heading",
                                event
                                    .target
                                    .value,
                            )
                        }
                        className="mt-1 block w-full rounded-lg border-slate-300"
                    />
                </div>

                <div>
                    <label className="text-sm font-medium text-slate-700">
                        Supporting text
                    </label>

                    <textarea
                        rows="4"
                        value={
                            form.data
                                .subheading
                        }
                        onChange={(
                            event,
                        ) =>
                            form.setData(
                                "subheading",
                                event
                                    .target
                                    .value,
                            )
                        }
                        className="mt-1 block w-full rounded-lg border-slate-300"
                    />
                </div>

                {section.fields.map(
                    (field) => (
                        <FixedFieldRenderer
                            key={
                                field.key
                            }
                            field={
                                field
                            }
                            value={
                                form.data
                                    .content[
                                    field.key
                                ]
                            }
                            media={media}
                            onChange={(
                                value,
                            ) =>
                                updateContent(
                                    field.key,
                                    value,
                                )
                            }
                        />
                    ),
                )}
            </div>

            <div className="mt-6 border-t border-slate-200 pt-5">
                <button
                    disabled={
                        form.processing
                    }
                    className="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white disabled:opacity-50"
                >
                    <Save className="h-4 w-4" />
                    Save{" "}
                    {section.label}
                </button>
            </div>
        </form>
    );
}
