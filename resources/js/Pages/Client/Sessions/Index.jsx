import Pagination from "@/Components/Pagination";
import ClientLayout from "@/Layouts/ClientLayout";
import { Head } from "@inertiajs/react";

function formatValue(value) {
    if (value === null || value === undefined || value === "") {
        return "Not provided";
    }

    return String(value)
        .replaceAll("_", " ")
        .replace(/\b\w/g, (character) => character.toUpperCase());
}

function Detail({ label, value }) {
    return (
        <div className="rounded-lg bg-gray-50 p-4">
            <p className="text-xs font-medium uppercase tracking-wide text-gray-500">
                {label}
            </p>
            <p className="mt-1 whitespace-pre-line text-sm font-semibold text-gray-900">
                {formatValue(value)}
            </p>
        </div>
    );
}

function SessionCard({ session }) {
    return (
        <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg">
            <div className="border-b border-gray-100 px-6 py-5">
                <div className="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                    <div>
                        <h3 className="text-lg font-semibold text-gray-900">
                            {session.counsellor.name}
                        </h3>
                        <p className="mt-1 text-sm text-gray-500">
                            {formatValue(session.counsellor.professional_title)}
                        </p>
                    </div>

                    <div className="rounded-lg bg-indigo-50 px-4 py-3 text-sm text-indigo-800">
                        <p className="font-semibold">
                            {session.appointment.appointment_date}
                        </p>
                        <p>
                            {session.appointment.start_time} -{" "}
                            {session.appointment.end_time}
                        </p>
                    </div>
                </div>
            </div>

            <div className="grid gap-4 p-6 md:grid-cols-2">
                <Detail
                    label="Session summary"
                    value={session.client_visible_summary}
                />
                <Detail
                    label="Homework / next steps"
                    value={session.homework}
                />
                <Detail
                    label="Follow-up recommended"
                    value={session.follow_up_recommended ? "Yes" : "No"}
                />
                <Detail
                    label="Next session date"
                    value={session.next_session_recommended_at}
                />
            </div>

            {session.notes.length > 0 && (
                <div className="border-t border-gray-100 px-6 py-5">
                    <h4 className="text-sm font-semibold uppercase tracking-wide text-gray-500">
                        Shared Notes
                    </h4>

                    <div className="mt-4 space-y-3">
                        {session.notes.map((note) => (
                            <div
                                key={note.id}
                                className="rounded-lg border border-gray-100 bg-gray-50 p-4"
                            >
                                <p className="whitespace-pre-line text-sm text-gray-700">
                                    {note.content}
                                </p>
                                <p className="mt-2 text-xs text-gray-500">
                                    {note.author.name} · {note.created_at}
                                </p>
                            </div>
                        ))}
                    </div>
                </div>
            )}
        </div>
    );
}

export default function Index({ sessions }) {
    return (
        <ClientLayout
            header={
                <div>
                    <h2 className="text-xl font-semibold leading-tight text-gray-800">
                        My Sessions
                    </h2>
                    <p className="mt-1 text-sm text-gray-500">
                        Completed counselling session summaries and follow-up
                        notes.
                    </p>
                </div>
            }
        >
            <Head title="My Sessions" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                    <div className="rounded-lg border border-indigo-100 bg-indigo-50 p-4">
                        <p className="text-sm text-indigo-900">
                            Only completed sessions with client-visible
                            summaries are shown here. Private clinical notes
                            stay private, because boundaries are not just for
                            CSS boxes.
                        </p>
                    </div>

                    {sessions.data.length === 0 ? (
                        <div className="overflow-hidden bg-white p-8 text-center shadow-sm sm:rounded-lg">
                            <h3 className="text-base font-semibold text-gray-900">
                                No completed sessions yet
                            </h3>
                            <p className="mt-2 text-sm text-gray-500">
                                Your completed counselling session summaries
                                will appear here.
                            </p>
                        </div>
                    ) : (
                        <div className="space-y-5">
                            {sessions.data.map((session) => (
                                <SessionCard
                                    key={session.id}
                                    session={session}
                                />
                            ))}
                        </div>
                    )}

                    <Pagination links={sessions.links} />
                </div>
            </div>
        </ClientLayout>
    );
}
