import { Link } from '@inertiajs/react';
import { Plus, Trash2 } from 'lucide-react';

const inputClass =
    'mt-1 block w-full rounded-xl border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500';

const errorClass = 'mt-1 text-sm text-red-600';

const emptyQualification = {
    qualification: '',
    institution: '',
    field_of_study: '',
    year_completed: '',
    certificate_number: '',
};

export default function CounsellorForm({
    data,
    setData,
    errors,
    processing,
    users,
    specializations,
    languages,
    submitLabel,
    onSubmit,
}) {
    const toggleSpecialization = (specializationId) => {
        const selected = data.specialization_ids.includes(
            specializationId,
        );

        setData(
            'specialization_ids',
            selected
                ? data.specialization_ids.filter(
                      (id) => id !== specializationId,
                  )
                : [...data.specialization_ids, specializationId],
        );
    };

    const toggleLanguage = (languageId) => {
        const selected = data.languages.some(
            (language) => language.language_id === languageId,
        );

        setData(
            'languages',
            selected
                ? data.languages.filter(
                      (language) =>
                          language.language_id !== languageId,
                  )
                : [
                      ...data.languages,
                      {
                          language_id: languageId,
                          proficiency: 'conversational',
                      },
                  ],
        );
    };

    const updateLanguageProficiency = (
        languageId,
        proficiency,
    ) => {
        setData(
            'languages',
            data.languages.map((language) =>
                language.language_id === languageId
                    ? {
                          ...language,
                          proficiency,
                      }
                    : language,
            ),
        );
    };

    const addQualification = () => {
        setData('qualifications', [
            ...data.qualifications,
            { ...emptyQualification },
        ]);
    };

    const updateQualification = (index, field, value) => {
        setData(
            'qualifications',
            data.qualifications.map((qualification, position) =>
                position === index
                    ? {
                          ...qualification,
                          [field]: value,
                      }
                    : qualification,
            ),
        );
    };

    const removeQualification = (index) => {
        setData(
            'qualifications',
            data.qualifications.filter(
                (_, position) => position !== index,
            ),
        );
    };

    const languageIsSelected = (languageId) =>
        data.languages.some(
            (language) => language.language_id === languageId,
        );

    const languageProficiency = (languageId) =>
        data.languages.find(
            (language) => language.language_id === languageId,
        )?.proficiency ?? 'conversational';

    return (
        <form onSubmit={onSubmit} className="space-y-6">
            <section className="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 className="text-lg font-semibold text-slate-900">
                    Account and registration
                </h2>

                <div className="mt-5 grid gap-5 md:grid-cols-2">
                    <div>
                        <label
                            htmlFor="user_id"
                            className="text-sm font-medium text-slate-700"
                        >
                            User account
                        </label>

                        <select
                            id="user_id"
                            value={data.user_id}
                            onChange={(event) =>
                                setData('user_id', event.target.value)
                            }
                            className={inputClass}
                            required
                        >
                            <option value="">
                                Select a user account
                            </option>

                            {users.map((user) => (
                                <option
                                    key={user.id}
                                    value={user.id}
                                >
                                    {user.name} — {user.email}
                                </option>
                            ))}
                        </select>

                        {errors.user_id && (
                            <p className={errorClass}>
                                {errors.user_id}
                            </p>
                        )}
                    </div>

                    <div>
                        <label
                            htmlFor="registration_number"
                            className="text-sm font-medium text-slate-700"
                        >
                            Registration number
                        </label>

                        <input
                            id="registration_number"
                            type="text"
                            value={data.registration_number}
                            onChange={(event) =>
                                setData(
                                    'registration_number',
                                    event.target.value,
                                )
                            }
                            className={inputClass}
                            required
                        />

                        {errors.registration_number && (
                            <p className={errorClass}>
                                {errors.registration_number}
                            </p>
                        )}
                    </div>

                    <div>
                        <label
                            htmlFor="professional_title"
                            className="text-sm font-medium text-slate-700"
                        >
                            Professional title
                        </label>

                        <input
                            id="professional_title"
                            type="text"
                            value={data.professional_title}
                            onChange={(event) =>
                                setData(
                                    'professional_title',
                                    event.target.value,
                                )
                            }
                            className={inputClass}
                            placeholder="Senior Counsellor"
                        />

                        {errors.professional_title && (
                            <p className={errorClass}>
                                {errors.professional_title}
                            </p>
                        )}
                    </div>

                    <div>
                        <label
                            htmlFor="status"
                            className="text-sm font-medium text-slate-700"
                        >
                            Status
                        </label>

                        <select
                            id="status"
                            value={data.status}
                            onChange={(event) =>
                                setData('status', event.target.value)
                            }
                            className={inputClass}
                            required
                        >
                            <option value="active">Active</option>
                            <option value="inactive">
                                Inactive
                            </option>
                        </select>

                        {errors.status && (
                            <p className={errorClass}>
                                {errors.status}
                            </p>
                        )}
                    </div>
                </div>
            </section>

            <section className="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 className="text-lg font-semibold text-slate-900">
                    Personal and professional details
                </h2>

                <div className="mt-5 grid gap-5 md:grid-cols-2">
                    <div>
                        <label
                            htmlFor="nic"
                            className="text-sm font-medium text-slate-700"
                        >
                            NIC
                        </label>

                        <input
                            id="nic"
                            type="text"
                            value={data.nic}
                            onChange={(event) =>
                                setData('nic', event.target.value)
                            }
                            className={inputClass}
                        />

                        {errors.nic && (
                            <p className={errorClass}>
                                {errors.nic}
                            </p>
                        )}
                    </div>

                    <div>
                        <label
                            htmlFor="date_of_birth"
                            className="text-sm font-medium text-slate-700"
                        >
                            Date of birth
                        </label>

                        <input
                            id="date_of_birth"
                            type="date"
                            value={data.date_of_birth}
                            onChange={(event) =>
                                setData(
                                    'date_of_birth',
                                    event.target.value,
                                )
                            }
                            className={inputClass}
                        />

                        {errors.date_of_birth && (
                            <p className={errorClass}>
                                {errors.date_of_birth}
                            </p>
                        )}
                    </div>

                    <div>
                        <label
                            htmlFor="gender"
                            className="text-sm font-medium text-slate-700"
                        >
                            Gender
                        </label>

                        <select
                            id="gender"
                            value={data.gender}
                            onChange={(event) =>
                                setData('gender', event.target.value)
                            }
                            className={inputClass}
                        >
                            <option value="">Select gender</option>
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                            <option value="other">Other</option>
                            <option value="prefer_not_to_say">
                                Prefer not to say
                            </option>
                        </select>

                        {errors.gender && (
                            <p className={errorClass}>
                                {errors.gender}
                            </p>
                        )}
                    </div>

                    <div>
                        <label
                            htmlFor="years_of_experience"
                            className="text-sm font-medium text-slate-700"
                        >
                            Years of experience
                        </label>

                        <input
                            id="years_of_experience"
                            type="number"
                            min="0"
                            max="80"
                            value={data.years_of_experience}
                            onChange={(event) =>
                                setData(
                                    'years_of_experience',
                                    event.target.value,
                                )
                            }
                            className={inputClass}
                            required
                        />

                        {errors.years_of_experience && (
                            <p className={errorClass}>
                                {errors.years_of_experience}
                            </p>
                        )}
                    </div>

                    <div>
                        <label
                            htmlFor="city"
                            className="text-sm font-medium text-slate-700"
                        >
                            City
                        </label>

                        <input
                            id="city"
                            type="text"
                            value={data.city}
                            onChange={(event) =>
                                setData('city', event.target.value)
                            }
                            className={inputClass}
                        />

                        {errors.city && (
                            <p className={errorClass}>
                                {errors.city}
                            </p>
                        )}
                    </div>

                    <div className="md:col-span-2">
                        <label
                            htmlFor="address"
                            className="text-sm font-medium text-slate-700"
                        >
                            Address
                        </label>

                        <textarea
                            id="address"
                            rows="3"
                            value={data.address}
                            onChange={(event) =>
                                setData('address', event.target.value)
                            }
                            className={inputClass}
                        />

                        {errors.address && (
                            <p className={errorClass}>
                                {errors.address}
                            </p>
                        )}
                    </div>

                    <div className="md:col-span-2">
                        <label
                            htmlFor="biography"
                            className="text-sm font-medium text-slate-700"
                        >
                            Biography
                        </label>

                        <textarea
                            id="biography"
                            rows="5"
                            value={data.biography}
                            onChange={(event) =>
                                setData(
                                    'biography',
                                    event.target.value,
                                )
                            }
                            className={inputClass}
                        />

                        {errors.biography && (
                            <p className={errorClass}>
                                {errors.biography}
                            </p>
                        )}
                    </div>
                </div>
            </section>

            <section className="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 className="text-lg font-semibold text-slate-900">
                    Specializations
                </h2>

                <div className="mt-5 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                    {specializations.map((specialization) => (
                        <label
                            key={specialization.id}
                            className="flex cursor-pointer items-center gap-3 rounded-xl border border-slate-200 p-3"
                        >
                            <input
                                type="checkbox"
                                checked={data.specialization_ids.includes(
                                    specialization.id,
                                )}
                                onChange={() =>
                                    toggleSpecialization(
                                        specialization.id,
                                    )
                                }
                                className="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                            />

                            <span className="text-sm text-slate-700">
                                {specialization.name}
                            </span>
                        </label>
                    ))}
                </div>

                {errors.specialization_ids && (
                    <p className={errorClass}>
                        {errors.specialization_ids}
                    </p>
                )}
            </section>

            <section className="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 className="text-lg font-semibold text-slate-900">
                    Languages
                </h2>

                <div className="mt-5 space-y-3">
                    {languages.map((language) => {
                        const selected = languageIsSelected(
                            language.id,
                        );

                        return (
                            <div
                                key={language.id}
                                className="grid gap-3 rounded-xl border border-slate-200 p-4 sm:grid-cols-2"
                            >
                                <label className="flex cursor-pointer items-center gap-3">
                                    <input
                                        type="checkbox"
                                        checked={selected}
                                        onChange={() =>
                                            toggleLanguage(language.id)
                                        }
                                        className="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                                    />

                                    <span className="text-sm font-medium text-slate-700">
                                        {language.name}
                                    </span>
                                </label>

                                {selected && (
                                    <select
                                        value={languageProficiency(
                                            language.id,
                                        )}
                                        onChange={(event) =>
                                            updateLanguageProficiency(
                                                language.id,
                                                event.target.value,
                                            )
                                        }
                                        className="rounded-xl border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    >
                                        <option value="basic">
                                            Basic
                                        </option>
                                        <option value="conversational">
                                            Conversational
                                        </option>
                                        <option value="fluent">
                                            Fluent
                                        </option>
                                        <option value="native">
                                            Native
                                        </option>
                                    </select>
                                )}
                            </div>
                        );
                    })}
                </div>

                {errors.languages && (
                    <p className={errorClass}>
                        {errors.languages}
                    </p>
                )}
            </section>

            <section className="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <div className="flex items-center justify-between gap-4">
                    <div>
                        <h2 className="text-lg font-semibold text-slate-900">
                            Qualifications
                        </h2>

                        <p className="mt-1 text-sm text-slate-500">
                            Add professional and academic
                            qualifications.
                        </p>
                    </div>

                    <button
                        type="button"
                        onClick={addQualification}
                        className="inline-flex items-center gap-2 rounded-xl border border-indigo-200 bg-indigo-50 px-4 py-2 text-sm font-medium text-indigo-700 hover:bg-indigo-100"
                    >
                        <Plus className="h-4 w-4" />
                        Add
                    </button>
                </div>

                <div className="mt-5 space-y-5">
                    {data.qualifications.length === 0 && (
                        <p className="rounded-xl bg-slate-50 p-4 text-sm text-slate-500">
                            No qualifications have been added.
                        </p>
                    )}

                    {data.qualifications.map(
                        (qualification, index) => (
                            <div
                                key={index}
                                className="rounded-xl border border-slate-200 p-5"
                            >
                                <div className="flex justify-between gap-4">
                                    <h3 className="font-medium text-slate-900">
                                        Qualification {index + 1}
                                    </h3>

                                    <button
                                        type="button"
                                        onClick={() =>
                                            removeQualification(index)
                                        }
                                        className="rounded-lg p-2 text-red-600 hover:bg-red-50"
                                        aria-label="Remove qualification"
                                    >
                                        <Trash2 className="h-4 w-4" />
                                    </button>
                                </div>

                                <div className="mt-4 grid gap-4 md:grid-cols-2">
                                    <div>
                                        <label className="text-sm font-medium text-slate-700">
                                            Qualification
                                        </label>

                                        <input
                                            type="text"
                                            value={
                                                qualification.qualification
                                            }
                                            onChange={(event) =>
                                                updateQualification(
                                                    index,
                                                    'qualification',
                                                    event.target.value,
                                                )
                                            }
                                            className={inputClass}
                                            required
                                        />

                                        {errors[
                                            `qualifications.${index}.qualification`
                                        ] && (
                                            <p className={errorClass}>
                                                {
                                                    errors[
                                                        `qualifications.${index}.qualification`
                                                    ]
                                                }
                                            </p>
                                        )}
                                    </div>

                                    <div>
                                        <label className="text-sm font-medium text-slate-700">
                                            Institution
                                        </label>

                                        <input
                                            type="text"
                                            value={
                                                qualification.institution
                                            }
                                            onChange={(event) =>
                                                updateQualification(
                                                    index,
                                                    'institution',
                                                    event.target.value,
                                                )
                                            }
                                            className={inputClass}
                                            required
                                        />
                                    </div>

                                    <div>
                                        <label className="text-sm font-medium text-slate-700">
                                            Field of study
                                        </label>

                                        <input
                                            type="text"
                                            value={
                                                qualification.field_of_study
                                            }
                                            onChange={(event) =>
                                                updateQualification(
                                                    index,
                                                    'field_of_study',
                                                    event.target.value,
                                                )
                                            }
                                            className={inputClass}
                                        />
                                    </div>

                                    <div>
                                        <label className="text-sm font-medium text-slate-700">
                                            Year completed
                                        </label>

                                        <input
                                            type="number"
                                            min="1900"
                                            max={
                                                new Date().getFullYear()
                                            }
                                            value={
                                                qualification.year_completed
                                            }
                                            onChange={(event) =>
                                                updateQualification(
                                                    index,
                                                    'year_completed',
                                                    event.target.value,
                                                )
                                            }
                                            className={inputClass}
                                        />
                                    </div>

                                    <div className="md:col-span-2">
                                        <label className="text-sm font-medium text-slate-700">
                                            Certificate number
                                        </label>

                                        <input
                                            type="text"
                                            value={
                                                qualification.certificate_number
                                            }
                                            onChange={(event) =>
                                                updateQualification(
                                                    index,
                                                    'certificate_number',
                                                    event.target.value,
                                                )
                                            }
                                            className={inputClass}
                                        />
                                    </div>
                                </div>
                            </div>
                        ),
                    )}
                </div>
            </section>

            <div className="flex justify-end gap-3">
                <Link
                    href={route('admin.counsellors.index')}
                    className="rounded-xl border border-slate-300 bg-white px-5 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50"
                >
                    Cancel
                </Link>

                <button
                    type="submit"
                    disabled={processing}
                    className="rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-medium text-white hover:bg-indigo-700 disabled:cursor-not-allowed disabled:opacity-50"
                >
                    {processing ? 'Saving...' : submitLabel}
                </button>
            </div>
        </form>
    );
}
