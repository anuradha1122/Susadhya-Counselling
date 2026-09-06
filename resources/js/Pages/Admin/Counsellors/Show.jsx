import AdminLayout from "@/Layouts/AdminLayout";
import {
    Head,
    Link,
    router,
    usePage,
} from "@inertiajs/react";
import {
    Archive,
    ArrowLeft,
    Pencil,
    RotateCcw,
    UserRound,
} from "lucide-react";

const statusClasses = {
    active:
        "bg-emerald-100 text-emerald-700",
    inactive:
        "bg-amber-100 text-amber-700",
    archived:
        "bg-slate-200 text-slate-700",
};

const displayValue = (
    value,
) => value || "Not provided";

function ProfilePhoto({
    counsellor,
}) {
    const photoUrl =
        counsellor.user
            ?.profile_photo_url;

    if (photoUrl) {
        return (
            <img
                src={photoUrl}
                alt={`${counsellor.user?.name ?? "Counsellor"} profile`}
                className="h-28 w-28 rounded-2xl border border-slate-200 object-cover shadow-sm"
            />
        );
    }

    return (
        <div className="flex h-28 w-28 items-center justify-center rounded-2xl border border-slate-200 bg-slate-100 text-slate-400">
            <UserRound className="h-12 w-12" />
        </div>
    );
}

export default function Show({
    counsellor,
    permissions,
}) {
    const {
        flash = {},
    } = usePage().props;

    const archiveCounsellor =
        () => {
            if (
                ! window.confirm(
                    "Archive this counsellor? Their user account will also be deactivated.",
                )
            ) {
                return;
            }

            router.delete(
                route(
                    "admin.counsellors.destroy",
                    counsellor.uuid,
                ),
            );
        };

    const restoreCounsellor =
        () => {
            if (
                ! window.confirm(
                    "Restore this counsellor and reactivate their user account?",
                )
            ) {
                return;
            }

            router.patch(
                route(
                    "admin.counsellors.restore",
                    counsellor.uuid,
                ),
            );
        };

    return (
        <AdminLayout title="Counsellor Details">
            <Head title="Counsellor Details" />

            <div className="space-y-6">
                <div className="flex flex-col justify-between gap-5 sm:flex-row sm:items-start">
                    <div>
                        <Link
                            href={route(
                                "admin.counsellors.index",
                            )}
                            className="inline-flex items-center gap-2 text-sm font-medium text-slate-600 hover:text-slate-900"
                        >
                            <ArrowLeft className="h-4 w-4" />

                            Back to
                            counsellors
                        </Link>

                        <div className="mt-5 flex flex-col gap-4 sm:flex-row sm:items-center">
                            <ProfilePhoto
                                counsellor={
                                    counsellor
                                }
                            />

                            <div>
                                <h1 className="text-2xl font-bold text-slate-900">
                                    {
                                        counsellor
                                            .user
                                            ?.name
                                    }
                                </h1>

                                <p className="mt-1 text-sm text-slate-500">
                                    {counsellor.professional_title
                                        || "Counsellor"}{" "}
                                    ·{" "}
                                    {
                                        counsellor.registration_number
                                    }
                                </p>

                                <span
                                    className={`mt-3 inline-flex rounded-full px-3 py-1 text-xs font-medium capitalize ${
                                        statusClasses[
                                            counsellor
                                                .status
                                        ]
                                    }`}
                                >
                                    {
                                        counsellor.status
                                    }
                                </span>
                            </div>
                        </div>
                    </div>

                    <div className="flex flex-wrap gap-3">
                        {permissions.update
                            && counsellor.status
                                !==
                                "archived" && (
                                <Link
                                    href={route(
                                        "admin.counsellors.edit",
                                        counsellor.uuid,
                                    )}
                                    className="inline-flex items-center gap-2 rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
                                >
                                    <Pencil className="h-4 w-4" />

                                    Edit
                                </Link>
                            )}

                        {permissions.archive && (
                            <button
                                type="button"
                                onClick={
                                    archiveCounsellor
                                }
                                className="inline-flex items-center gap-2 rounded-xl bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700"
                            >
                                <Archive className="h-4 w-4" />

                                Archive
                            </button>
                        )}

                        {permissions.restore && (
                            <button
                                type="button"
                                onClick={
                                    restoreCounsellor
                                }
                                className="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700"
                            >
                                <RotateCcw className="h-4 w-4" />

                                Restore
                            </button>
                        )}
                    </div>
                </div>

                {flash.success && (
                    <div className="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                        {
                            flash.success
                        }
                    </div>
                )}

                <div className="grid gap-6 lg:grid-cols-3">
                    <section className="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm lg:col-span-2">
                        <h2 className="text-lg font-semibold text-slate-900">
                            Profile
                        </h2>

                        <dl className="mt-5 grid gap-5 sm:grid-cols-2">
                            <div>
                                <dt className="text-sm text-slate-500">
                                    Email
                                </dt>

                                <dd className="mt-1 font-medium text-slate-900">
                                    {displayValue(
                                        counsellor
                                            .user
                                            ?.email,
                                    )}
                                </dd>
                            </div>

                            <div>
                                <dt className="text-sm text-slate-500">
                                    NIC
                                </dt>

                                <dd className="mt-1 font-medium text-slate-900">
                                    {displayValue(
                                        counsellor.nic,
                                    )}
                                </dd>
                            </div>

                            <div>
                                <dt className="text-sm text-slate-500">
                                    Date of
                                    birth
                                </dt>

                                <dd className="mt-1 font-medium text-slate-900">
                                    {displayValue(
                                        counsellor.date_of_birth,
                                    )}
                                </dd>
                            </div>

                            <div>
                                <dt className="text-sm text-slate-500">
                                    Gender
                                </dt>

                                <dd className="mt-1 font-medium capitalize text-slate-900">
                                    {displayValue(
                                        counsellor.gender?.replaceAll(
                                            "_",
                                            " ",
                                        ),
                                    )}
                                </dd>
                            </div>

                            <div>
                                <dt className="text-sm text-slate-500">
                                    Experience
                                </dt>

                                <dd className="mt-1 font-medium text-slate-900">
                                    {
                                        counsellor.years_of_experience
                                    }{" "}
                                    years
                                </dd>
                            </div>

                            <div>
                                <dt className="text-sm text-slate-500">
                                    City
                                </dt>

                                <dd className="mt-1 font-medium text-slate-900">
                                    {displayValue(
                                        counsellor.city,
                                    )}
                                </dd>
                            </div>

                            <div className="sm:col-span-2">
                                <dt className="text-sm text-slate-500">
                                    Address
                                </dt>

                                <dd className="mt-1 whitespace-pre-line text-slate-900">
                                    {displayValue(
                                        counsellor.address,
                                    )}
                                </dd>
                            </div>

                            <div className="sm:col-span-2">
                                <dt className="text-sm text-slate-500">
                                    Biography
                                </dt>

                                <dd className="mt-1 whitespace-pre-line text-slate-900">
                                    {displayValue(
                                        counsellor.biography,
                                    )}
                                </dd>
                            </div>
                        </dl>
                    </section>

                    <section className="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <h2 className="text-lg font-semibold text-slate-900">
                            Status
                        </h2>

                        <div className="mt-5 space-y-4">
                            <span
                                className={`inline-flex rounded-full px-3 py-1 text-sm font-medium capitalize ${
                                    statusClasses[
                                        counsellor
                                            .status
                                    ]
                                }`}
                            >
                                {
                                    counsellor.status
                                }
                            </span>

                            <div>
                                <p className="text-sm text-slate-500">
                                    User
                                    account
                                </p>

                                <p className="mt-1 font-medium text-slate-900">
                                    {counsellor
                                        .user
                                        ?.is_active
                                        ? "Active"
                                        : "Inactive"}
                                </p>
                            </div>

                            {counsellor.archived_at && (
                                <>
                                    <div>
                                        <p className="text-sm text-slate-500">
                                            Archived
                                            at
                                        </p>

                                        <p className="mt-1 font-medium text-slate-900">
                                            {
                                                counsellor.archived_at
                                            }
                                        </p>
                                    </div>

                                    <div>
                                        <p className="text-sm text-slate-500">
                                            Archived
                                            by
                                        </p>

                                        <p className="mt-1 font-medium text-slate-900">
                                            {displayValue(
                                                counsellor
                                                    .archived_by
                                                    ?.name,
                                            )}
                                        </p>
                                    </div>
                                </>
                            )}
                        </div>
                    </section>
                </div>

                <div className="grid gap-6 lg:grid-cols-2">
                    <section className="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <h2 className="text-lg font-semibold text-slate-900">
                            Specializations
                        </h2>

                        <div className="mt-5 flex flex-wrap gap-2">
                            {counsellor.specializations.map(
                                (
                                    specialization,
                                ) => (
                                    <span
                                        key={
                                            specialization.id
                                        }
                                        className="rounded-full bg-indigo-50 px-3 py-1.5 text-sm text-indigo-700"
                                    >
                                        {
                                            specialization.name
                                        }
                                    </span>
                                ),
                            )}

                            {counsellor
                                .specializations
                                .length
                                ===
                                0 && (
                                <p className="text-sm text-slate-500">
                                    No
                                    specializations
                                    recorded.
                                </p>
                            )}
                        </div>
                    </section>

                    <section className="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <h2 className="text-lg font-semibold text-slate-900">
                            Languages
                        </h2>

                        <div className="mt-5 space-y-3">
                            {counsellor.languages.map(
                                (
                                    language,
                                ) => (
                                    <div
                                        key={
                                            language.id
                                        }
                                        className="flex justify-between rounded-xl bg-slate-50 px-4 py-3"
                                    >
                                        <span className="text-sm font-medium text-slate-900">
                                            {
                                                language.name
                                            }
                                        </span>

                                        <span className="text-sm capitalize text-slate-500">
                                            {
                                                language
                                                    .pivot
                                                    ?.proficiency
                                            }
                                        </span>
                                    </div>
                                ),
                            )}

                            {counsellor
                                .languages
                                .length
                                ===
                                0 && (
                                <p className="text-sm text-slate-500">
                                    No
                                    languages
                                    recorded.
                                </p>
                            )}
                        </div>
                    </section>
                </div>

                <section className="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h2 className="text-lg font-semibold text-slate-900">
                        Qualifications
                    </h2>

                    <div className="mt-5 overflow-x-auto">
                        <table className="min-w-full divide-y divide-slate-200">
                            <thead className="bg-slate-50">
                                <tr>
                                    <th className="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">
                                        Qualification
                                    </th>

                                    <th className="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">
                                        Institution
                                    </th>

                                    <th className="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">
                                        Field
                                    </th>

                                    <th className="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">
                                        Year
                                    </th>

                                    <th className="px-4 py-3 text-left text-xs font-semibold uppercase text-slate-500">
                                        Certificate
                                    </th>
                                </tr>
                            </thead>

                            <tbody className="divide-y divide-slate-100">
                                {counsellor.qualifications.map(
                                    (
                                        qualification,
                                    ) => (
                                        <tr
                                            key={
                                                qualification.id
                                            }
                                        >
                                            <td className="px-4 py-3 text-sm font-medium text-slate-900">
                                                {
                                                    qualification.qualification
                                                }
                                            </td>

                                            <td className="px-4 py-3 text-sm text-slate-700">
                                                {
                                                    qualification.institution
                                                }
                                            </td>

                                            <td className="px-4 py-3 text-sm text-slate-700">
                                                {displayValue(
                                                    qualification.field_of_study,
                                                )}
                                            </td>

                                            <td className="px-4 py-3 text-sm text-slate-700">
                                                {displayValue(
                                                    qualification.year_completed,
                                                )}
                                            </td>

                                            <td className="px-4 py-3 text-sm text-slate-700">
                                                {displayValue(
                                                    qualification.certificate_number,
                                                )}
                                            </td>
                                        </tr>
                                    ),
                                )}

                                {counsellor
                                    .qualifications
                                    .length
                                    ===
                                    0 && (
                                    <tr>
                                        <td
                                            colSpan="5"
                                            className="px-4 py-10 text-center text-sm text-slate-500"
                                        >
                                            No
                                            qualifications
                                            recorded.
                                        </td>
                                    </tr>
                                )}
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>
        </AdminLayout>
    );
}
