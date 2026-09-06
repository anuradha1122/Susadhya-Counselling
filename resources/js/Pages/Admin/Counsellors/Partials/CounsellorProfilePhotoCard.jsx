import {
    Camera,
    ImagePlus,
    Trash2,
} from "lucide-react";
import {
    useEffect,
    useState,
} from "react";

const errorClass =
    "mt-2 text-sm text-rose-600";

export default function CounsellorProfilePhotoCard({
    data,
    setData,
    errors,
    currentPhotoUrl = null,
}) {
    const [
        previewUrl,
        setPreviewUrl,
    ] = useState(
        currentPhotoUrl,
    );

    useEffect(() => {
        if (
            data.profile_photo
            instanceof File
        ) {
            const objectUrl =
                URL.createObjectURL(
                    data.profile_photo,
                );

            setPreviewUrl(
                objectUrl,
            );

            return () => {
                URL.revokeObjectURL(
                    objectUrl,
                );
            };
        }

        if (
            data.remove_profile_photo
        ) {
            setPreviewUrl(
                null,
            );

            return undefined;
        }

        setPreviewUrl(
            currentPhotoUrl,
        );

        return undefined;
    }, [
        data.profile_photo,
        data.remove_profile_photo,
        currentPhotoUrl,
    ]);

    const handlePhotoChange = (
        event,
    ) => {
        const file =
            event.target.files?.[0]
            ?? null;

        setData(
            "profile_photo",
            file,
        );

        if (file) {
            setData(
                "remove_profile_photo",
                false,
            );
        }
    };

    const removePhoto = () => {
        setData(
            "profile_photo",
            null,
        );

        setData(
            "remove_profile_photo",
            Boolean(
                currentPhotoUrl,
            ),
        );

        setPreviewUrl(
            null,
        );
    };

    return (
        <section className="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div className="flex flex-col gap-5 sm:flex-row sm:items-start">
                <div className="flex h-36 w-36 shrink-0 items-center justify-center overflow-hidden rounded-2xl border border-slate-200 bg-slate-50">
                    {previewUrl ? (
                        <img
                            src={
                                previewUrl
                            }
                            alt="Counsellor profile preview"
                            className="h-full w-full object-cover"
                        />
                    ) : (
                        <div className="flex flex-col items-center gap-2 text-slate-400">
                            <Camera
                                size={
                                    34
                                }
                            />

                            <span className="text-xs font-medium">
                                No photo
                            </span>
                        </div>
                    )}
                </div>

                <div className="min-w-0 flex-1">
                    <h2 className="text-lg font-semibold text-slate-900">
                        Profile photo
                    </h2>

                    <p className="mt-1 max-w-2xl text-sm leading-6 text-slate-500">
                        This photo
                        appears on
                        the public
                        counsellor
                        profile and
                        counsellor
                        cards.
                    </p>

                    <div className="mt-4 flex flex-wrap gap-3">
                        <label className="inline-flex cursor-pointer items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-medium text-white shadow-sm transition hover:bg-indigo-700">
                            <ImagePlus
                                size={
                                    17
                                }
                            />

                            {previewUrl
                                ? "Change photo"
                                : "Upload photo"}

                            <input
                                type="file"
                                accept="image/jpeg,image/png,image/webp"
                                onChange={
                                    handlePhotoChange
                                }
                                className="sr-only"
                            />
                        </label>

                        {previewUrl && (
                            <button
                                type="button"
                                onClick={
                                    removePhoto
                                }
                                className="inline-flex items-center gap-2 rounded-xl border border-rose-200 bg-white px-4 py-2.5 text-sm font-medium text-rose-700 transition hover:bg-rose-50"
                            >
                                <Trash2
                                    size={
                                        17
                                    }
                                />

                                Remove
                                photo
                            </button>
                        )}
                    </div>

                    <p className="mt-3 text-xs leading-5 text-slate-500">
                        JPG, JPEG,
                        PNG or WebP.
                        Maximum file
                        size 5 MB.
                        Maximum image
                        size 4000 ×
                        4000 pixels.
                    </p>

                    {data.profile_photo && (
                        <p className="mt-2 truncate text-xs font-medium text-slate-600">
                            Selected:{" "}
                            {
                                data
                                    .profile_photo
                                    .name
                            }
                        </p>
                    )}

                    {errors.profile_photo && (
                        <p
                            className={
                                errorClass
                            }
                        >
                            {
                                errors.profile_photo
                            }
                        </p>
                    )}
                </div>
            </div>
        </section>
    );
}
