import AdminLayout from "@/Layouts/AdminLayout";
import CounsellorForm from "@/Pages/Admin/Counsellors/Partials/CounsellorForm";
import { Head, useForm } from "@inertiajs/react";

export default function Edit({
    counsellor,
    users,
    specializations,
    languages,
}) {
    const { data, setData, put, processing, errors } = useForm({
        user_id: String(counsellor.user_id ?? ""),
        registration_number: counsellor.registration_number ?? "",
        professional_title: counsellor.professional_title ?? "",
        nic: counsellor.nic ?? "",
        date_of_birth: counsellor.date_of_birth ?? "",
        gender: counsellor.gender ?? "",
        years_of_experience: counsellor.years_of_experience ?? 0,
        biography: counsellor.biography ?? "",
        address: counsellor.address ?? "",
        city: counsellor.city ?? "",
        status: counsellor.status ?? "active",
        specialization_ids: counsellor.specialization_ids ?? [],
        languages: counsellor.languages ?? [],
        qualifications: (counsellor.qualifications ?? []).map(
            (qualification) => ({
                qualification: qualification.qualification ?? "",
                institution: qualification.institution ?? "",
                field_of_study: qualification.field_of_study ?? "",
                year_completed: qualification.year_completed ?? "",
                certificate_number: qualification.certificate_number ?? "",
            }),
        ),
    });

    const submit = (event) => {
        event.preventDefault();

        put(route("admin.counsellors.update", counsellor.id), {
            preserveScroll: true,
            transform: (formData) => ({
                ...formData,
                user_id: Number(formData.user_id),
                years_of_experience: Number(formData.years_of_experience),
            }),
        });
    };

    return (
        <AdminLayout title="Edit Counsellor">
            <Head title="Edit Counsellor" />

            <div className="space-y-6">
                <div>
                    <h1 className="text-2xl font-bold text-slate-900">
                        Edit counsellor
                    </h1>

                    <p className="mt-1 text-sm text-slate-500">
                        Update the profile for {counsellor.user?.name}.
                    </p>
                </div>

                <CounsellorForm
                    data={data}
                    setData={setData}
                    errors={errors}
                    processing={processing}
                    users={users}
                    specializations={specializations}
                    languages={languages}
                    submitLabel="Save changes"
                    onSubmit={submit}
                />
            </div>
        </AdminLayout>
    );
}
