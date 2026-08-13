import Pagination from "@/Components/Pagination";
import PrimaryButton from "@/Components/PrimaryButton";
import SecondaryButton from "@/Components/SecondaryButton";
import TextInput from "@/Components/TextInput";
import AdminLayout from "@/Layouts/AdminLayout";
import { Head, router, useForm } from "@inertiajs/react";

function formatValue(value) {
    if (value === null || value === undefined || value === "") {
        return "Not provided";
    }

    return String(value)
        .replaceAll("_", " ")
        .replace(/\b\w/g, (character) => character.toUpperCase());
}

function StatusBadge({ active }) {
    return (
        <span
            className={`rounded-full px-3 py-1 text-xs font-semibold ${
                active
                    ? "bg-green-100 text-green-800"
                    : "bg-gray-100 text-gray-700"
            }`}
        >
            {active ? "Active" : "Inactive"}
        </span>
    );
}

function CountBadge({ label, value }) {
    return (
        <div className="rounded-lg border border-gray-200 bg-gray-50 px-4 py-3">
            <p className="text-xs font-medium uppercase tracking-wide text-gray-500">
                {label}
            </p>
            <p className="mt-1 text-xl font-semibold text-gray-900">{value}</p>
        </div>
    );
}

function EmptyMessage({ children }) {
    return <p className="text-sm text-gray-500">{children}</p>;
}

function TimeRange({ start, end, fullDay }) {
    if (fullDay) {
        return <span>Full day</span>;
    }

    return (
        <span>
            {formatValue(start)} - {formatValue(end)}
        </span>
    );
}

export default function Index({ counsellors, filters, options }) {
    const { data, setData, get, processing } = useForm({
        search: filters.search ?? "",
        day_of_week: filters.day_of_week ?? "",
        status: filters.status ?? "",
    });

    const submit = (event) => {
        event.preventDefault();

        get(route("admin.availability.index"), {
            preserveScroll: true,
            preserveState: true,
            replace: true,
        });
    };

    const resetFilters = () => {
        router.get(
            route("admin.availability.index"),
            {},
            {
                preserveScroll: true,
                replace: true,
            },
        );
    };

    return (
        <AdminLayout
            header={
                <div>
                    <h2 className="text-xl font-semibold leading-tight text-gray-800">
                        Availability Oversight
                    </h2>
                    <p className="mt-1 text-sm text-gray-500">
                        Review counsellor recurring availability, breaks,
                        blocked slots, and leave days.
                    </p>
                </div>
            }
        >
            <Head title="Availability Oversight" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                    <div className="rounded-lg border border-indigo-100 bg-indigo-50 p-4">
                        <p className="text-sm text-indigo-900">
                            This is a read-only oversight page. Counsellors
                            manage their own availability from My Availability.
                            Admin can review whether availability, breaks,
                            blocked slots, and leave days are configured
                            correctly.
                        </p>
                    </div>

                    <form
                        onSubmit={submit}
                        className="overflow-hidden bg-white p-6 shadow-sm sm:rounded-lg"
                    >
                        <div className="grid gap-4 lg:grid-cols-4">
                            <div className="lg:col-span-2">
                                <label
                                    htmlFor="search"
                                    className="text-sm font-medium text-gray-700"
                                >
                                    Search counsellor
                                </label>

                                <TextInput
                                    id="search"
                                    value={data.search}
                                    className="mt-1 block w-full"
                                    placeholder="Name, email, or phone"
                                    onChange={(event) =>
                                        setData("search", event.target.value)
                                    }
                                />
                            </div>

                            <div>
                                <label
                                    htmlFor="day_of_week"
                                    className="text-sm font-medium text-gray-700"
                                >
                                    Day
                                </label>

                                <select
                                    id="day_of_week"
                                    value={data.day_of_week}
                                    onChange={(event) =>
                                        setData(
                                            "day_of_week",
                                            event.target.value,
                                        )
                                    }
                                    className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                >
                                    <option value="">All days</option>
                                    {options.days.map((day) => (
                                        <option
                                            key={day.value}
                                            value={day.value}
                                        >
                                            {day.label}
                                        </option>
                                    ))}
                                </select>
                            </div>

                            <div>
                                <label
                                    htmlFor="status"
                                    className="text-sm font-medium text-gray-700"
                                >
                                    Rule status
                                </label>

                                <select
                                    id="status"
                                    value={data.status}
                                    onChange={(event) =>
                                        setData("status", event.target.value)
                                    }
                                    className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                >
                                    {options.statuses.map((status) => (
                                        <option
                                            key={status.value}
                                            value={status.value}
                                        >
                                            {status.label}
                                        </option>
                                    ))}
                                </select>
                            </div>
                        </div>

                        <div className="mt-4 flex flex-wrap justify-end gap-3">
                            <SecondaryButton
                                type="button"
                                onClick={resetFilters}
                            >
                                Reset
                            </SecondaryButton>

                            <PrimaryButton disabled={processing}>
                                Apply filters
                            </PrimaryButton>
                        </div>
                    </form>

                    <div className="space-y-6">
                        {counsellors.data.length === 0 ? (
                            <div className="overflow-hidden bg-white p-8 text-center shadow-sm sm:rounded-lg">
                                <p className="text-sm text-gray-500">
                                    No counsellor availability records found.
                                </p>
                            </div>
                        ) : (
                            counsellors.data.map((counsellor) => (
                                <div
                                    key={counsellor.id}
                                    className="overflow-hidden bg-white shadow-sm sm:rounded-lg"
                                >
                                    <div className="border-b border-gray-100 px-6 py-5">
                                        <div className="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                                            <div>
                                                <div className="flex flex-wrap items-center gap-3">
                                                    <h3 className="text-lg font-semibold text-gray-900">
                                                        {
                                                            counsellor.display_name
                                                        }
                                                    </h3>

                                                    <StatusBadge
                                                        active={
                                                            counsellor.is_user_active
                                                        }
                                                    />
                                                </div>

                                                <div className="mt-2 space-y-1 text-sm text-gray-500">
                                                    <p>
                                                        Email:{" "}
                                                        {formatValue(
                                                            counsellor.email,
                                                        )}
                                                    </p>
                                                    <p>
                                                        Phone:{" "}
                                                        {formatValue(
                                                            counsellor.phone,
                                                        )}
                                                    </p>
                                                </div>
                                            </div>

                                            <div className="grid grid-cols-2 gap-3 sm:grid-cols-4">
                                                <CountBadge
                                                    label="Rules"
                                                    value={
                                                        counsellor.rules_count
                                                    }
                                                />
                                                <CountBadge
                                                    label="Active"
                                                    value={
                                                        counsellor.active_rules_count
                                                    }
                                                />
                                                <CountBadge
                                                    label="Blocked"
                                                    value={
                                                        counsellor.blocked_slots_count
                                                    }
                                                />
                                                <CountBadge
                                                    label="Leave"
                                                    value={
                                                        counsellor.leave_days_count
                                                    }
                                                />
                                            </div>
                                        </div>
                                    </div>

                                    <div className="space-y-8 p-6">
                                        <section>
                                            <h4 className="text-base font-semibold text-gray-900">
                                                Recurring Availability
                                            </h4>

                                            {counsellor.rules.length === 0 ? (
                                                <div className="mt-3 rounded-lg border border-dashed border-gray-300 p-4">
                                                    <EmptyMessage>
                                                        No matching availability
                                                        rules.
                                                    </EmptyMessage>
                                                </div>
                                            ) : (
                                                <div className="mt-3 overflow-x-auto">
                                                    <table className="min-w-full divide-y divide-gray-200">
                                                        <thead className="bg-gray-50">
                                                            <tr>
                                                                <th className="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                                                    Day
                                                                </th>
                                                                <th className="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                                                    Time
                                                                </th>
                                                                <th className="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                                                    Mode
                                                                </th>
                                                                <th className="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                                                    Slot
                                                                </th>
                                                                <th className="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                                                    Status
                                                                </th>
                                                                <th className="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                                                    Breaks
                                                                </th>
                                                            </tr>
                                                        </thead>

                                                        <tbody className="divide-y divide-gray-200 bg-white">
                                                            {counsellor.rules.map(
                                                                (rule) => (
                                                                    <tr
                                                                        key={
                                                                            rule.id
                                                                        }
                                                                    >
                                                                        <td className="px-4 py-4 text-sm font-medium text-gray-900">
                                                                            {
                                                                                rule.day_name
                                                                            }
                                                                        </td>
                                                                        <td className="px-4 py-4 text-sm text-gray-600">
                                                                            {
                                                                                rule.start_time
                                                                            }{" "}
                                                                            -{" "}
                                                                            {
                                                                                rule.end_time
                                                                            }
                                                                        </td>
                                                                        <td className="px-4 py-4 text-sm text-gray-600">
                                                                            {formatValue(
                                                                                rule.mode,
                                                                            )}
                                                                        </td>
                                                                        <td className="px-4 py-4 text-sm text-gray-600">
                                                                            {
                                                                                rule.slot_duration_minutes
                                                                            }{" "}
                                                                            mins
                                                                            <span className="block text-xs text-gray-500">
                                                                                Buffer:{" "}
                                                                                {
                                                                                    rule.buffer_minutes
                                                                                }{" "}
                                                                                mins
                                                                            </span>
                                                                            <span className="block text-xs text-gray-500">
                                                                                Capacity:{" "}
                                                                                {
                                                                                    rule.capacity_per_slot
                                                                                }
                                                                            </span>
                                                                        </td>
                                                                        <td className="px-4 py-4">
                                                                            <StatusBadge
                                                                                active={
                                                                                    rule.is_active
                                                                                }
                                                                            />
                                                                        </td>
                                                                        <td className="px-4 py-4 text-sm text-gray-600">
                                                                            {rule
                                                                                .breaks
                                                                                .length ===
                                                                            0 ? (
                                                                                <span className="text-gray-400">
                                                                                    None
                                                                                </span>
                                                                            ) : (
                                                                                <div className="flex flex-wrap gap-2">
                                                                                    {rule.breaks.map(
                                                                                        (
                                                                                            availabilityBreak,
                                                                                        ) => (
                                                                                            <span
                                                                                                key={
                                                                                                    availabilityBreak.id
                                                                                                }
                                                                                                className="rounded-full bg-gray-100 px-3 py-1 text-xs text-gray-700"
                                                                                            >
                                                                                                {
                                                                                                    availabilityBreak.title
                                                                                                }

                                                                                                :{" "}
                                                                                                {
                                                                                                    availabilityBreak.start_time
                                                                                                }{" "}
                                                                                                -{" "}
                                                                                                {
                                                                                                    availabilityBreak.end_time
                                                                                                }
                                                                                            </span>
                                                                                        ),
                                                                                    )}
                                                                                </div>
                                                                            )}
                                                                        </td>
                                                                    </tr>
                                                                ),
                                                            )}
                                                        </tbody>
                                                    </table>
                                                </div>
                                            )}
                                        </section>

                                        <section className="grid gap-6 lg:grid-cols-2">
                                            <div>
                                                <h4 className="text-base font-semibold text-gray-900">
                                                    Upcoming Blocked Slots
                                                </h4>

                                                {counsellor.blocked_slots
                                                    .length === 0 ? (
                                                    <div className="mt-3 rounded-lg border border-dashed border-gray-300 p-4">
                                                        <EmptyMessage>
                                                            No upcoming blocked
                                                            slots.
                                                        </EmptyMessage>
                                                    </div>
                                                ) : (
                                                    <div className="mt-3 space-y-3">
                                                        {counsellor.blocked_slots.map(
                                                            (blockedSlot) => (
                                                                <div
                                                                    key={
                                                                        blockedSlot.id
                                                                    }
                                                                    className="rounded-lg border border-gray-200 p-4"
                                                                >
                                                                    <p className="font-semibold text-gray-900">
                                                                        {
                                                                            blockedSlot.blocked_date
                                                                        }
                                                                    </p>
                                                                    <p className="text-sm text-gray-600">
                                                                        <TimeRange
                                                                            start={
                                                                                blockedSlot.start_time
                                                                            }
                                                                            end={
                                                                                blockedSlot.end_time
                                                                            }
                                                                            fullDay={
                                                                                blockedSlot.is_full_day
                                                                            }
                                                                        />
                                                                    </p>
                                                                    <p className="mt-1 text-sm text-gray-500">
                                                                        Reason:{" "}
                                                                        {formatValue(
                                                                            blockedSlot.reason,
                                                                        )}
                                                                    </p>
                                                                </div>
                                                            ),
                                                        )}
                                                    </div>
                                                )}
                                            </div>

                                            <div>
                                                <h4 className="text-base font-semibold text-gray-900">
                                                    Upcoming Leave Days
                                                </h4>

                                                {counsellor.leave_days
                                                    .length === 0 ? (
                                                    <div className="mt-3 rounded-lg border border-dashed border-gray-300 p-4">
                                                        <EmptyMessage>
                                                            No upcoming leave
                                                            days.
                                                        </EmptyMessage>
                                                    </div>
                                                ) : (
                                                    <div className="mt-3 space-y-3">
                                                        {counsellor.leave_days.map(
                                                            (leaveDay) => (
                                                                <div
                                                                    key={
                                                                        leaveDay.id
                                                                    }
                                                                    className="rounded-lg border border-gray-200 p-4"
                                                                >
                                                                    <p className="font-semibold text-gray-900">
                                                                        {
                                                                            leaveDay.leave_date
                                                                        }
                                                                    </p>
                                                                    <p className="text-sm text-gray-600">
                                                                        <TimeRange
                                                                            start={
                                                                                leaveDay.start_time
                                                                            }
                                                                            end={
                                                                                leaveDay.end_time
                                                                            }
                                                                            fullDay={
                                                                                leaveDay.is_full_day
                                                                            }
                                                                        />
                                                                    </p>
                                                                    <p className="mt-1 text-sm text-gray-500">
                                                                        Reason:{" "}
                                                                        {formatValue(
                                                                            leaveDay.reason,
                                                                        )}
                                                                    </p>
                                                                </div>
                                                            ),
                                                        )}
                                                    </div>
                                                )}
                                            </div>
                                        </section>
                                    </div>
                                </div>
                            ))
                        )}
                    </div>

                    <Pagination links={counsellors.links} />
                </div>
            </div>
        </AdminLayout>
    );
}
