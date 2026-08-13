import InputError from "@/Components/InputError";
import InputLabel from "@/Components/InputLabel";
import PrimaryButton from "@/Components/PrimaryButton";
import SecondaryButton from "@/Components/SecondaryButton";
import TextInput from "@/Components/TextInput";
import CounsellorLayout from "@/Layouts/CounsellorLayout";
import { Head, router, useForm } from "@inertiajs/react";

function formatValue(value) {
    if (value === null || value === undefined || value === "") {
        return "Not provided";
    }

    return String(value)
        .replaceAll("_", " ")
        .replace(/\b\w/g, (character) => character.toUpperCase());
}

function BooleanBadge({
    active,
    trueLabel = "Active",
    falseLabel = "Inactive",
}) {
    return (
        <span
            className={`rounded-full px-3 py-1 text-xs font-semibold ${
                active
                    ? "bg-green-100 text-green-800"
                    : "bg-gray-100 text-gray-700"
            }`}
        >
            {active ? trueLabel : falseLabel}
        </span>
    );
}

function SectionCard({ title, description, children }) {
    return (
        <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg">
            <div className="border-b border-gray-100 px-6 py-4">
                <h3 className="text-lg font-semibold text-gray-900">{title}</h3>
                {description && (
                    <p className="mt-1 text-sm text-gray-500">{description}</p>
                )}
            </div>

            <div className="p-6">{children}</div>
        </div>
    );
}

function Field({ label, htmlFor, error, children }) {
    return (
        <div>
            <InputLabel htmlFor={htmlFor} value={label} />
            <div className="mt-1">{children}</div>
            <InputError message={error} className="mt-2" />
        </div>
    );
}

export default function Index({
    counsellorProfile,
    rules,
    blockedSlots,
    leaveDays,
    options,
}) {
    const ruleForm = useForm({
        day_of_week: "",
        start_time: "",
        end_time: "",
        mode: "both",
        slot_duration_minutes: 60,
        buffer_minutes: 0,
        capacity_per_slot: 1,
        timezone: "Asia/Colombo",
        effective_from: "",
        effective_until: "",
        is_active: true,
        notes: "",
    });

    const breakForm = useForm({
        availability_rule_id: "",
        title: "Break",
        start_time: "",
        end_time: "",
        is_active: true,
    });

    const blockedSlotForm = useForm({
        blocked_date: "",
        is_full_day: false,
        start_time: "",
        end_time: "",
        reason: "",
        notes: "",
    });

    const leaveDayForm = useForm({
        leave_date: "",
        is_full_day: true,
        start_time: "",
        end_time: "",
        reason: "",
        notes: "",
    });

    const submitRule = (event) => {
        event.preventDefault();

        ruleForm.post(route("counsellor.availability.rules.store"), {
            preserveScroll: true,
            onSuccess: () => {
                ruleForm.reset(
                    "day_of_week",
                    "start_time",
                    "end_time",
                    "effective_from",
                    "effective_until",
                    "notes",
                );
            },
        });
    };

    const submitBreak = (event) => {
        event.preventDefault();

        if (!breakForm.data.availability_rule_id) {
            breakForm.setError(
                "availability_rule_id",
                "Please select an availability rule.",
            );

            return;
        }

        breakForm.post(
            route(
                "counsellor.availability.breaks.store",
                breakForm.data.availability_rule_id,
            ),
            {
                preserveScroll: true,
                onSuccess: () => {
                    breakForm.reset(
                        "availability_rule_id",
                        "title",
                        "start_time",
                        "end_time",
                    );
                    breakForm.setData("title", "Break");
                    breakForm.setData("is_active", true);
                },
            },
        );
    };

    const submitBlockedSlot = (event) => {
        event.preventDefault();

        blockedSlotForm.post(
            route("counsellor.availability.blocked-slots.store"),
            {
                preserveScroll: true,
                onSuccess: () => {
                    blockedSlotForm.reset(
                        "blocked_date",
                        "start_time",
                        "end_time",
                        "reason",
                        "notes",
                    );
                    blockedSlotForm.setData("is_full_day", false);
                },
            },
        );
    };

    const submitLeaveDay = (event) => {
        event.preventDefault();

        leaveDayForm.post(route("counsellor.availability.leave-days.store"), {
            preserveScroll: true,
            onSuccess: () => {
                leaveDayForm.reset(
                    "leave_date",
                    "start_time",
                    "end_time",
                    "reason",
                    "notes",
                );
                leaveDayForm.setData("is_full_day", true);
            },
        });
    };

    const deleteRule = (rule) => {
        const confirmed = window.confirm(
            `Delete availability for ${rule.day_name} ${rule.start_time} - ${rule.end_time}?`,
        );

        if (!confirmed) {
            return;
        }

        router.delete(route("counsellor.availability.rules.destroy", rule.id), {
            preserveScroll: true,
        });
    };

    const deleteBreak = (availabilityBreak) => {
        const confirmed = window.confirm(
            `Delete break "${availabilityBreak.title}"?`,
        );

        if (!confirmed) {
            return;
        }

        router.delete(
            route(
                "counsellor.availability.breaks.destroy",
                availabilityBreak.id,
            ),
            {
                preserveScroll: true,
            },
        );
    };

    const deleteBlockedSlot = (blockedSlot) => {
        const confirmed = window.confirm(
            `Delete blocked slot on ${blockedSlot.blocked_date}?`,
        );

        if (!confirmed) {
            return;
        }

        router.delete(
            route(
                "counsellor.availability.blocked-slots.destroy",
                blockedSlot.id,
            ),
            {
                preserveScroll: true,
            },
        );
    };

    const deleteLeaveDay = (leaveDay) => {
        const confirmed = window.confirm(
            `Delete leave day on ${leaveDay.leave_date}?`,
        );

        if (!confirmed) {
            return;
        }

        router.delete(
            route("counsellor.availability.leave-days.destroy", leaveDay.id),
            {
                preserveScroll: true,
            },
        );
    };

    return (
        <CounsellorLayout
            header={
                <div>
                    <h2 className="text-xl font-semibold leading-tight text-gray-800">
                        My Availability
                    </h2>
                    <p className="mt-1 text-sm text-gray-500">
                        Manage recurring availability, breaks, blocked slots,
                        and leave days.
                    </p>
                </div>
            }
        >
            <Head title="My Availability" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                    <div className="rounded-lg border border-indigo-100 bg-indigo-50 p-4">
                        <p className="text-sm text-indigo-900">
                            Availability belongs to{" "}
                            <span className="font-semibold">
                                {counsellorProfile?.display_name ??
                                    "your counsellor profile"}
                            </span>
                            . Avoid overlapping rules, breaks, blocked slots,
                            and leave days. The backend now validates that,
                            because humans cannot be trusted with calendars.
                        </p>
                    </div>

                    <SectionCard
                        title="Recurring Availability"
                        description="Add weekly availability rules such as Monday 09:00 to 17:00."
                    >
                        <form onSubmit={submitRule} className="space-y-6">
                            <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                                <Field
                                    label="Day"
                                    htmlFor="day_of_week"
                                    error={ruleForm.errors.day_of_week}
                                >
                                    <select
                                        id="day_of_week"
                                        value={ruleForm.data.day_of_week}
                                        onChange={(event) =>
                                            ruleForm.setData(
                                                "day_of_week",
                                                event.target.value,
                                            )
                                        }
                                        className="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    >
                                        <option value="">Select day</option>
                                        {options.days.map((day) => (
                                            <option
                                                key={day.value}
                                                value={day.value}
                                            >
                                                {day.label}
                                            </option>
                                        ))}
                                    </select>
                                </Field>

                                <Field
                                    label="Start time"
                                    htmlFor="rule_start_time"
                                    error={ruleForm.errors.start_time}
                                >
                                    <TextInput
                                        id="rule_start_time"
                                        type="time"
                                        value={ruleForm.data.start_time}
                                        className="block w-full"
                                        onChange={(event) =>
                                            ruleForm.setData(
                                                "start_time",
                                                event.target.value,
                                            )
                                        }
                                    />
                                </Field>

                                <Field
                                    label="End time"
                                    htmlFor="rule_end_time"
                                    error={ruleForm.errors.end_time}
                                >
                                    <TextInput
                                        id="rule_end_time"
                                        type="time"
                                        value={ruleForm.data.end_time}
                                        className="block w-full"
                                        onChange={(event) =>
                                            ruleForm.setData(
                                                "end_time",
                                                event.target.value,
                                            )
                                        }
                                    />
                                </Field>

                                <Field
                                    label="Mode"
                                    htmlFor="mode"
                                    error={ruleForm.errors.mode}
                                >
                                    <select
                                        id="mode"
                                        value={ruleForm.data.mode}
                                        onChange={(event) =>
                                            ruleForm.setData(
                                                "mode",
                                                event.target.value,
                                            )
                                        }
                                        className="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    >
                                        {options.modes.map((mode) => (
                                            <option
                                                key={mode.value}
                                                value={mode.value}
                                            >
                                                {mode.label}
                                            </option>
                                        ))}
                                    </select>
                                </Field>
                            </div>

                            <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                                <Field
                                    label="Slot duration"
                                    htmlFor="slot_duration_minutes"
                                    error={
                                        ruleForm.errors.slot_duration_minutes
                                    }
                                >
                                    <select
                                        id="slot_duration_minutes"
                                        value={
                                            ruleForm.data.slot_duration_minutes
                                        }
                                        onChange={(event) =>
                                            ruleForm.setData(
                                                "slot_duration_minutes",
                                                Number(event.target.value),
                                            )
                                        }
                                        className="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    >
                                        {options.slotDurations.map(
                                            (duration) => (
                                                <option
                                                    key={duration}
                                                    value={duration}
                                                >
                                                    {duration} minutes
                                                </option>
                                            ),
                                        )}
                                    </select>
                                </Field>

                                <Field
                                    label="Buffer minutes"
                                    htmlFor="buffer_minutes"
                                    error={ruleForm.errors.buffer_minutes}
                                >
                                    <TextInput
                                        id="buffer_minutes"
                                        type="number"
                                        min="0"
                                        max="120"
                                        value={ruleForm.data.buffer_minutes}
                                        className="block w-full"
                                        onChange={(event) =>
                                            ruleForm.setData(
                                                "buffer_minutes",
                                                Number(event.target.value),
                                            )
                                        }
                                    />
                                </Field>

                                <Field
                                    label="Capacity per slot"
                                    htmlFor="capacity_per_slot"
                                    error={ruleForm.errors.capacity_per_slot}
                                >
                                    <TextInput
                                        id="capacity_per_slot"
                                        type="number"
                                        min="1"
                                        max="20"
                                        value={ruleForm.data.capacity_per_slot}
                                        className="block w-full"
                                        onChange={(event) =>
                                            ruleForm.setData(
                                                "capacity_per_slot",
                                                Number(event.target.value),
                                            )
                                        }
                                    />
                                </Field>

                                <Field
                                    label="Timezone"
                                    htmlFor="timezone"
                                    error={ruleForm.errors.timezone}
                                >
                                    <select
                                        id="timezone"
                                        value={ruleForm.data.timezone}
                                        onChange={(event) =>
                                            ruleForm.setData(
                                                "timezone",
                                                event.target.value,
                                            )
                                        }
                                        className="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    >
                                        {options.timezones.map((timezone) => (
                                            <option
                                                key={timezone.value}
                                                value={timezone.value}
                                            >
                                                {timezone.label}
                                            </option>
                                        ))}
                                    </select>
                                </Field>
                            </div>

                            <div className="grid gap-4 md:grid-cols-2">
                                <Field
                                    label="Effective from"
                                    htmlFor="effective_from"
                                    error={ruleForm.errors.effective_from}
                                >
                                    <TextInput
                                        id="effective_from"
                                        type="date"
                                        value={ruleForm.data.effective_from}
                                        className="block w-full"
                                        onChange={(event) =>
                                            ruleForm.setData(
                                                "effective_from",
                                                event.target.value,
                                            )
                                        }
                                    />
                                </Field>

                                <Field
                                    label="Effective until"
                                    htmlFor="effective_until"
                                    error={ruleForm.errors.effective_until}
                                >
                                    <TextInput
                                        id="effective_until"
                                        type="date"
                                        value={ruleForm.data.effective_until}
                                        className="block w-full"
                                        onChange={(event) =>
                                            ruleForm.setData(
                                                "effective_until",
                                                event.target.value,
                                            )
                                        }
                                    />
                                </Field>
                            </div>

                            <Field
                                label="Notes"
                                htmlFor="rule_notes"
                                error={ruleForm.errors.notes}
                            >
                                <textarea
                                    id="rule_notes"
                                    value={ruleForm.data.notes}
                                    onChange={(event) =>
                                        ruleForm.setData(
                                            "notes",
                                            event.target.value,
                                        )
                                    }
                                    rows="3"
                                    className="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                />
                            </Field>

                            <label className="flex items-center gap-2 text-sm text-gray-700">
                                <input
                                    type="checkbox"
                                    checked={ruleForm.data.is_active}
                                    onChange={(event) =>
                                        ruleForm.setData(
                                            "is_active",
                                            event.target.checked,
                                        )
                                    }
                                    className="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                />
                                Active
                            </label>

                            <div className="flex justify-end">
                                <PrimaryButton disabled={ruleForm.processing}>
                                    Add availability
                                </PrimaryButton>
                            </div>
                        </form>
                    </SectionCard>

                    <SectionCard
                        title="Current Availability Rules"
                        description="Recurring weekly availability with active breaks."
                    >
                        {rules.length === 0 ? (
                            <p className="text-sm text-gray-500">
                                No availability rules added yet.
                            </p>
                        ) : (
                            <div className="space-y-4">
                                {rules.map((rule) => (
                                    <div
                                        key={rule.id}
                                        className="rounded-lg border border-gray-200 p-4"
                                    >
                                        <div className="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                                            <div>
                                                <div className="flex flex-wrap items-center gap-2">
                                                    <h4 className="text-base font-semibold text-gray-900">
                                                        {rule.day_name}:{" "}
                                                        {rule.start_time} -{" "}
                                                        {rule.end_time}
                                                    </h4>
                                                    <BooleanBadge
                                                        active={rule.is_active}
                                                    />
                                                </div>

                                                <div className="mt-2 grid gap-2 text-sm text-gray-600 md:grid-cols-2 lg:grid-cols-4">
                                                    <p>
                                                        Mode:{" "}
                                                        {formatValue(rule.mode)}
                                                    </p>
                                                    <p>
                                                        Slot:{" "}
                                                        {
                                                            rule.slot_duration_minutes
                                                        }{" "}
                                                        mins
                                                    </p>
                                                    <p>
                                                        Buffer:{" "}
                                                        {rule.buffer_minutes}{" "}
                                                        mins
                                                    </p>
                                                    <p>
                                                        Capacity:{" "}
                                                        {rule.capacity_per_slot}
                                                    </p>
                                                </div>

                                                <p className="mt-2 text-sm text-gray-500">
                                                    Effective:{" "}
                                                    {formatValue(
                                                        rule.effective_from,
                                                    )}{" "}
                                                    to{" "}
                                                    {formatValue(
                                                        rule.effective_until,
                                                    )}
                                                </p>

                                                {rule.notes && (
                                                    <p className="mt-2 text-sm text-gray-500">
                                                        {rule.notes}
                                                    </p>
                                                )}
                                            </div>

                                            <SecondaryButton
                                                type="button"
                                                onClick={() => deleteRule(rule)}
                                            >
                                                Delete
                                            </SecondaryButton>
                                        </div>

                                        <div className="mt-4 border-t border-gray-100 pt-4">
                                            <h5 className="text-sm font-semibold text-gray-800">
                                                Breaks
                                            </h5>

                                            {rule.breaks.length === 0 ? (
                                                <p className="mt-1 text-sm text-gray-500">
                                                    No active breaks.
                                                </p>
                                            ) : (
                                                <div className="mt-2 flex flex-wrap gap-2">
                                                    {rule.breaks.map(
                                                        (availabilityBreak) => (
                                                            <span
                                                                key={
                                                                    availabilityBreak.id
                                                                }
                                                                className="inline-flex items-center gap-2 rounded-full bg-gray-100 px-3 py-1 text-xs text-gray-700"
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
                                                                <button
                                                                    type="button"
                                                                    onClick={() =>
                                                                        deleteBreak(
                                                                            availabilityBreak,
                                                                        )
                                                                    }
                                                                    className="font-semibold text-red-600 hover:text-red-800"
                                                                >
                                                                    Remove
                                                                </button>
                                                            </span>
                                                        ),
                                                    )}
                                                </div>
                                            )}
                                        </div>
                                    </div>
                                ))}
                            </div>
                        )}
                    </SectionCard>

                    <SectionCard
                        title="Add Break"
                        description="Breaks must stay inside the selected availability rule."
                    >
                        <form onSubmit={submitBreak} className="space-y-6">
                            <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                                <Field
                                    label="Availability rule"
                                    htmlFor="availability_rule_id"
                                    error={
                                        breakForm.errors.availability_rule_id ||
                                        breakForm.errors
                                            .counsellor_availability_rule_id
                                    }
                                >
                                    <select
                                        id="availability_rule_id"
                                        value={
                                            breakForm.data.availability_rule_id
                                        }
                                        onChange={(event) =>
                                            breakForm.setData(
                                                "availability_rule_id",
                                                event.target.value,
                                            )
                                        }
                                        className="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    >
                                        <option value="">Select rule</option>
                                        {rules.map((rule) => (
                                            <option
                                                key={rule.id}
                                                value={rule.id}
                                            >
                                                {rule.day_name}{" "}
                                                {rule.start_time} -{" "}
                                                {rule.end_time}
                                            </option>
                                        ))}
                                    </select>
                                </Field>

                                <Field
                                    label="Title"
                                    htmlFor="break_title"
                                    error={breakForm.errors.title}
                                >
                                    <TextInput
                                        id="break_title"
                                        value={breakForm.data.title}
                                        className="block w-full"
                                        onChange={(event) =>
                                            breakForm.setData(
                                                "title",
                                                event.target.value,
                                            )
                                        }
                                    />
                                </Field>

                                <Field
                                    label="Start time"
                                    htmlFor="break_start_time"
                                    error={breakForm.errors.start_time}
                                >
                                    <TextInput
                                        id="break_start_time"
                                        type="time"
                                        value={breakForm.data.start_time}
                                        className="block w-full"
                                        onChange={(event) =>
                                            breakForm.setData(
                                                "start_time",
                                                event.target.value,
                                            )
                                        }
                                    />
                                </Field>

                                <Field
                                    label="End time"
                                    htmlFor="break_end_time"
                                    error={breakForm.errors.end_time}
                                >
                                    <TextInput
                                        id="break_end_time"
                                        type="time"
                                        value={breakForm.data.end_time}
                                        className="block w-full"
                                        onChange={(event) =>
                                            breakForm.setData(
                                                "end_time",
                                                event.target.value,
                                            )
                                        }
                                    />
                                </Field>
                            </div>

                            <label className="flex items-center gap-2 text-sm text-gray-700">
                                <input
                                    type="checkbox"
                                    checked={breakForm.data.is_active}
                                    onChange={(event) =>
                                        breakForm.setData(
                                            "is_active",
                                            event.target.checked,
                                        )
                                    }
                                    className="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                />
                                Active
                            </label>

                            <div className="flex justify-end">
                                <PrimaryButton disabled={breakForm.processing}>
                                    Add break
                                </PrimaryButton>
                            </div>
                        </form>
                    </SectionCard>

                    <SectionCard
                        title="Blocked Slots"
                        description="Use blocked slots for one-off unavailable times."
                    >
                        <form
                            onSubmit={submitBlockedSlot}
                            className="space-y-6"
                        >
                            <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                                <Field
                                    label="Blocked date"
                                    htmlFor="blocked_date"
                                    error={blockedSlotForm.errors.blocked_date}
                                >
                                    <TextInput
                                        id="blocked_date"
                                        type="date"
                                        value={
                                            blockedSlotForm.data.blocked_date
                                        }
                                        className="block w-full"
                                        onChange={(event) =>
                                            blockedSlotForm.setData(
                                                "blocked_date",
                                                event.target.value,
                                            )
                                        }
                                    />
                                </Field>

                                <Field
                                    label="Start time"
                                    htmlFor="blocked_start_time"
                                    error={blockedSlotForm.errors.start_time}
                                >
                                    <TextInput
                                        id="blocked_start_time"
                                        type="time"
                                        value={blockedSlotForm.data.start_time}
                                        disabled={
                                            blockedSlotForm.data.is_full_day
                                        }
                                        className="block w-full"
                                        onChange={(event) =>
                                            blockedSlotForm.setData(
                                                "start_time",
                                                event.target.value,
                                            )
                                        }
                                    />
                                </Field>

                                <Field
                                    label="End time"
                                    htmlFor="blocked_end_time"
                                    error={blockedSlotForm.errors.end_time}
                                >
                                    <TextInput
                                        id="blocked_end_time"
                                        type="time"
                                        value={blockedSlotForm.data.end_time}
                                        disabled={
                                            blockedSlotForm.data.is_full_day
                                        }
                                        className="block w-full"
                                        onChange={(event) =>
                                            blockedSlotForm.setData(
                                                "end_time",
                                                event.target.value,
                                            )
                                        }
                                    />
                                </Field>

                                <Field
                                    label="Reason"
                                    htmlFor="blocked_reason"
                                    error={blockedSlotForm.errors.reason}
                                >
                                    <TextInput
                                        id="blocked_reason"
                                        value={blockedSlotForm.data.reason}
                                        className="block w-full"
                                        onChange={(event) =>
                                            blockedSlotForm.setData(
                                                "reason",
                                                event.target.value,
                                            )
                                        }
                                    />
                                </Field>
                            </div>

                            <label className="flex items-center gap-2 text-sm text-gray-700">
                                <input
                                    type="checkbox"
                                    checked={blockedSlotForm.data.is_full_day}
                                    onChange={(event) =>
                                        blockedSlotForm.setData(
                                            "is_full_day",
                                            event.target.checked,
                                        )
                                    }
                                    className="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                />
                                Full day block
                            </label>

                            <Field
                                label="Notes"
                                htmlFor="blocked_notes"
                                error={blockedSlotForm.errors.notes}
                            >
                                <textarea
                                    id="blocked_notes"
                                    value={blockedSlotForm.data.notes}
                                    onChange={(event) =>
                                        blockedSlotForm.setData(
                                            "notes",
                                            event.target.value,
                                        )
                                    }
                                    rows="3"
                                    className="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                />
                            </Field>

                            <div className="flex justify-end">
                                <PrimaryButton
                                    disabled={blockedSlotForm.processing}
                                >
                                    Add blocked slot
                                </PrimaryButton>
                            </div>
                        </form>

                        <div className="mt-6 border-t border-gray-100 pt-6">
                            {blockedSlots.length === 0 ? (
                                <p className="text-sm text-gray-500">
                                    No upcoming blocked slots.
                                </p>
                            ) : (
                                <div className="space-y-3">
                                    {blockedSlots.map((blockedSlot) => (
                                        <div
                                            key={blockedSlot.id}
                                            className="flex flex-col gap-3 rounded-lg border border-gray-200 p-4 md:flex-row md:items-center md:justify-between"
                                        >
                                            <div>
                                                <p className="font-semibold text-gray-900">
                                                    {blockedSlot.blocked_date}
                                                </p>
                                                <p className="text-sm text-gray-600">
                                                    {blockedSlot.is_full_day
                                                        ? "Full day"
                                                        : `${blockedSlot.start_time} - ${blockedSlot.end_time}`}
                                                </p>
                                                <p className="text-sm text-gray-500">
                                                    {formatValue(
                                                        blockedSlot.reason,
                                                    )}
                                                </p>
                                            </div>

                                            <SecondaryButton
                                                type="button"
                                                onClick={() =>
                                                    deleteBlockedSlot(
                                                        blockedSlot,
                                                    )
                                                }
                                            >
                                                Delete
                                            </SecondaryButton>
                                        </div>
                                    ))}
                                </div>
                            )}
                        </div>
                    </SectionCard>

                    <SectionCard
                        title="Leave Days"
                        description="Use leave days for full or partial leave records."
                    >
                        <form onSubmit={submitLeaveDay} className="space-y-6">
                            <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                                <Field
                                    label="Leave date"
                                    htmlFor="leave_date"
                                    error={leaveDayForm.errors.leave_date}
                                >
                                    <TextInput
                                        id="leave_date"
                                        type="date"
                                        value={leaveDayForm.data.leave_date}
                                        className="block w-full"
                                        onChange={(event) =>
                                            leaveDayForm.setData(
                                                "leave_date",
                                                event.target.value,
                                            )
                                        }
                                    />
                                </Field>

                                <Field
                                    label="Start time"
                                    htmlFor="leave_start_time"
                                    error={leaveDayForm.errors.start_time}
                                >
                                    <TextInput
                                        id="leave_start_time"
                                        type="time"
                                        value={leaveDayForm.data.start_time}
                                        disabled={leaveDayForm.data.is_full_day}
                                        className="block w-full"
                                        onChange={(event) =>
                                            leaveDayForm.setData(
                                                "start_time",
                                                event.target.value,
                                            )
                                        }
                                    />
                                </Field>

                                <Field
                                    label="End time"
                                    htmlFor="leave_end_time"
                                    error={leaveDayForm.errors.end_time}
                                >
                                    <TextInput
                                        id="leave_end_time"
                                        type="time"
                                        value={leaveDayForm.data.end_time}
                                        disabled={leaveDayForm.data.is_full_day}
                                        className="block w-full"
                                        onChange={(event) =>
                                            leaveDayForm.setData(
                                                "end_time",
                                                event.target.value,
                                            )
                                        }
                                    />
                                </Field>

                                <Field
                                    label="Reason"
                                    htmlFor="leave_reason"
                                    error={leaveDayForm.errors.reason}
                                >
                                    <TextInput
                                        id="leave_reason"
                                        value={leaveDayForm.data.reason}
                                        className="block w-full"
                                        onChange={(event) =>
                                            leaveDayForm.setData(
                                                "reason",
                                                event.target.value,
                                            )
                                        }
                                    />
                                </Field>
                            </div>

                            <label className="flex items-center gap-2 text-sm text-gray-700">
                                <input
                                    type="checkbox"
                                    checked={leaveDayForm.data.is_full_day}
                                    onChange={(event) =>
                                        leaveDayForm.setData(
                                            "is_full_day",
                                            event.target.checked,
                                        )
                                    }
                                    className="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                />
                                Full day leave
                            </label>

                            <Field
                                label="Notes"
                                htmlFor="leave_notes"
                                error={leaveDayForm.errors.notes}
                            >
                                <textarea
                                    id="leave_notes"
                                    value={leaveDayForm.data.notes}
                                    onChange={(event) =>
                                        leaveDayForm.setData(
                                            "notes",
                                            event.target.value,
                                        )
                                    }
                                    rows="3"
                                    className="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                />
                            </Field>

                            <div className="flex justify-end">
                                <PrimaryButton
                                    disabled={leaveDayForm.processing}
                                >
                                    Add leave day
                                </PrimaryButton>
                            </div>
                        </form>

                        <div className="mt-6 border-t border-gray-100 pt-6">
                            {leaveDays.length === 0 ? (
                                <p className="text-sm text-gray-500">
                                    No upcoming leave days.
                                </p>
                            ) : (
                                <div className="space-y-3">
                                    {leaveDays.map((leaveDay) => (
                                        <div
                                            key={leaveDay.id}
                                            className="flex flex-col gap-3 rounded-lg border border-gray-200 p-4 md:flex-row md:items-center md:justify-between"
                                        >
                                            <div>
                                                <p className="font-semibold text-gray-900">
                                                    {leaveDay.leave_date}
                                                </p>
                                                <p className="text-sm text-gray-600">
                                                    {leaveDay.is_full_day
                                                        ? "Full day"
                                                        : `${leaveDay.start_time} - ${leaveDay.end_time}`}
                                                </p>
                                                <p className="text-sm text-gray-500">
                                                    {formatValue(
                                                        leaveDay.reason,
                                                    )}
                                                </p>
                                            </div>

                                            <SecondaryButton
                                                type="button"
                                                onClick={() =>
                                                    deleteLeaveDay(leaveDay)
                                                }
                                            >
                                                Delete
                                            </SecondaryButton>
                                        </div>
                                    ))}
                                </div>
                            )}
                        </div>
                    </SectionCard>
                </div>
            </div>
        </CounsellorLayout>
    );
}
