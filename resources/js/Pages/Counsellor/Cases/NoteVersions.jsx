import {
    Head,
    Link,
} from "@inertiajs/react";
import {
    FileClock,
    LockKeyhole,
} from "lucide-react";

import CounsellorLayout from "@/Layouts/CounsellorLayout";

export default function NoteVersions({
    caseRecord,
    clinicalNote,
}) {
    return (
        <CounsellorLayout>
            <Head title="Clinical Note Versions" />

            <div className="py-12">
                <div className="mx-auto max-w-5xl space-y-6 sm:px-6 lg:px-8">
                    <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <div className="p-6">
                            <Link
                                href={route(
                                    "counsellor.cases.show",
                                    caseRecord.id,
                                )}
                                className="text-sm font-medium text-indigo-600 hover:text-indigo-700"
                            >
                                ← Back to case
                            </Link>

                            <div className="mt-3 flex flex-wrap items-start justify-between gap-4">
                                <div>
                                    <div className="flex items-center gap-2">
                                        <FileClock className="h-5 w-5 text-indigo-600" />

                                        <h1 className="text-2xl font-semibold text-slate-900">
                                            Clinical note
                                            version history
                                        </h1>
                                    </div>

                                    <p className="mt-2 text-sm text-slate-500">
                                        {caseRecord
                                            .client_profile
                                            ?.user
                                            ?.name ??
                                            "Client"}{" "}
                                        · Clinical note #
                                        {clinicalNote.id}
                                    </p>
                                </div>

                                {clinicalNote.locked_at && (
                                    <div className="inline-flex items-center gap-2 rounded-lg bg-slate-100 px-3 py-2 text-sm font-medium text-slate-700">
                                        <LockKeyhole className="h-4 w-4" />
                                        Signed &
                                        locked
                                    </div>
                                )}
                            </div>

                            <div className="mt-4 rounded-lg border border-indigo-100 bg-indigo-50 p-4 text-sm text-indigo-800">
                                Version history is
                                read-only. Historical
                                clinical documentation
                                cannot be altered.
                            </div>
                        </div>
                    </div>

                    {clinicalNote.versions
                        ?.length === 0 && (
                        <div className="rounded-lg bg-white p-8 text-center text-sm text-slate-500 shadow-sm">
                            No clinical note
                            versions are available.
                        </div>
                    )}

                    {clinicalNote.versions?.map(
                        (version) => (
                            <article
                                key={version.id}
                                className="overflow-hidden bg-white shadow-sm sm:rounded-lg"
                            >
                                <div className="p-6">
                                    <div className="flex flex-wrap items-start justify-between gap-4">
                                        <div>
                                            <h2 className="text-lg font-semibold text-slate-900">
                                                Version{" "}
                                                {
                                                    version.version
                                                }
                                            </h2>

                                            <p className="mt-1 text-xs text-slate-500">
                                                {
                                                    version.status
                                                }{" "}
                                                · Changed
                                                by{" "}
                                                {version
                                                    .changed_by
                                                    ?.name ??
                                                    "Not provided"}
                                            </p>
                                        </div>

                                        <div className="text-right text-xs text-slate-500">
                                            <p>
                                                {
                                                    version.created_at
                                                }
                                            </p>

                                            {version.locked_at && (
                                                <p className="mt-1 font-medium text-slate-700">
                                                    Locked
                                                </p>
                                            )}
                                        </div>
                                    </div>

                                    {version.change_reason && (
                                        <div className="mt-4 rounded-md bg-slate-50 p-3 text-sm text-slate-600">
                                            <span className="font-medium">
                                                Change
                                                reason:
                                            </span>{" "}
                                            {
                                                version.change_reason
                                            }
                                        </div>
                                    )}

                                    <div className="mt-6 grid gap-5">
                                        <ReadField
                                            label="Title"
                                            value={
                                                version.title
                                            }
                                        />

                                        <ReadField
                                            label="Clinical note"
                                            value={
                                                version.note
                                            }
                                        />

                                        <ReadField
                                            label="Formulation"
                                            value={
                                                version.formulation
                                            }
                                        />

                                        <ReadField
                                            label="Intervention"
                                            value={
                                                version.intervention
                                            }
                                        />

                                        <ReadField
                                            label="Risk assessment"
                                            value={
                                                version.risk_assessment
                                            }
                                        />

                                        <ReadField
                                            label="Plan"
                                            value={
                                                version.plan
                                            }
                                        />

                                        <div className="grid gap-4 sm:grid-cols-3">
                                            <ReadField
                                                label="Risk level"
                                                value={
                                                    version.risk_level
                                                }
                                            />

                                            <ReadField
                                                label="Status"
                                                value={
                                                    version.status
                                                }
                                            />

                                            <ReadField
                                                label="Signed"
                                                value={
                                                    version.signed_at ??
                                                    "Not provided"
                                                }
                                            />
                                        </div>
                                    </div>
                                </div>
                            </article>
                        ),
                    )}
                </div>
            </div>
        </CounsellorLayout>
    );
}

function ReadField({
    label,
    value,
}) {
    return (
        <div>
            <h3 className="text-xs font-semibold uppercase tracking-wide text-slate-500">
                {label}
            </h3>

            <p className="mt-1 whitespace-pre-wrap text-sm leading-6 text-slate-700">
                {value || "Not provided"}
            </p>
        </div>
    );
}
