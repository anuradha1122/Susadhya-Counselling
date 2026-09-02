import InputError from "@/Components/InputError";
import PrimaryButton from "@/Components/PrimaryButton";
import SecondaryButton from "@/Components/SecondaryButton";
import ClientLayout from "@/Layouts/ClientLayout";
import { Head, useForm } from "@inertiajs/react";

function formatValue(value) {
    if (value === null || value === undefined || value === "") {
        return "Not provided";
    }

    return String(value)
        .replaceAll("_", " ")
        .replace(/\b\w/g, (character) => character.toUpperCase());
}

function statusClasses(status) {
    const classes = {
        draft: "bg-gray-100 text-gray-700",
        submitted: "bg-amber-50 text-amber-700",
        reviewed: "bg-green-50 text-green-700",
        requires_follow_up: "bg-blue-50 text-blue-700",
    };

    return classes[status] ?? "bg-gray-100 text-gray-700";
}

function riskClasses(riskLevel) {
    const classes = {
        low: "bg-green-50 text-green-700",
        moderate: "bg-amber-50 text-amber-700",
        high: "bg-red-50 text-red-700",
        urgent: "bg-rose-100 text-rose-800",
    };

    return classes[riskLevel] ?? "bg-gray-100 text-gray-700";
}

function Badge({ value, type = "status" }) {
    const classes = type === "risk" ? riskClasses(value) : statusClasses(value);

    return (
        <span className={`rounded-full px-3 py-1 text-xs font-semibold ${classes}`}>
            {formatValue(value)}
        </span>
    );
}

function CheckboxField({ id, label, checked, onChange, disabled, error }) {
    return (
        <div className="rounded-lg border border-gray-100 bg-gray-50 p-4">
            <label htmlFor={id} className="flex items-start gap-3">
                <input
                    id={id}
                    type="checkbox"
                    checked={Boolean(checked)}
                    onChange={(event) => onChange(event.target.checked)}
                    disabled={disabled}
                    className="mt-1 rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500 disabled:opacity-60"
                />

                <span className="text-sm text-gray-700">{label}</span>
            </label>

            <InputError message={error} className="mt-2" />
        </div>
    );
}

export default function Edit({
    intake,
    screeningQuestions,
    screeningScoreOptions,
    preferredSessionModes,
}) {
    const initialAnswers = screeningQuestions.reduce((answers, question) => {
        answers[question.key] = intake.screening_answers?.[question.key] ?? {
            answer_score: "",
            answer_notes: "",
        };

        return answers;
    }, {});

    const { data, setData, patch, post, processing, errors } = useForm({
        presenting_concerns: intake.presenting_concerns ?? "",
        current_symptoms: intake.current_symptoms ?? "",
        counselling_goals: intake.counselling_goals ?? "",
        preferred_session_mode: intake.preferred_session_mode ?? "either",
        previous_counselling: Boolean(intake.previous_counselling),
        previous_counselling_notes: intake.previous_counselling_notes ?? "",
        medication_notes: intake.medication_notes ?? "",
        emergency_contact_name: intake.emergency_contact_name ?? "",
        emergency_contact_phone: intake.emergency_contact_phone ?? "",
        emergency_contact_relationship:
            intake.emergency_contact_relationship ?? "",
        consent_terms_accepted: Boolean(intake.consent_terms_accepted),
        consent_privacy_accepted: Boolean(intake.consent_privacy_accepted),
        consent_telehealth_accepted: Boolean(
            intake.consent_telehealth_accepted,
        ),
        consent_data_processing_accepted: Boolean(
            intake.consent_data_processing_accepted,
        ),
        screening_answers: initialAnswers,
    });

    const disabled = !intake.can_edit;

    const updateScreeningAnswer = (questionKey, field, value) => {
        setData("screening_answers", {
            ...data.screening_answers,
            [questionKey]: {
                ...(data.screening_answers[questionKey] ?? {}),
                [field]: value,
            },
        });
    };

    const saveDraft = (event) => {
        event.preventDefault();

        patch(route("client.intake.update"), {
            preserveScroll: true,
        });
    };

    const submitIntake = (event) => {
        event.preventDefault();

        post(route("client.intake.submit"), {
            preserveScroll: true,
        });
    };

    return (
        <ClientLayout
            header={
                <div>
                    <h2 className="text-xl font-semibold leading-tight text-gray-800">
                        Intake, Consent & Screening
                    </h2>
                    <p className="mt-1 text-sm text-gray-500">
                        Complete your intake details before counselling
                        sessions.
                    </p>
                </div>
            }
        >
            <Head title="Intake, Consent & Screening" />

            <div className="py-12">
                <div className="mx-auto max-w-5xl space-y-6 sm:px-6 lg:px-8">
                    <div className="rounded-lg border border-indigo-100 bg-indigo-50 p-5">
                        <div className="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                            <div>
                                <h3 className="text-base font-semibold text-indigo-950">
                                    Intake Status
                                </h3>
                                <p className="mt-1 text-sm text-indigo-900">
                                    Save your draft first, then submit when all
                                    consent and screening items are complete.
                                </p>
                            </div>

                            <div className="flex flex-wrap gap-2">
                                <Badge value={intake.status} />
                                <Badge value={intake.risk_level} type="risk" />
                            </div>
                        </div>

                        {intake.risk_notes && (
                            <p className="mt-3 text-sm text-indigo-900">
                                Risk note: {intake.risk_notes}
                            </p>
                        )}

                        {intake.reviewer_notes && (
                            <p className="mt-3 rounded-lg bg-white p-3 text-sm text-gray-700">
                                Reviewer note: {intake.reviewer_notes}
                            </p>
                        )}

                        {!intake.can_edit && (
                            <p className="mt-3 rounded-lg bg-white p-3 text-sm text-gray-700">
                                This intake has been submitted and cannot be
                                edited unless follow-up is requested. Forms,
                                finally doing their one job.
                            </p>
                        )}
                    </div>

                    <form onSubmit={saveDraft} className="space-y-6">
                        <div className="overflow-hidden bg-white p-6 shadow-sm sm:rounded-lg">
                            <h3 className="text-lg font-semibold text-gray-900">
                                Main Concerns
                            </h3>

                            <div className="mt-5 space-y-5">
                                <div>
                                    <label
                                        htmlFor="presenting_concerns"
                                        className="text-sm font-medium text-gray-700"
                                    >
                                        Main concern
                                    </label>

                                    <textarea
                                        id="presenting_concerns"
                                        rows="4"
                                        value={data.presenting_concerns}
                                        disabled={disabled}
                                        onChange={(event) =>
                                            setData(
                                                "presenting_concerns",
                                                event.target.value,
                                            )
                                        }
                                        className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 disabled:bg-gray-100"
                                    />

                                    <InputError
                                        message={errors.presenting_concerns}
                                        className="mt-2"
                                    />
                                </div>

                                <div>
                                    <label
                                        htmlFor="current_symptoms"
                                        className="text-sm font-medium text-gray-700"
                                    >
                                        Current symptoms or difficulties
                                    </label>

                                    <textarea
                                        id="current_symptoms"
                                        rows="4"
                                        value={data.current_symptoms}
                                        disabled={disabled}
                                        onChange={(event) =>
                                            setData(
                                                "current_symptoms",
                                                event.target.value,
                                            )
                                        }
                                        className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 disabled:bg-gray-100"
                                    />

                                    <InputError
                                        message={errors.current_symptoms}
                                        className="mt-2"
                                    />
                                </div>

                                <div>
                                    <label
                                        htmlFor="counselling_goals"
                                        className="text-sm font-medium text-gray-700"
                                    >
                                        Counselling goals
                                    </label>

                                    <textarea
                                        id="counselling_goals"
                                        rows="4"
                                        value={data.counselling_goals}
                                        disabled={disabled}
                                        onChange={(event) =>
                                            setData(
                                                "counselling_goals",
                                                event.target.value,
                                            )
                                        }
                                        className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 disabled:bg-gray-100"
                                    />

                                    <InputError
                                        message={errors.counselling_goals}
                                        className="mt-2"
                                    />
                                </div>

                                <div>
                                    <label
                                        htmlFor="preferred_session_mode"
                                        className="text-sm font-medium text-gray-700"
                                    >
                                        Preferred session mode
                                    </label>

                                    <select
                                        id="preferred_session_mode"
                                        value={data.preferred_session_mode}
                                        disabled={disabled}
                                        onChange={(event) =>
                                            setData(
                                                "preferred_session_mode",
                                                event.target.value,
                                            )
                                        }
                                        className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 disabled:bg-gray-100"
                                    >
                                        {preferredSessionModes.map((mode) => (
                                            <option
                                                key={mode.value}
                                                value={mode.value}
                                            >
                                                {mode.label}
                                            </option>
                                        ))}
                                    </select>

                                    <InputError
                                        message={errors.preferred_session_mode}
                                        className="mt-2"
                                    />
                                </div>
                            </div>
                        </div>

                        <div className="overflow-hidden bg-white p-6 shadow-sm sm:rounded-lg">
                            <h3 className="text-lg font-semibold text-gray-900">
                                Background Details
                            </h3>

                            <div className="mt-5 space-y-5">
                                <CheckboxField
                                    id="previous_counselling"
                                    label="I have attended counselling or psychological support before."
                                    checked={data.previous_counselling}
                                    disabled={disabled}
                                    onChange={(checked) =>
                                        setData(
                                            "previous_counselling",
                                            checked,
                                        )
                                    }
                                    error={errors.previous_counselling}
                                />

                                <div>
                                    <label
                                        htmlFor="previous_counselling_notes"
                                        className="text-sm font-medium text-gray-700"
                                    >
                                        Previous counselling notes
                                    </label>

                                    <textarea
                                        id="previous_counselling_notes"
                                        rows="3"
                                        value={data.previous_counselling_notes}
                                        disabled={disabled}
                                        onChange={(event) =>
                                            setData(
                                                "previous_counselling_notes",
                                                event.target.value,
                                            )
                                        }
                                        className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 disabled:bg-gray-100"
                                    />

                                    <InputError
                                        message={
                                            errors.previous_counselling_notes
                                        }
                                        className="mt-2"
                                    />
                                </div>

                                <div>
                                    <label
                                        htmlFor="medication_notes"
                                        className="text-sm font-medium text-gray-700"
                                    >
                                        Medication or medical notes
                                    </label>

                                    <textarea
                                        id="medication_notes"
                                        rows="3"
                                        value={data.medication_notes}
                                        disabled={disabled}
                                        onChange={(event) =>
                                            setData(
                                                "medication_notes",
                                                event.target.value,
                                            )
                                        }
                                        className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 disabled:bg-gray-100"
                                    />

                                    <InputError
                                        message={errors.medication_notes}
                                        className="mt-2"
                                    />
                                </div>
                            </div>
                        </div>

                        <div className="overflow-hidden bg-white p-6 shadow-sm sm:rounded-lg">
                            <h3 className="text-lg font-semibold text-gray-900">
                                Emergency Contact
                            </h3>

                            <div className="mt-5 grid gap-5 md:grid-cols-3">
                                <div>
                                    <label
                                        htmlFor="emergency_contact_name"
                                        className="text-sm font-medium text-gray-700"
                                    >
                                        Name
                                    </label>

                                    <input
                                        id="emergency_contact_name"
                                        type="text"
                                        value={data.emergency_contact_name}
                                        disabled={disabled}
                                        onChange={(event) =>
                                            setData(
                                                "emergency_contact_name",
                                                event.target.value,
                                            )
                                        }
                                        className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 disabled:bg-gray-100"
                                    />

                                    <InputError
                                        message={errors.emergency_contact_name}
                                        className="mt-2"
                                    />
                                </div>

                                <div>
                                    <label
                                        htmlFor="emergency_contact_phone"
                                        className="text-sm font-medium text-gray-700"
                                    >
                                        Phone
                                    </label>

                                    <input
                                        id="emergency_contact_phone"
                                        type="text"
                                        value={data.emergency_contact_phone}
                                        disabled={disabled}
                                        onChange={(event) =>
                                            setData(
                                                "emergency_contact_phone",
                                                event.target.value,
                                            )
                                        }
                                        className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 disabled:bg-gray-100"
                                    />

                                    <InputError
                                        message={errors.emergency_contact_phone}
                                        className="mt-2"
                                    />
                                </div>

                                <div>
                                    <label
                                        htmlFor="emergency_contact_relationship"
                                        className="text-sm font-medium text-gray-700"
                                    >
                                        Relationship
                                    </label>

                                    <input
                                        id="emergency_contact_relationship"
                                        type="text"
                                        value={
                                            data.emergency_contact_relationship
                                        }
                                        disabled={disabled}
                                        onChange={(event) =>
                                            setData(
                                                "emergency_contact_relationship",
                                                event.target.value,
                                            )
                                        }
                                        className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 disabled:bg-gray-100"
                                    />

                                    <InputError
                                        message={
                                            errors.emergency_contact_relationship
                                        }
                                        className="mt-2"
                                    />
                                </div>
                            </div>
                        </div>

                        <div className="overflow-hidden bg-white p-6 shadow-sm sm:rounded-lg">
                            <h3 className="text-lg font-semibold text-gray-900">
                                Screening Questionnaire
                            </h3>

                            <p className="mt-1 text-sm text-gray-500">
                                Select how often you experienced each item
                                recently.
                            </p>

                            <div className="mt-5 space-y-4">
                                {screeningQuestions.map((question) => (
                                    <div
                                        key={question.key}
                                        className="rounded-lg border border-gray-100 p-4"
                                    >
                                        <div className="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                                            <div>
                                                <p className="text-sm font-semibold text-gray-900">
                                                    {question.label}
                                                </p>
                                                <p className="mt-1 text-xs text-gray-500">
                                                    {question.category}
                                                </p>
                                            </div>

                                            <select
                                                value={
                                                    data.screening_answers[
                                                        question.key
                                                    ]?.answer_score ?? ""
                                                }
                                                disabled={disabled}
                                                onChange={(event) =>
                                                    updateScreeningAnswer(
                                                        question.key,
                                                        "answer_score",
                                                        event.target.value,
                                                    )
                                                }
                                                className="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 disabled:bg-gray-100 lg:w-72"
                                            >
                                                <option value="">
                                                    Select answer
                                                </option>

                                                {screeningScoreOptions.map(
                                                    (option) => (
                                                        <option
                                                            key={option.value}
                                                            value={option.value}
                                                        >
                                                            {option.label}
                                                        </option>
                                                    ),
                                                )}
                                            </select>
                                        </div>

                                        <InputError
                                            message={
                                                errors[
                                                    `screening_answers.${question.key}.answer_score`
                                                ]
                                            }
                                            className="mt-2"
                                        />
                                    </div>
                                ))}
                            </div>
                        </div>

                        <div className="overflow-hidden bg-white p-6 shadow-sm sm:rounded-lg">
                            <h3 className="text-lg font-semibold text-gray-900">
                                Consent
                            </h3>

                            <div className="mt-5 space-y-4">
                                <CheckboxField
                                    id="consent_terms_accepted"
                                    label="I understand the counselling process, including its limits and responsibilities."
                                    checked={data.consent_terms_accepted}
                                    disabled={disabled}
                                    onChange={(checked) =>
                                        setData(
                                            "consent_terms_accepted",
                                            checked,
                                        )
                                    }
                                    error={errors.consent_terms_accepted}
                                />

                                <CheckboxField
                                    id="consent_privacy_accepted"
                                    label="I understand how my private information will be handled and protected."
                                    checked={data.consent_privacy_accepted}
                                    disabled={disabled}
                                    onChange={(checked) =>
                                        setData(
                                            "consent_privacy_accepted",
                                            checked,
                                        )
                                    }
                                    error={errors.consent_privacy_accepted}
                                />

                                <CheckboxField
                                    id="consent_telehealth_accepted"
                                    label="I consent to online counselling when sessions are conducted remotely."
                                    checked={data.consent_telehealth_accepted}
                                    disabled={disabled}
                                    onChange={(checked) =>
                                        setData(
                                            "consent_telehealth_accepted",
                                            checked,
                                        )
                                    }
                                    error={errors.consent_telehealth_accepted}
                                />

                                <CheckboxField
                                    id="consent_data_processing_accepted"
                                    label="I consent to the platform storing and processing my intake information for counselling service delivery."
                                    checked={
                                        data.consent_data_processing_accepted
                                    }
                                    disabled={disabled}
                                    onChange={(checked) =>
                                        setData(
                                            "consent_data_processing_accepted",
                                            checked,
                                        )
                                    }
                                    error={
                                        errors.consent_data_processing_accepted
                                    }
                                />
                            </div>
                        </div>

                        <InputError message={errors.intake} />

                        {intake.can_edit && (
                            <div className="flex flex-wrap justify-end gap-3">
                                <SecondaryButton
                                    type="submit"
                                    disabled={processing}
                                >
                                    Save draft
                                </SecondaryButton>

                                <PrimaryButton
                                    type="button"
                                    disabled={processing}
                                    onClick={submitIntake}
                                >
                                    Submit intake
                                </PrimaryButton>
                            </div>
                        )}
                    </form>
                </div>
            </div>
        </ClientLayout>
    );
}
